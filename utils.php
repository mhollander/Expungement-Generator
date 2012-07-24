<?php

/************************************************************************
*
*	utils.php
*
*	a collection of utilities used by the rest of the expungement generator
*
*	Copyright 2011 Michael Hollander
*
************************************************************************/

// checks to see if a person is logged in
// @return TRUE if the person is logged in and false if not
function isLoggedIn()
{
	if(isset($_SESSION['loginUserID']))
		return TRUE;
	else
		return FALSE;
}

// @return if the user is logged in, returns the logged in user; otherwise returns null

function getLoggedInUserName()
{
	return ($_SESSION['loginUserFirst'] . " " . $_SESSION['loginUserLast']);
}

// print a key only if it is set in the "GET" variables
function printIfSet($key)
{
	if(isset($_GET[$key]))
		print $_GET[$key];
}

	// gets a "person" from the getvars
// @return a hash with each value from the get vars escaped etc... to be html and sql safe
function getPersonFromGetVars()
{
	//
	if ($GLOBALS['debug'])
	{
		print "POST VARS: <br />";
		foreach ($_POST as $name=>$value)
		{
			print "$name: $value <br/>";
		}
	}
	
	$urlPerson = array();
	$urlPerson['First'] = mysql_escape_string(htmlspecialchars(stripslashes($_POST["personFirst"])));
	$urlPerson['Last'] = mysql_escape_string(htmlspecialchars(stripslashes($_POST["personLast"])));
	$urlPerson['Street'] = mysql_escape_string(htmlspecialchars(stripslashes($_POST["personStreet"])));
	$urlPerson['City'] = mysql_escape_string(htmlspecialchars(stripslashes($_POST["personCity"]))); $personState = mysql_escape_string(htmlspecialchars(stripslashes($_POST["personState"])));
	$urlPerson['State'] = mysql_escape_string(htmlspecialchars(stripslashes($_POST["personState"]))); $personState = mysql_escape_string(htmlspecialchars(stripslashes($_POST["personState"])));
	$urlPerson['Zip'] = mysql_escape_string(htmlspecialchars(stripslashes($_POST["personZip"])));
	$urlPerson['SID'] = mysql_escape_string(htmlspecialchars(stripslashes($_POST["personSID"])));
	$urlPerson['PP'] = mysql_escape_string(htmlspecialchars(stripslashes($_POST["personPP"])));
	$urlPerson['SSN'] = mysql_escape_string(htmlspecialchars(stripslashes($_POST["personSSN"])));
	$urlPerson['Alias'] = explode(",", mysql_escape_string(htmlspecialchars(stripslashes($_POST["personAlias"]))));
	
	return $urlPerson;
}

// zips all of the files in the array and returns the location of the zipfile.
// @return the name of the zipfile archive or null if there was a problem making the zipfile.
function zipFiles($files, $dataDir)
{
	$zip = new ZipArchive();
	$zipFileName = $dataDir . time() . ".zip";
	
	if ($zip->open($zipFileName, ZipArchive::CREATE)===TRUE )
	{
		foreach ($files as $index=>$file)
		{
			if($zip->addFile($file, basename($file)) && $GLOBALS['debug'])
				print "added $file to archive <br />";
		}
		if ($zip->close())
			return $zipFileName;
	}
	
	// if we couldn't open the zip file or save the zip file, return null
	return NULL;
	
}

// removes all of the files in $files from the OS
function cleanupFiles($files)
{
	foreach ($files as $file)
	{
		if (file_exists($file))
		{
			try
			{
				unlink($file);
			}
			catch (Exception $e) {}
		}
	}
}
 
// calculates date1-date2 in years
function dateDifference($date1, $date2)
{
	$difference = ((int)$date1->format('Y')) - ((int)$date2->format('Y'));
	// now check to see if date2 is later in the year than date1
	// (z = number of days since jan 1)
	if (((int)$date1->format('z')) > ((int)$date2->format('z')))
		return $difference;
	else
		return $difference-1;	
}

// @return a date in the form YYYY-MM-DD
// @param a date in the form MM/DD/YYYY
function dateConvert($docketDate)
{
	if (preg_match("/\d{1,2}\/\d{1,2}\/\d{2,4}/",$docketDate))
	{
		$mysqlDate = new DateTime($docketDate);
		return $mysqlDate->format('Y-m-d');
	}
	else
		return ("0000-00-00");
}

// @return bool True if a file with the case id passed in exists in the pdf file dir, false if not
// @param id - the ID of the expungement that we are looking for a PDF for
function doesPDFExistForCaseId($id)
{
	$filename = $GLOBALS['docketSheetsDir']	. $id;
	if(file_exists($filename))
		return TRUE;
	else
		return FALSE;
}