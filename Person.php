<?php

/***************************
*	Person.php
*	the main class representing a criminal defendant in a case.  contains all of the important helper functions for a Person.
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
***************************/

class Person
{
	private $first;
	private $last;
	private $SSN;
	private $street;
	private $city;
	private $state;
	private $zip;
	private $alias = array();
	private $personID;
	private $DOB;
	
	public function __construct($first, $last, $SSN, $street, $city, $state, $zip) 
	{
		$this->setFirst($first);
		$this->setLast($last);
		$this->setSSN($SSN);
		$this->setStreet($street);
		$this->setCity($city);
		$this->setState($state);
		$this->setZip($zip);
	}
	// setters
	public function setFirst($first) { $this->first = $first; }
	public function setLast($last) { $this->last = $last; }
	public function setSSN($SSN) { $this->SSN = $SSN; }
	public function setStreet($street) { $this->street = $street; }
	public function setCity($city) { $this->city = $city; }
	public function setState($state) { $this->state = $state; }
	public function setZip($zip) { $this->zip = $zip; }	
	public function setAlias($alias) { $this->alias = $alias; }	
	public function setPersonID($personID) { $this->personID = $personID; }	
	public function setDOB($dob) { $this->DOB = $dob; }	
	
	// getters
	public function getFirst() { return $this->first; }
	public function getLast() { return $this->last; }
	public function getSSN() { return $this->SSN; }
	public function getStreet() { return $this->street; }
	public function getCity() { return $this->city; }
	public function getState() { return $this->state; }
	public function getZip() { return $this->zip; }	
	public function getAlias() { return $this->alias; }	
	public function getAliasCommaList() { return implode("; ", $this->alias); }
	public function getPersonID() { return $this->personID; }
	public function getDOB() { return $this->DOB; }
	
	
	public function addAliases($aliases)
	{
		// merge the current alias array with the new alias array, but cut out duplicate entries
		// This could be done more quickly if I wrong my own function for array_unique, but there are so few aliases generally, I don't think it matters
		$this->setAlias(array_unique(array_merge($this->getAlias(), $aliases)));
	}
	
	// writes a person to the database, if there
	public function writePersonToDB($db)
	{
		// if the person is already in the DB, then just exist
		error_log("Trying to write person to db");
		if ($this->checkInDB($db))
			return;
		
		error_log("person's not already in db");
		$sql = "INSERT INTO defendant (firstName, lastName, PP, SID, SSN, DOB, street, city, state, zip, alias) VALUES ('" . $this->getFirst() . "', '" . $this->getLast() . "', 0, '', '" . $this->getSSN() . "', '" . dateConvert($this->getDOB()) . "', '" . $this->getStreet() . "', '" . $this->getCity() . "', '" . $this->getState() . "', '" . $this->getZip() . "', '" . $this->getAliasCommaList() . "')";
	 	error_log("querying db for person now with query: ");	
		error_log($sql);
		if (!$db->query($sql))
		{
			error_log("could not add defendant to the db. Query was");
			error_log($sql);
			if ($GLOBALS['debug'])
				die('Could not add the Defendant to the DB:' . $db->error);
			else
				die('Could not add the Defendant to the DB');
		}
		error_log("almost done writing person to db");
		$this->setPersonID($db->insert_id);
		return;
	}
	
	// checks to see if a person is already in the db
	public function checkInDB($db)
	{
		$sql = "SELECT defendantID FROM defendant WHERE SSN='" . $this->getSSN() . "'";
		if ($GLOBALS['debug'])
			print $sql;
		$result = $db->query($sql);
		if (!$result) 
		{
			if ($GLOBALS['debug'])
				die('Could not check if the Defendant was in the DB:' . $db->error);
			else
				die('Could not check if the Defendant was in the DB');
		}
		
		// if there is a row already, then set the person ID, return true, and get out
		if ($result->num_rows>0)
		{
			$personID = $result->fetch_array();
			$this->setPersonID($personID[0]);
			$result->close();
			return TRUE;
		}
		else
		{
			$result->close();
			return FALSE;
		}
	}
	
	public function getAge()
	{
		$birthDate = $this->getDOB();
         //explode the date to get month, day and year
         $birthDate = explode("/", $birthDate);
         //get age from date or birthdate
         $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md") ? ((date("Y")-$birthDate[2])-1):(date("Y")-$birthDate[2]));
         return $age;
	}
}

?>
