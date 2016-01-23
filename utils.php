<?php

/************************************************************************
*
*	utils.php
*
*	a collection of utilities used by the rest of the expungement generator
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
	$urlPerson['First'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personFirst"])));
	$urlPerson['Last'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personLast"])));
	$urlPerson['Street'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personStreet"])));
	$urlPerson['City'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personCity"])));
	$urlPerson['State'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personState"])));
	$urlPerson['Zip'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personZip"])));
	$urlPerson['SSN'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personSSN"])));
	$urlPerson['DOB'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personDOB"])));
	
	return $urlPerson;
}

	// gets vars passed in from LS
// @return a hash with each value from the get vars escaped etc... to be html and sql safe
function getLSVars()
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
	
	$LSVars = array();
	
	if (isSet($_POST["personFirst"])) $LSVars['First'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personFirst"])));
	if (isSet($_POST["personLast"])) $LSVars['Last'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personLast"])));
	if (isSet($_POST["personStreet"])) $LSVars['Street'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personStreet"])));
	if (isSet($_POST["personCity"])) $LSVars['City'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personCity"])));
	if (isSet($_POST["personState"])) $LSVars['State'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personState"])));
	if (isSet($_POST["personZip"])) $LSVars['Zip'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personZip"])));
	if (isSet($_POST["personSID"])) $LSVars['SID'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personSID"])));
	if (isSet($_POST["personPP"])) $LSVars['PP'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personPP"])));
	if (isSet($_POST["personSSN"])) $LSVars['SSN'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["personSSN"])));
	if (isSet($_POST["personLSUser"])) $LSVars['LSUser'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["LSUser"])));
	if (isSet($_POST["personLSPass"])) $LSVars['LSPass'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["LSPass"])));
	if (isSet($_POST["Token"])) $LSVars['Token'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["Token"])));
	if (isSet($_POST["debug"])) $LSVars['debug'] = $GLOBALS['db']->real_escape_string(htmlspecialchars(stripslashes($_POST["debug"])));
	
	return $LSVars;
}

// print index if exists
function printVar($array, $index)
{
	if (isSet($array["$index"]))
		print $array["$index"];
}

// zips all of the files in the array and returns the location of the zipfile.
// @return the name of the zipfile archive or null if there was a problem making the zipfile.
function zipFiles($files, $dataDir, $dockets)
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
        
        foreach ($dockets['userFile']['tmp_name'] as $key=>$docket)
        {
            $zip->addFile($docket, "dockets" . DIRECTORY_SEPARATOR . $dockets['userFile']['name'][$key]);
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