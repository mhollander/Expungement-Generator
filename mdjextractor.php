<?php

/*******************************************************
*
*	mdjextractor.php
*	reads the webpage http://www.pacourts.us/T/SpecialCourts/MDJList.htm
* 	and extracts all of the court information for each MDJ number
*	NOTE: THIS IS A HELPER FILE THAT IS NOT USED REGULARLY FOR THE FUNCTIONING OF THE EG
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
*******************************************************/


require_once("config.php");
require_once("head.php");

static $firstLineSearch = "/.*County\s\(\d+\)/";
static $districtNumberSearch = "/District Court: (.*)/";
static $phoneSearch = "/Phone: (.*)/";
static $faxSearch = "/Fax: (.*)/";
static $cityStateSearch = "/(.*),\s+(PA)\s+(\d{5})/";

$district;
$judge;
$address;
$city;
$state;
$zip;
$phone;
$fax;
$courtName;

$mdjFile = file("data/mdjlist.txt");

// the file is structured as follows:
/* 
Adams County (51)
District Court: 51-3-04
Mark D. Beauchat 
2267 Fairfield Road
Gettysburg,  PA    17325
Phone: 717-337-3870
Fax: 717-337-0934
-----repeat-----
*/
// so, what I want to do is read the file line by line until I get to the word "County" followed by (nn)

foreach ($mdjFile as $line_num => $line)
{
	// read in each line and grab all of the relevant info.
	// once we have all relevant info, write it to the DB
	// and continue to the next county
	
	// when we match district, we also know that the address is on the second line down
	if (preg_match($districtNumberSearch, $line, $matches))
	{
		$district = trim($matches[1]);
		$judge = trim($mdjFile[$line_num+1]);
		$address = trim($mdjFile[$line_num+2]);
	}
	elseif (preg_match($cityStateSearch, $line, $matches))
	{
		$city = preg_replace("/\s+/"," ", trim($matches[1]));
		$state = preg_replace("/\s+/"," ", trim($matches[2]));
		$zip = preg_replace("/\s+/"," ", trim($matches[3]));
	}
	elseif (preg_match($phoneSearch, $line, $matches))
		$phone = trim($matches[1]);
	elseif (preg_match($faxSearch, $line, $matches))
		$fax = trim($matches[1]);
		
	// we have to check if $district is set, becuase it won't be on the first go around.
	elseif (preg_match($firstLineSearch, $line, $matches) && isset($district))
	{
		$sql = "INSERT INTO mdjcourt (district, judge, address, city, state, zip, phone, fax, courtName) VALUES ('". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $district) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "', '". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $judge) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "', '". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $address) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "', '". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $city) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "', '". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $state) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "', '". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $zip) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "', '". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $phone) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "', '". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $fax) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "', 'Magisterial District Court ". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $district) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "')";
		
		print $sql . "<br/>";
		if (!$db->query($sql))
			die('ERROR:' . $db->error);

		$district = $judge = $address = $cityStreet = $phone = $fax = NULL;
	}
}

// add in the final one, since we didn't get that in the loop.
$sql = "INSERT INTO mdjcourt (district, judge, address, city, state, zip, phone, fax) VALUES ('". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $district) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "', '". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $judge) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "', '". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $address) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "', '". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $city) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "', '". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $state) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "', '". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $zip) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "', '". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $phone) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "', '". ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $fax) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "')";
		
print $sql;
if (!$db->query($sql);
	die('ERROR:' . $db->error);


?>