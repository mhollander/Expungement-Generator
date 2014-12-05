<?php 
/*
*	displayPDF.php
	
*	displays a PDF file stored in the database.  Ensures that you are logged in first and that 
*	you have permission to view this PDF (either an admin or you did this person's expungement)

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

*/

require_once("config.php");
require_once("Attorney.php");



// if the user isn't logged in, then don't display this page.  Tell them they need to log in.
if (!isLoggedIn())
	include("displayNotLoggedIn.php");
else
{
	$attorney = new Attorney($_SESSION["loginUserID"], $db);
	if($GLOBALS['debug'])
		$attorney->printAttorneyInfo();

	// only certain users can see this page
	// we're being overly restrictive right now - only letting CLS lawyers see it
	if ($attorney->getProgramId() != 1)
		print "You must have permission to view this page.";

	else
	{
		$id = $_GET['id'];
		
		$docketFile = $GLOBALS['docketSheetsDir'] . "$id";
		$outputFilename = ".pdf";

		// get the docket number from the arrest ID
		$sql = "SELECT docketNumPrimary as docketNum FROM arrest WHERE arrest.arrestID='" . ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $id) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "'";
		$result = $db->query($sql);
		if (!$result) 
		{
			if ($GLOBALS['debug'])
				die("Could not get the PDF from the databsae.  Perhaps it doesn't exist?:" . $db->error);
			else
				die("Could not get the PDF from the databsae.  Perhaps it doesn't exist?");
		}

		$row = $result->fetch_assoc();
		$result->close();		
		
		// set the filename to the docket number.  at some point we might want to change this
		// so that we have a zip file with all of the docket numbers
		$outputFilename = $row['docketNum'] . $outputFilename;

	
		header('Content-type: application/pdf');
		//header("Content-length: {$row['size']}");
		header("Content-length: " . filesize($docketFile));
		header("Cache-Control: no-cache");
		header("Pragma: no-cache");
		header("Content-Disposition: inline;filename='$outputFilename'");

		// echo $row['data'];
		readfile($docketFile);
		

	}
}

?>