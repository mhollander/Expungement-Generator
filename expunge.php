<?php
// @todo replace mysql_escape_string with mysql_real_escape_string
// @todo check login

require_once("config.php");
require_once("library/odf.php");
require_once("Arrest.php");
require_once("ArrestSummary.php");
require_once("Person.php");
require_once("Attorney.php");
require_once("utils.php");

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
else
{

	$arrests = array();
	$arrestSummary = new ArrestSummary();

	// get information about the person from the POST vars passed in
	$urlPerson = getPersonFromGetVars();
	$person = new Person($urlPerson['First'], $urlPerson['Last'], $urlPerson['PP'], $urlPerson['SID'], $urlPerson['SSN'], $urlPerson['Street'], $urlPerson['City'], $urlPerson['State'], $urlPerson['Zip'], $urlPerson['Alias']);

	// make sure to change this in the future to prevent hacking!
	$attorney = new Attorney($_SESSION["loginUserID"], $db);
	if($GLOBALS['debug'])
		$attorney->printAttorneyInfo();
	
	// parse the uploaded files will lead to expungements or redactions
	$arrests = parseDockets($_FILES, $toolsDir, $tempFile, $pdftotext, $arrestSummary);
	
	// integrate the summary information in with the arrests 
	integrateSummaryInformation($arrests, $person, $arrestSummary);
	
	// combine the docket sheets that are from the same arrest
	$arrests = combineArrests($arrests);
		
	// do the expungements in PDF form
	$files = doExpungements($arrests, $templateDir, $dataDir, $person, $attorney, $db);
	
	$files[] = createOverview($arrests, $templateDir, $dataDir, $person);  
	
	// zip up the final PDFs
	$zipFile = zipFiles($files, $dataDir);

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
function parseDockets($files, $toolsDir, $tempFile, $pdftotext, $arrestSummary)
{
	$arrests = array();
	// loop over all of the files that we uploaded and read them in to see if they are expungeable
	foreach($files["userFile"]["tmp_name"] as $key => $file)
	{
		$command = $toolsDir . $pdftotext . " -layout \"" . $file . "\" \"" . $tempFile . "\"";
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
				$arrest->readArrestRecord($thisRecord);
				
				// now add the arrest to the arrests array
				// but don't include arrests that were summary traffic tickets or something
				if ($arrest->isArrestCriminal())
					$arrests[] = $arrest;
					
				// associate the PDF with the file for later saving to the DB
				if ($files["userFile"]["size"][$key] > 0)
					$arrest->setPDFFile($file);
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
	}

	// finally, integrate the DOB from the arrests into the person
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
function doExpungements($arrests, $templateDir, $dataDir, $person, $attorney, $db)
{
	$files = array();

	//temporary - for testing of new petitions
	$newPetition = FALSE;
	if (isset($_POST["newStylePetition"]) && $_POST["newStylePetition"] == "TRUE")
		$newPetition = TRUE;
	// end temporary
	
	print "<ul class='no-indent'>";
	foreach ($arrests as $arrest)
	{
		if ($arrest->isArrestExpungement() || $arrest->isArrestRedaction() || $arrest->isArrestSummaryExpungement($arrests))
		{
			$files[] = $arrest->writeExpungement($templateDir, $dataDir, $person, $attorney, $newPetition, $db);
			
			// if this isn't a philly arrest and this is an agency that has IFP status, then add in 
			// an IFP notice.
			if ($arrest->getCounty()!="Philadelphia" && $attorney->getIFP())
				$files[] = $arrest->writeIFP($person, $attorney, $db);
			
			print "<li><span class='boldLabel'>Performing ";
			print (($arrest->isArrestExpungement() || $arrest->isArrestSummaryExpungement($arrests))?("expungement"):("redaction"));
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
	$odf = new odf($templateDir . Arrest::$overviewTemplate);
	
	// set person variables
	$odf->setVars("NAME", $person->getFirst() . " " . $person->getLast());
	$odf->setVars("PPID", $person->getPP());
	$odf->setVars("SID", $person->getSID());
	
	if (sizeof($arrests) > 0)
		$odf->setVars("DOB", $arrests[0]->getDOB());

	$theArrest = $odf->setSegment("summary");

	foreach ($arrests as $arrest)
	{
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
			$theArrest->setVars("DOCKET", implode(", ", $arrest->getDocketNumber()));
			$theArrest->setVars("OTN", $arrest->getOTN());
			$theArrest->setVars("EXPUNGEMENT_TYPE", $expType);
			$theArrest->setVars("UNPAID_COSTS", number_format($arrest->getCostsTotal() - $arrest->getBailTotal(),2));
			$theArrest->setVars("BAIL",number_format($arrest->getBailTotalTotal(),2));
			$theArrest->merge();
		}
	}
	$odf->mergeSegment($theArrest);
	
	$outputFile = $dataDir . $person->getFirst() . $person->getLast() . "Overview.odt";
	$odf->saveToDisk($outputFile);	
	
	return $outputFile;
}

// writes the expungements to the database
// @return none
function writeExpungementsToDatabase($arrests, $person, $attorney, $db)
{
	// if this isn't a CLS lawyer, then just return
	if ($attorney->getProgramID() != 1)
		return;
	
	// otherwise, write the defendant into the db if he doesn't already exist
	$person->writePersonToDB($db);
	
	// and then for each arrest, write the arrest into the database as well
	foreach ($arrests as $arrest)
	{
		$arrest->writeExpungementToDatabase($person, $attorney, $db);
	}
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
	