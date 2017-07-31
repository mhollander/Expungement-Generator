

<?php
	include('CPCMS.php');
	require_once('utils.php');
	require_once('config.php');
	include('expungehelpers.php');
	//initialize the response that will get sent back to requester
	$response = array();

	if(!validAPIKey()) {
		$response['results']['status'] = "403 - bad api key.";
	} else {
		// a cpcmsSearch flag can be set to true in the post request
		// to trigger a cpcms search.
		if (isset($_POST['cpcmsSearch']) && $_POST['cpcmsSearch']=='true'){
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
        			unset($_POST['cpcmsSearch']);
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
		$attorney = new Attorney($_POST['useremail'], $db);

		$docketFiles = $_FILES;
		
		if (!isset($docketNums)) {
			// If $docketNums wasn't set in CPCMS search, initialize it to an empty array.
			$docketNums = array();
		}

		if (isset($_POST['docketNums'])) {
			// Add any docket numbers passed in POST request to $docketnums.
			// POST[docketnums] should be a comma-delimited string like "MC-12345,CP-34566"
			foreach (explode(",",$_POST['docketNums']) as $doc) {
				array_push($docketNums, $doc);
			}
			$response['results']['dockets'] = $docketNums;
		}

		if (count($docketNums)>0) {
			//if the cpcms search has been run and has found dockets
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
		// doExpungements prints a table to the screen. That's not desired here, so this method wraps the function 
		// in an OutputBuffer to prevent that.
		ob_start();
		$files = doExpungements($arrests, $templateDir, $dataDir, $person,
														$attorney, $_SESSION['expungeRegardless'],
														$db, $sealable);
		ob_end_clean();
		$files[] = createOverview($arrests, $templateDir, $dataDir, $person, $sealable);
		$zipFile = zipFiles($files, $dataDir, $docketFiles,
								        $person->getFirst() . $person->getLast() . "Expungements");

		if (count($files) > 0) {
			$response['results']['expungeZip'] = $baseURL . "data/" . basename($zipFile);
		} else {
			$response['results']['status'] = "Error. No dockets downloaded. It would be nice if this message were more helpful.";
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
		if (!isset($db)) {
		} else {
		}
		if (!isset($_POST['useremail'])) {
			return False;
		}
		$useremail = $_POST['useremail'];
		if (isset($_POST['apikey'])) {
			$query = "SELECT user.apikey FROM user WHERE user.email = '".$useremail."';";
			$result = $db->query($query);
			if (!$result) {
				return False;
			};
			$row = mysqli_fetch_assoc($result);
			if (password_verify($_POST['apikey'], $row['apikey'])) {
			return True;
			};
		}; 
		return False;
	};
?>
