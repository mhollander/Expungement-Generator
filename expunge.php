<?php
// @todo replace mysql_escape_string with mysql_real_escape_string
// @todo check login

/***********************************************************************
*
*	expunge.php
*	The main controller for actually completing the expungements.  Deals with 
*   dump of all docket sheets and summary sheet, sends them all to be parsed,
*	combines information as needed, and then makes calls to generate reports.
*
*	Copyright 2011-2015 Community Legal Services
* 
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
*    http://www.apache.org/licenses/LICENSE-2.0

* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
**
***********************************************************************/


require_once("config.php");
require_once("Arrest.php");
require_once("ArrestSummary.php");
require_once("Person.php");
require_once("Attorney.php");
require_once("utils.php");
require_once("CPCMS.php");

include('head.php');
include('header.php');
?>

<div class="main">
	<div class="content-left">&nbsp;</div>
	<div class="content-center">

<?php
// if the user isn't logged in, then don't display this page.  Tell them they need to log in.
if (!isLoggedIn())
	include("displayNotLoggedIn.php");

// they are logged in, see if we were supposed to do a CPCMS search or if they are sending
// us files from CPCMS themselves
else if (isset($_POST['cpcmsSearch']) && $_POST['cpcmsSearch'] == "true")
{
    // this is a CPCMS search.  So do one, display the results in a form (with hidden fields for
    // entries from teh previous screen), and shoot everything back here to do the expungements
    $urlPerson = getPersonFromGetVars();
    $cpcms = new CPCMS($urlPerson['First'], $urlPerson['Last'], $urlPerson['DOB']);
    $status = $cpcms->cpcmsSearch();
    if (!preg_match("/0/",$status[0]))
        print "There was a problem with your search. Either the CPCMS website is down or your search returned no results.  Status: $status[0].";
    else
    {
        //only integrate the summary information if we
        // have a DOB; otherwise what is the point?
        if (!empty($urlPerson['DOB']))
            $cpcms->integrateSummaryInformation();
        
        // remove the cpcmsSearch variable from the POST vars and then pass them to
        // a display funciton that will display all of the arrests as a webform, with all
        // of the post vars re-posted as hidden variables.  Also pass this filename as the 
        // form action location.
        unset($_POST['cpcmsSearch']);
        
        $cpcms->displayAsWebForm(basename(__FILE__), $_POST);
    }
}

else 
{

	$arrests = array();
	$arrestSummary = new ArrestSummary();

	// get information about the person from the POST vars passed in
	$urlPerson = getPersonFromGetVars();
	$person = new Person($urlPerson['First'], $urlPerson['Last'], $urlPerson['SSN'], $urlPerson['Street'], $urlPerson['City'], $urlPerson['State'], $urlPerson['Zip']);
	
	// if this is true, then we do an expungement of every charge, even ones that normally aren't expungeable.
	// This is for situations like pardons where someone needs an expungement of convictions.
	$expungeRegardless = isset($_POST["expungeRegardless"]);

	// make sure to change this in the future to prevent hacking!
	$attorney = new Attorney($_SESSION["loginUserID"], $db);
	if($GLOBALS['debug'])
		$attorney->printAttorneyInfo();
	
	// parse the uploaded files will lead to expungements or redactions
    $docketFiles = $_FILES;
    if (isset($_POST['scrapedDockets']))
        $docketFiles = CPCMS::downloadDockets($_POST['docket']);
      
	$arrests = parseDockets($tempFile, $pdftotext, $arrestSummary, $person, $docketFiles);
	
	// integrate the summary information in with the arrests 
	integrateSummaryInformation($arrests, $person, $arrestSummary);
	
	print "<b>EXPUNGEMENT INFORMATION</b><br/><br/>";
	// combine the docket sheets that are from the same arrest
	$arrests = combineArrests($arrests);
		
	// do the expungements in PDF form
	$files = doExpungements($arrests, $templateDir, $dataDir, $person, $attorney, $expungeRegardless, $db);
	

	$files[] = createOverview($arrests, $templateDir, $dataDir, $person);  
	
	// zip up the final PDFs
	$zipFile = zipFiles($files, $dataDir, $docketFiles);

	print "<div>&nbsp;</div>";
	if (count($files) > 0)
		print "<div><b>Download Petitions and Overview: <a href='" .$baseURL . "data/" . basename($zipFile). "'>" . basename($zipFile) . "</a></b></div>";
	else
		print "<div><b>No expungeable or redactable offenses found for this individual.</b></div>";

	// write everything to the DB as long as this wasn't a "test" upload.
	// we determine test upload if a SSN is entered.  If there is no SSN, we assume that 
	// there was no expungement either - it was just a test to see whether expungements were
	// possible or a test of the generator itself by yours truly.
	if (isset($urlPerson['SSN']) && $urlPerson['SSN'] != "")
		writeExpungementsToDatabase($arrests, $person, $attorney, $db);
	
	
	// if we are debuging, display the expungements
	if ($GLOBALS['debug']) 
		screenDisplayExpungements($arrests);
		
	// cleanup any files that are left over
	cleanupFiles($files);
} // if isLoggedIn()
?>
		</div> <!-- content-center -->
	<div class="content-right"><?php include("expungementDisclaimers.php");?></div>
	</div>

<?php
include ('foot.php');
	
//******  begin helper functions *******************/

// parse the docket sheets into Arrest objects and place them all into an array
// @return an array of Arrest objects containing each docket sheet parsed
function parseDockets($tempFile, $pdftotext, $arrestSummary, $person, $docketFiles)
{
	$arrests = array();
	// loop over all of the files that we uploaded and read them in to see if they are expungeable
	foreach($docketFiles["userFile"]["tmp_name"] as $key => $file)
	{
		$command = $pdftotext . " -layout \"" . $file . "\" \"" . $tempFile . "\"";
		//print $command;
		system($command, $ret);
		if($GLOBALS['debug'])
			print "<br>The pdftotext command: $command <BR />";
		
		if ($ret == 0)
		{
			//print $filename . "<br />";
			$thisRecord = file($tempFile);

			$arrest = new Arrest();

			if ($arrest->isDocketSheet($thisRecord[1]))
			{
				// if this is a regular docket sheet, use the regular parsing function
				$arrest->readArrestRecord($thisRecord, $person);
				
				// now add the arrest to the arrests array
				// but don't include arrests that were summary traffic tickets or something
				if ($arrest->isArrestCriminal())
					$arrests[$arrest->getFirstDocketNumber()] = $arrest;
					
				// associate the PDF with the arrest for later saving to the DB
				// associate the real PDF file name with the arrest as well for use in the overview
				if ($docketFiles["userFile"]["size"][$key] > 0)
				{
					$arrest->setPDFFile($file);
					$arrest->setPDFFileName($docketFiles["userFile"]["name"][$key]);
				}
			}
			elseif (ArrestSummary::isArrestSummary($thisRecord))
			{
				// if this is a summary sheet of all arrests, make a separate array
				$arrestSummary->processArrestSummary($thisRecord);
			}	
		}
	}
	try
	{
		unlink($tempFile);
	}
	catch (Exception $e) {}
	
	return $arrests;
}

// takes an array of Arrests and determines which ones are part and parcel of the same case.
// @return the array of Arrests, pared down.
function combineArrests($arrests)
{
	// start by comparing the arrests and combining the ones with matching OTNS or DC numbers
	foreach ($arrests as $key=>$arrest)
	{
		$innerArrests = $arrests;
		foreach ($innerArrests as $innerKey=>$innerArrest)
		{
			if($arrest->combine($innerArrest))
			{
				print "combining " . $arrest->getFirstDocketNumber() . " | " . $innerArrest->getFirstDocketNumber() . "<br />";
				unset($arrests[$innerKey]);
			}
		}
	}
	// reindex the arrests array now that some entries have been removed
	$arrests = array_values($arrests);
	return $arrests;
}

// integrates information from the summaryArrest object and the arrests array.  The summary 
// arrest object most commonly contains additional information about the judge, but could
// have other useful information like the OTN or DC number.
// also integrates information into the Person object if a PPID and SID exist
function integrateSummaryInformation($arrests, $person, $arrestSummary)
{
	if ($arrestSummary != null && $arrestSummary->hasValuableInformation())
	{
		// integrate arrests together
		foreach ($arrests as $arrest)
		{
			// grab the docket number off of the arrest
			$docket = $arrest->getFirstDocketNumber();
			
			// and combine with the like summary, if one exists
			if ($arrestSummary->isArrestInSummary($docket))
				$arrest->combineWithSummary($arrestSummary->getArrest($docket));
		}
		
		// integrate the SID and PPID
		if ($arrestSummary->getSID() != null && $arrestSummary->getSID() != "")
			$person->setSID($arrestSummary->getSID());
		if ($arrestSummary->getPID() != null && $arrestSummary->getPID() != "")
			$person->setPP($arrestSummary->getPID());
	

		// warn the user about any cases that are in the summary, but that were not uploaded
		$summaryKeys = $arrestSummary->getArrestKeys();
		$arrestKeys = array_keys($arrests);
		$missingDockets = array_diff($summaryKeys, $arrestKeys);
		
		if (count($missingDockets) > 0)
		{
			print "<b>The following cases appear in the summary docket, but you didn't upload a corresponding docket sheet:</b><br/>";
			foreach ($missingDockets as $missingDocket)
				print "$missingDocket<br/>";
			print "<br/>";
	
		}
	}
	
	// integrate the DOB from the arrests into the person
	foreach ($arrests as $arrest)
	{
		$DOB = $arrest->getDOB();
		if($DOB != null and $DOB != "")
		{
			$person->setDOB($DOB);
			return;
		}
	}
	
}
	

// loops through all of the Arrests in $arrests and does a paper and to screen expungement
// @return an array of the file names that were created during this process.
function doExpungements($arrests, $templateDir, $dataDir, $person, $attorney, $expungeRegardless, $db)
{
	$files = array();

	print "<ul class='no-indent'>";
	foreach ($arrests as $arrest)	
	{
		if ($arrest->isArrestSummaryExpungement($arrests) || $arrest->isArrestExpungement() ||  $arrest->isArrestOver70Expungement($arrests, $person) || $arrest->isArrestRedaction() || $expungeRegardless)
		{
			$files[] = $arrest->writeExpungement($templateDir, $dataDir, $person, $attorney, $expungeRegardless, $db);
			
			// if this isn't a philly arrest and this is an agency that has IFP status, then add in 
			// an IFP notice.
			if ($arrest->getCounty()!="Philadelphia" && $attorney->getIFP())
				$files[] = $arrest->writeIFP($templateDir, $person, $attorney);
			
			print "<li><span class='boldLabel'>Performing ";
			
			if ($arrest->isArrestExpungement() || $arrest->isArrestSummaryExpungement($arrests) || $expungeRegardless)
				print "expungement";
			else if ($arrest->isArrestOver70Expungement($arrests, $person))
				print "expungement (over 70)";
			else 
				print "redaction";

			print " on case: </span> " . $arrest->getFirstDocketNumber() . "</li>";
		}
		else 
			print "<li><span class='boldLabel'>No expungement possible on case: </span>" . $arrest->getFirstDocketNumber() . "</li>";
	}
	print "</ul>";
	return $files;
}

// creates an overview document that lists all of the relevant information for the advocate
function createOverview($arrests, $templateDir, $dataDir, $person)
{
    $docx = new \PhpOffice\PhpWord\TemplateProcessor($templateDir . Arrest::$overviewTemplate);
      
	// set person variables
	$docx->setValue("NAME", htmlspecialchars($person->getFirst() . " " . $person->getLast(), ENT_COMPAT, 'UTF-8'));
	//$docx->setValue("PPID", htmlspecialchars($person->getPP(), ENT_COMPAT, 'UTF-8'));
	//$docx->setValue("SID", htmlspecialchars($person->getSID(), ENT_COMPAT, 'UTF-8'));
	
	if (sizeof($arrests) > 0)
		$docx->setValue("DOB", htmlspecialchars($arrests[0]->getDOB(), ENT_COMPAT, 'UTF-8'));

	$docx->cloneRow("DOCKET", count($arrests));

    $i = 1;
	foreach ($arrests as $arrest)
	{
			$expType = "No expungement possible";
			if ($arrest->isArrestRedaction())
				$expType = "Redaction";
			if ($arrest->isArrestExpungement())
				$expType = "Expungement";
			if ($arrest->isArrestARDExpungement())
				$expType = "ARD Expungement***";
			if ($arrest->isArrestSummaryExpungement($arrests))
				$expType = "Summary Expungement";
			if ($arrest->isArrestOver70Expungement($arrests, $person))
				$expType = "Expungement (over 70)";
			$docx->setValue("DOCKET#" . $i, htmlspecialchars(implode(", ", $arrest->getDocketNumber()), ENT_COMPAT, 'UTF-8'));
			$docx->setValue("PDFNAME#" . $i, htmlspecialchars($arrest->getPDFFileName(), ENT_COMPAT, 'UTF-8'));
			$docx->setValue("OTN#" . $i, htmlspecialchars($arrest->getOTN(), ENT_COMPAT, 'UTF-8'));
			$docx->setValue("EXPUNGEMENT_TYPE#" . $i, htmlspecialchars($expType, ENT_COMPAT, 'UTF-8'));
			$docx->setValue("UNPAID_COSTS#" . $i, htmlspecialchars(number_format($arrest->getCostsTotal() - $arrest->getBailTotal(),2), ENT_COMPAT, 'UTF-8'));
			$docx->setValue("BAIL#" . $i, htmlspecialchars(number_format($arrest->getBailTotalTotal(),2), ENT_COMPAT, 'UTF-8'));
            $i = $i+1;
	}
	
	$outputFile = $dataDir . $person->getFirst() . $person->getLast() . "Overview.docx";
	$docx->saveAs($outputFile);	
	
	return $outputFile;
}

// writes the expungements to the database
// @return none
function writeExpungementsToDatabase($arrests, $person, $attorney, $db)
{
	// setup a db connection for CREP users so that we can write remotely
	$crepDB;
	
	
	// if this is a crep lawyer, write this to the crep database
	if ($attorney->getProgramID() == 2)
	{
		$crepDB = new mysqli($GLOBALS['crepDBHost'], $GLOBALS['crepDBUser'], $GLOBALS['crepDBPassword'], $GLOBALS['crepDBName']);
		if ($crepDB->connect_error) 
			die('Error connecting to the db: Connect Error (' . $crepDB->connect_errno . ') ' . $crepDB->connect_error);
		
		$person->writePersonToDB($crepDB);
	}
	
	// if this isn't a CLS lawyer, we only update the number of total petitions generated and return
	else if ($attorney->getProgramID() == 1)
		// otherwise, write the defendant into the db if he doesn't already exist
		$person->writePersonToDB($db);
	
	$total = 0;
	// and then for each arrest, write the arrest into the database as well
	foreach ($arrests as $arrest)
	{
		// count the number of petitions prepared
		if ($arrest->isArrestExpungement() || $arrest->isArrestRedaction() || $arrest->isArrestSummaryExpungement($arrests))
			$total++;
	
		// only add this to the db for certain programs
		if ($attorney->getProgramID() == 1)
			$arrest->writeExpungementToDatabase($person, $attorney, $db, true);
		
		// if this is CREP, add this to a remote CREP database
		else if ($attorney->getProgramID() == 2)
			$arrest->writeExpungementToDatabase($person, $attorney, $crepDB, false);
		
	}
	
	$attorney->updateTotalPetitions($total, $db);
	
	return;
}

// prints out the expungement data to the screen
function screenDisplayExpungements($arrests)
{
	foreach ($arrests as $arrest)
	{
		print "expungement? " . $arrest->isArrestExpungement() . "<br />";
		print "redaction? " . $arrest->isArrestRedaction() . "<br />";
		print "held for court? ". $arrest->isArrestHeldForCourt() . "<br />";
		$arrest->simplePrint();
		print "<br />";

	}
}

?>
	