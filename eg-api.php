

<?php
	include('CPCMS.php');
	require_once('utils.php');
	require_once('config.php');
	include('expungehelpers.php');
	include('helpers/mail_helper.php');
	include('helpers/api_validator.php');
	//initialize the response that will get sent back to requester
	$response = array();
	//set default response code:
	http_response_code(404);


	// Log the request, but strip identifying info
	$test_headers = $_REQUEST;
	error_log("Logging a request to eg-api.");

	$test_headers['apikey'] = preg_replace('/./', 'x', $test_headers['apikey']);	
	$test_headers['personFirst'] = preg_replace('/(?!^)./','x',$test_headers['personFirst']);
	$test_headers['personLast'] = preg_replace('/(?!^)./','x',$test_headers['personLast']);
	$test_headers['personStreet'] = preg_replace('/(?!^)./','x',$test_headers['personStreet']);

	//file_put_contents('php://stderr', print_r($test_headers, TRUE));

	
	// Test if the quest is well formed.
	if(malformedRequest($_REQUEST)) {
		http_response_code(403);
		$response['results']['status'] = malformedRequest($_REQUEST);
	} elseif(!validAPIKey($_REQUEST, $db)) {
		http_response_code(403);
		$response['results']['status'] = "Invalid request.";
	} else {
		http_response_code(200);
		error_log("Starting to process a good response.");
		// a cpcmsSearch flag can be set to true in the post request
		// to trigger a cpcms search.
		if (isset($_REQUEST['cpcmsSearch']) && preg_match('/^(t|true|1)$/i', $_REQUEST['cpcmsSearch'])===1){
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
		error_log("Done processing cpcmsSearch");
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
		$attorney = new Attorney(validAPIKey($_REQUEST, $db), $db);

		error_log("Figured out the Attorney:");
		error_log("Attorney " . $_REQUEST['current_user'] . " is " . validApiKey($_REQUEST['current_user'], $db)); 	

		$docketFiles = $_FILES;
		
		if (!isset($docketNums)) {
			// If $docketNums wasn't set in CPCMS search, initialize it to an empty array.
			$docketNums = array();
		}

		if (isset($_REQUEST['docketNums'])) {
			// Add any docket numbers passed in POST request to $docketnums.
			// POST[docketnums] should be a comma-delimited string like "MC-12345,CP-34566"
			$docketNumsRequest = filter_var($_REQUEST['docketNums'], FILTER_SANITIZE_SPECIAL_CHARS);
			foreach (explode(",",$docketNumsRequest) as $doc) {
				if ($doc) { //Doc will be false if the filter fails.
					array_push($docketNums, $doc);
				}
			}
			$response['results']['dockets'] = $docketNums;
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
		$parsed_results = parseArrests($arrests, $sealable, $person);
		$response['results']['expungements_redactions'] = $parsed_results['expungements_redactions'];
		$response['results']['sealing'] = $parsed_results['sealing'];
		$files[] = createOverview($arrests, $templateDir, $dataDir, $person, $sealable);
		error_log("beginning to create petitions, if requested.");
		if (preg_match('/^(t|true|1)$/i', $_REQUEST['createPetitions'])===1) {
			$zipFile = zipFiles($files, $dataDir, $docketFiles,
				uniqid($person->getFirst() . $person->getLast(), true) . "Expungements");

			if (count($files) > 0) {
				$response['results']['expungeZip'] = basename($zipFile);
			} else {
				$response['results']['status'] = "Error. No dockets downloaded. It would be nice if this message were more helpful.";
			}
		}

		// write everything to the DB as long as this wasn't a "test" upload.
		// we determine test upload if a SSN is entered.  If there is no SSN, we assume that
		// there was no expungement either - it was just a test to see whether expungements were
		// possible or a test of the generator itself by yours truly.

		error_log("starting to write to db");
		if (isset($urlPerson['SSN']) && $urlPerson['SSN'] != "") {
			//error_log("writing to db:");
			//error_log("arrests:");
		    	//file_put_contents('php://stderr', print_r($arrests), TRUE);
			//error_log("person");
			//file_put_contents('php://stderr', print_r($person), TRUE);
			//error_log("attorney");
			//file_put_contents('php://stderr', print_r($attorney), TRUE);
	
			writeExpungementsToDatabase($arrests, $person, $attorney, $db);
			//error_log("wrote to db");
		}
		//error_log("cleaning up files");
		cleanupFiles($files);
		//error_log("done writing to db");
	}// end of processing req from a valid user


	error_log("checking whether to email petitions.");
	
	if (isset($_REQUEST['emailPetitions']) && preg_match('/^(t|true|1)$/i', $_REQUEST['emailPetitions'])===1){
		if (!(isset($_REQUEST['createPetitions']) && preg_match('/^(t|true|1)$/i', $_REQUEST['createPetitions'])===1)) {
			$file_path = NULL;
			unset($response['results']['expungeZip']);
		} else { 
			$file_path = $response['results']['expungeZip'];
			$path_parts = pathinfo($response['results']['expungeZip']);
			$response['results']['expungeZip'] = $baseURL . "secureServe.php?serveFile=" . $path_parts['filename']; 
		} 
	    error_log("current_user" . $_REQUEST['current_user'] . " response:" . $response . " filepath:" . $file_path);
	    mailPetition($_REQUEST['current_user'], $_REQUEST['current_user'], $response, $file_path);

	} else {
		error_log("emailPetitions was not set");
	}

	//file_put_contents('php://stderr', print_r(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), TRUE));
	print_r(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES, 10));



//END OF SCRIPT, start of some functions it uses.
//	function validAPIKey() {
//		$db = $GLOBALS['db'];
//		if (!isset($_REQUEST['current_user'])) {
//			return False;
//		}
//		$useremail = $db->real_escape_string($_REQUEST['current_user']);
//		if (isset($_REQUEST['apikey'])) {
//			$query = $db->prepare("SELECT apiKey from user as u left join userinfo as ui on u.userid=ui.userid left join program as p on ui.programID=p.programid WHERE u.email=?");
//			$query->bind_param("s", $useremail);
//			$query->execute();
//			$query->bind_result($apikey_hashed);
//			$query->fetch();
//			$query->close();
//			if (!$apikey_hashed) {
//				return False;
//			};
//		if (password_verify($_REQUEST['apikey'], $apikey_hashed)) {
//			// The user submitted the correct api key, so now find the userid number.
//			$query = $db->prepare("SELECT userid from user where email = ?");
//			$query->bind_param("s",$useremail);
//			$query->execute();
//			$query->bind_result($userid);
//			$query->fetch();
//			$query->close();
//			if (!$userid) {
//				return False;
//			};
//			return $userid;
//			};
//		}; 
//		return False;
//	};

	function malformedRequest($request) {
		// Given a dictionary $request
		// Return false if there are no missing values
		// 	but return a message if any of certain conditions are true.
		// This takes advantage of the truthiness of php strings
		// 	to supply a helpful message.

		

		if (empty($request['current_user'])) {
			return "User email missing from request.";
		}

		if ( ($request['cpcmsSearch'] == 'false') && empty($request['docketNums']) ) {
			return "If you do not wish to do a CPCMS search, then you must supply docket numbers.";
		}

		if ( !isset($request['createPetitions']) || ($request['createPetitions'] == '')  ) {	
			return "Should I create petitions? Please include createPetitions=[0|1] in your request.";
		}

		if ( empty($request['apikey']) ) {
			return "Key missing from request.";
		}
		return False;
	};//End of well-formed request



	function parseArrests($arrests, $sealable, $person) {
		// Similar to createOverview, but without the microsoft word
		$results = Array();
		//print("\nParsing arrests.\n");
		//print_r($arrests);
		//print("\n Size of arrests: ");
		//print(sizeof($arrests));
		//print("\n");
		if (sizeof($arrests) == 0) {
			$results['expungements_redactions'] = ["none"];
		} else {
			$results['expungements_redactions'] = Array();
			$results['sealing'] = Array();
			foreach($arrests as $arrest) {
			
				$thisArrest = Array();
					
				$thisArrest['docket'] = htmlspecialchars(implode(", ", $arrest->getDocketNumber()), ENT_COMPAT, 'UTF-8');
				$thisArrest['otn'] = htmlspecialchars($arrest->getOTN(), ENT_COMPAT, 'UTF-8');				

				$expType = "No expungement possible";
				if ($arrest->isArrestRedaction()) {
					$expType = "Partial Expungement";
				}
				if ($arrest->isArrestExpungement()) {
					$expType = "Expungement";
				}
				if ($arrest->isArrestARDExpungement()) {
					$expType = "ARD Expungement***"; 
				}
				if ($arrest->isArrestSummaryExpungement($arrests)) {
					$expType = "Summary Expungement";
				}
				if ($arrest->isArrestOver70Expungement($arrests, $person)) {
					$expType = "Expungement (over 70)";
				}
				// Ignoring act 5 sealing for now
				$thisArrest['expungement_type'] = $expType;
				$thisArrest['unpaid_costs'] = htmlspecialchars(number_format($arrest->getCostsTotal() - $arrest->getBailTotal(),2),ENT_COMPAT, 'UTF-8');
				$thisArrest['bail'] = htmlspecialchars(number_format($arrest->getBailTotalTotal(),2), ENT_COMPAT, 'UTF-8');
				$results['expungements_redactions'][] = $thisArrest;
				if ($arrest->isArrestSealable()>0) {
					//then iterate over all the charges
					foreach ($arrest->getCharges() as $charge) {
						$thisCharge = Array();
						// check if the charge is a conviction and if it is sealable (non conviction charges get a 1)
						if ( $charge->isConviction() && ($charge->isSealable() >0) ) {
							$thisCharge['case_number'] = htmlspecialchars($arrest->getFirstDocketNumber(), ENT_COMPAT, 'UTF-8');
							$thisCharge['charge_name'] = htmlspecialchars($arrest->getChargeName(), ENT_COMPAT, 'UTF-8');
							$thisCharge['code_section'] = htmlspecialchars($arrest->getCodeSection(), ENT_COMPAT, 'UTF-8');
							if ($charge->isSealable()==1) {
								$thisCharge['sealable'] = "Yes";
							} else {
							 	$thisCharge['sealable'] = "No";
							}
							$thisCharge['additional_information'] = htmlspecialchars($arrest->getSealablePercent(), ENT_COMPAT, 'UTF-8');
							$results['sealing'][] = $thisCharge;
						} // end processing if a charge is a conviction that is sealable
					} //end loop over charges for an arrest
				} // end of checking if arrest is sealable

			}//end of processing arrests

		}// end of processing results
		//error_log("Returning response:");
		//error_log("-----------");
		return $results;
	}//end of parseArrests


?>
