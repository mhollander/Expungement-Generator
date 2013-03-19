<?php

class Person
{
	private $first;
	private $last;
	private $PP;
	private $SID;
	private $SSN;
	private $street;
	private $city;
	private $state;
	private $zip;
	private $alias = array();
	private $personID;
	private $DOB;
	
	public function __construct($first, $last, $PP, $SID, $SSN, $street, $city, $state, $zip, $alias) 
	{
		$this->setFirst($first);
		$this->setLast($last);
		$this->setPP($PP);
		$this->setSID($SID);
		$this->setSSN($SSN);
		$this->setStreet($street);
		$this->setCity($city);
		$this->setState($state);
		$this->setZip($zip);
		$this->setAlias($alias);
	}
	// setters
	public function setFirst($first) { $this->first = $first; }
	public function setLast($last) { $this->last = $last; }
	public function setPP($PP) { $this->PP = $PP; }
	public function setSID($SID) { $this->SID = $SID; }
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
	public function getPP() { return $this->PP; }
	public function getSID() { return $this->SID; }
	public function getSSN() { return $this->SSN; }
	public function getStreet() { return $this->street; }
	public function getCity() { return $this->city; }
	public function getState() { return $this->state; }
	public function getZip() { return $this->zip; }	
	public function getAlias() { return $this->alias; }	
	public function getAliasCommaList() { return implode(", ", $this->alias); }
	public function getPersonID() { return $this->personID; }
	public function getDOB() { return $this->DOB; }
	
	
	// writes a person to the database, if there
	public function writePersonToDB($db)
	{
		// if the person is already in the DB, then just exist
		if ($this->checkInDB($db))
			return;
		
		$sql = "INSERT INTO defendant (firstName, lastName, PP, SID, SSN, DOB, street, city, state, zip, alias) VALUES ('" . $this->getFirst() . "', '" . $this->getLast() . "', '" . $this->getPP() . "', '" . $this->getSID() . "', '" . $this->getSSN() . "', '" . dateConvert($this->getDOB()) . "', '" . $this->getStreet() . "', '" . $this->getCity() . "', '" . $this->getState() . "', '" . $this->getZip() . "', '" . $this->getAliasCommaList() . "')";
		
		$result = mysql_query($sql, $db);
		if (!$result) 
		{
			if ($GLOBALS['debug'])
				die('Could not add the Defendant to the DB:' . mysql_error());
			else
				die('Could not add the Defendant to the DB');
		}

		$this->setPersonID(mysql_insert_id($db));
		return;
	}
	
	// checks to see if a person is already in the db
	public function checkInDB($db)
	{
		$sql = "SELECT defendantID FROM defendant WHERE SSN='" . $this->getSSN() . "'";
		if ($GLOBALS['debug'])
			print $sql;
		$result = mysql_query($sql, $db);
		if (!$result) 
		{
			if ($GLOBALS['debug'])
				die('Could not check if the Defendant was in the DB:' . mysql_error());
			else
				die('Could not check if the Defendant was in the DB');
		}
		
		// if there is a row already, then set the person ID, return true, and get out
		if (mysql_num_rows($result)>0)
		{
			$this->setPersonID(mysql_result($result,0));
			return TRUE;
		}
		else
			return FALSE;
			
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