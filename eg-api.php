

<?php
	//print_r("getting started");
	include('CPCMS.php');
	require_once('utils.php');
	require_once('config.php');
	//require_once('dbconnect.php');
	include('expungehelpers.php');
	//initialize the response that will get sent back to requester
	//print_r("everything included.");
	$response = "response not clear yet.";

	session_start();
	
	if(!validAPIKey()) {
		$response = "403 - bad api key.";
	} else {
		// a cpcmsSearch flag can be set to true in the post request
		// to trigger a cpcms search.
		if (isset($_POST['cpcmsSearch']) && $_POST['cpcmsSearch']=='true'){
			$urlPerson = getPersonFromPostOrSession();
			$cpcms = new CPCMS($urlPerson['First'],$urlPerson['Last'], $urlPerson['DOB']);
			$status = $cpcms->cpcmsSearch();
			$statusMDJ = $cpcms->cpcmsSearch(true);
			if (!preg_match("/0/",$status[0]) && !preg_match("/0/", $statusMDJ[0])) {
				$response = "Your CPCMS search returned no results.";
			} else {
				//only integrate the summary information if we
        // have a DOB; otherwise what is the point?
        			if (!empty($urlPerson['DOB'])) {
            				$cpcms->integrateSummaryInformation();
				}
        // remove the cpcmsSearch variable from the POST vars and then pass them to
        // a display funciton that will display all of the arrests as a webform, with all
        // of the post vars re-posted as hidden variables.  Also pass this filename as the
        // form action location.
        			unset($_POST['cpcmsSearch']);
			}
		} // end of processing cpcmsSearch

		$arrests = array(); //an array to hold Arrest objects
		$arrestSummary = new ArrestSummary();

		$urlPerson = getPesonFromPostOrSession();
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

		$attorney = new Attorney(get_id_from_api_key($_POST['apikey']), $db);

		$docketFiles = $_FILES;
		if (isset($_SESSION['scrapedDockets'])) {
			//if the cpcms search has been run and has found dockets
			$docketFiles = CPCMS::downloadDockets($_SESSION['docket']);
			$arrests = parseDockets($tempFile, $pdftotext, $arrestSummary, $person, $docketFiles);
			integrateSummaryInformation($arrests, $person, $arrestSummary);
			$arrests = combineArrests($arrests);
		}
		$sealable = checkIfSealable($arrests);

		$files = doExpungements($arrests, $templateDir, $dataDir, $person,
														$attorney, $_SESSION['expungeRegardless'],
														$db, $sealable);
		$files[] = createOverview($arrests, $templateDir, $dataDir, $person, $sealable);
		$zipFile = zipFiles($files, $dataDir, $docketFiles,
								        $person->getFirst() . $person->getLast() . "Expungements");

		if (count($files) > 0) {
			$response = $baseURL . "data/" . basename($zipFile);
		} else {
			$response = "Error. No dockets downloaded. It would be nice if this message were more helpful.";
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
	print($response);

	function validAPIKey() {
		print("Testing validity of apikey");
		print_r($_POST);
		$db = $GLOBALS['db'];
		if (!isset($db)) {
			print("database still not accessible!");
		} else {
			print_r($db);
		}
		if (!isset($_POST['useremail'])) {
			print("useremail not included in POST request.");
			return False;
		}
		$useremail = $_POST['useremail'];
		if (isset($_POST['apikey'])) {
			print("Hashing " . $_POST['apikey']);
			print($db->real_escape_string($_POST['useremail']));
			$query = "SELECT user.apikey FROM user WHERE user.email = '".$useremail."';";
			print("\n" . $query . "\n");
			$result = $db->query($query);
			print_r($result);
			if (!$result) {
				print("could not get api hash for user email from db");
				return False;
			};
			$row = mysqli_fetch_assoc($result);
			print_r($row);
			if (password_verify($_POST['apikey'], $row['apikey'])) {
			print("API Key verified!!");
			return True;
			};
		}; 
		return False;
	};

?>
