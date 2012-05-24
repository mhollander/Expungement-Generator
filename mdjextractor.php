<?php

//	mdjextractor.php
//	reads the webpage http://www.pacourts.us/T/SpecialCourts/MDJList.htm
// 	and extracts all of the court information for each MDJ number
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
		$sql = "INSERT INTO mdjcourt (district, judge, address, city, state, zip, phone, fax, courtName) VALUES ('". mysql_real_escape_string($district) . "', '". mysql_real_escape_string($judge) . "', '". mysql_real_escape_string($address) . "', '". mysql_real_escape_string($city) . "', '". mysql_real_escape_string($state) . "', '". mysql_real_escape_string($zip) . "', '". mysql_real_escape_string($phone) . "', '". mysql_real_escape_string($fax) . "', 'Magisterial District Court ". mysql_real_escape_string($district) . "')";
		
		print $sql . "<br/>";
		$result = mysql_query($sql, $db);
		if (!$result) 
			die('ERROR:' . mysql_error());

		$district = $judge = $address = $cityStreet = $phone = $fax = NULL;
	}
}

// add in the final one, since we didn't get that in the loop.
$sql = "INSERT INTO mdjcourt (district, judge, address, city, state, zip, phone, fax) VALUES ('". mysql_real_escape_string($district) . "', '". mysql_real_escape_string($judge) . "', '". mysql_real_escape_string($address) . "', '". mysql_real_escape_string($city) . "', '". mysql_real_escape_string($state) . "', '". mysql_real_escape_string($zip) . "', '". mysql_real_escape_string($phone) . "', '". mysql_real_escape_string($fax) . "')";
		
print $sql;
$result = mysql_query($sql, $db);
if (!$result) 
	die('ERROR:' . mysql_error());



?>