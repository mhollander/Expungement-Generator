

<?php
	include('CPCMS.php');
	require_once('utils.php');
	require_once('config.php');
	include('expungehelpers.php');
	//initialize the response that will get sent back to requester
	$response = array();
	
	//set default response code:
	http_response_code(404);

	//print("printing full _POST\n");
	//print_r($_REQUEST);
	
	if(malformedRequest($_REQUEST)) {
		http_response_code(403);
		$response['results']['status'] = malformedRequest($_REQUEST);
	} elseif(!validAPIKey()) {
		http_response_code(403);
		$response['results']['status'] = "Invalid request.";
	} else {
		http_response_code(200);
		// a cpcmsSearch flag can be set to true in the post request
		// to trigger a cpcms search.
		if (isset($_REQUEST['cpcmsSearch']) && $_POST['cpcmsSearch']=='true'){
			$urlPerson = getPersonFromPostOrSession();

			$cpcms = new CPCMS($urlPerson['First'],$urlPerson['Last'], $urlPerson['DOB']);
			$status = $cpcms->cpcmsSearch();
			$statusMDJ = $cpcms->cpcmsSearch(true);
			if (!preg_match("/0/",$status[0]) && !preg_match("/0/", $statusMDJ[0])) {
				$response['results'] = "Your CPCMS search returned no results.";
			} else {
				//only integrate the summary information if we
        // have a DOB; otherwise what is the point?
        			if (!empty($urlPerson['DOB'])) {
            				$cpcms->integrateSummaryInformation();
				}
				// We need an array of docket numbers, so we take the list of results
				// and extract only the docket number from each.
				$docketNums = array();
				foreach (array_merge($cpcms->getResults(), $cpcms->getMDJResults()) as $result) {
					$docketNums[] = $result[0];
				};
        // remove the cpcmsSearch variable from the POST vars and then pass them to
        // a display funciton that will display all of the arrests as a webform, with all
        // of the post vars re-posted as hidden variables.  Also pass this filename as the
        // form action location.
        			unset($_REQUEST['cpcmsSearch']);
			}
		} // end of processing cpcmsSearch

		$arrests = array(); //an array to hold Arrest objects
		$arrestSummary = new ArrestSummary();

		$urlPerson = getPersonFromPostOrSession();
		$person = new Person($urlPerson['First'],
			$urlPerson['Last'],
			$urlPerson['SSN'],
			$urlPerson['Street'],
			$urlPerson['City'],
			$urlPerson['State'],
			$urlPerson['Zip']);
	  	
		getInfoFromGetVars(); //this sets session variables based on the GET or
		// POST variables 'docket', 'act5Regardless', 'expungeRegardless', and
		// 'zipOnly'
		$response['personFirst'] = $urlPerson['First'];
		$response['personLast'] = $urlPerson['Last'];
		$response['dob'] = $urlPerson['DOB'];
		$attorney = new Attorney($_REQUEST['useremail'], $db);

		$docketFiles = $_FILES;
		
		if (!isset($docketNums)) {
			// If $docketNums wasn't set in CPCMS search, initialize it to an empty array.
			$docketNums = array();
		}

		if (isset($_REQUEST['docketNums'])) {
			// Add any docket numbers passed in POST request to $docketnums.
			// POST[docketnums] should be a comma-delimited string like "MC-12345,CP-34566"
			foreach (explode(",",$_REQUEST['docketNums']) as $doc) {
				$doc = filter_var($doc, FILTER_SANITIZE_SPECIAL_CHARS); 
				if ($doc) { //Doc will be false if the filter fails.
					array_push($docketNums, $doc);
				}
			}
			$response['results']['dockets'] = $docketNums;
			//print("posted docketnums:");
			//print_r( $_REQUEST['docketNums']);
			//print("\n response docketnums: " );
			//print_r( $response['results']['dockets'] );
		}
		// doExpungements prints a table to the screen. 
		// combineArrests also prints to the screen.
		// I only want to print a response object to the screen, so I put the 
		// the functions that print
		// into an OutputBuffer to prevent that.
		ob_start();
		if (count($docketNums)>0) {
			//if the cpcms search has been run and has found dockets
			//or of docket numbers were sent with the request to the api
			$docketFiles = CPCMS::downloadDockets($docketNums);
			$arrests = parseDockets($tempFile, $pdftotext, $arrestSummary, $person, $docketFiles);
			integrateSummaryInformation($arrests, $person, $arrestSummary, True);
			//set $isAPI in integrateSummaryInformation() to True to prevent printing to screen
			$arrests = combineArrests($arrests);
			$response['results']['arrestCount'] = count($arrests);
			# TODO Could add a function to insert a string of arrest information into $response.
			# TODO Could also add a function to insert information about chargeObjects (child of Arrest)
		}
		$sealable = checkIfSealable($arrests);

		$files = doExpungements($arrests, $templateDir, $dataDir, $person,
			$attorney, $_SESSION['expungeRegardless'],
			$db, $sealable);
		ob_end_clean();
		$files[] = createOverview($arrests, $templateDir, $dataDir, $person, $sealable);
		if ($_REQUEST['createPetitions']==1) {
			$zipFile = zipFiles($files, $dataDir, $docketFiles,
				$person->getFirst() . $person->getLast() . "Expungements");

			if (count($files) > 0) {
				$response['results']['expungeZip'] = $baseURL . "data/" . basename($zipFile);
			} else {
				$response['results']['status'] = "Error. No dockets downloaded. It would be nice if this message were more helpful.";
			}
		}

		// write everything to the DB as long as this wasn't a "test" upload.
		// we determine test upload if a SSN is entered.  If there is no SSN, we assume that
		// there was no expungement either - it was just a test to see whether expungements were
		// possible or a test of the generator itself by yours truly.
		if (isset($urlPerson['SSN']) && $urlPerson['SSN'] != "") {
			writeExpungementsToDatabase($arrests, $person, $attorney, $db);
		}
		cleanupFiles($files);
	}// end of processing req from a valid user
	
	print_r(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES, 3));

	function validAPIKey() {
		$db = $GLOBALS['db'];
		if (!isset($_REQUEST['useremail'])) {
			return False;
		}
		$useremail = $db->real_escape_string($_REQUEST['useremail']);
		if (isset($_REQUEST['apikey'])) {
			$query = "SELECT user.apikey FROM user WHERE user.email = '".$useremail."';";
			$result = $db->query($query);
			if (!$result) {
				return False;
			};
			$row = mysqli_fetch_assoc($result);
			if (password_verify($_REQUEST['apikey'], $row['apikey'])) {
			return True;
			};
		}; 
		return False;
	};

	function malformedRequest($post) {
		// Given a dictionary $post
		// Return false if there are no missing values
		// 	but return a message if any of certain conditions are true.
		// This takes advantage of the truthiness of php strings
		// 	to supply a helpful message.

		// TODO This will only flag one error at a time, though. 
		//print("In malformedRequest, post is is: \n ");
		//print_r($post);
		//print("\n but $_REQUEST is ");
		//print_r($_REQUEST);
		if ( ($post['useremail'] == "") || (!isset($post['useremail']) ) ) {
			return "User email missing from request.";
		}

		if ( ($post['cpcmsSearch'] == 'false') && ( (!isset($post['docketNums'])) || ($post['docketNums'] == "") ) ) {
			return "If you do not wish to do a CPCMS search, then you must supply docket numbers.";
		}

		if ( ($post['createPetitions'] == '') || (!isset($post['createPetitions']) ) ) {	
			return "Should I create petitions? Please include createPetitions=[0|1] in your request.";
		}

		if ( ($post['apikey'] == '') || (!isset($post['apikey']) ) ) {
			return "Key missing from request.";
		}
		return False;
	};//End of well-formed request

?>
