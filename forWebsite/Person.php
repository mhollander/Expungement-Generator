<?php

class Person
{
	private $PP;
	private $SID;
	private $SSN;
	private $street;
	private $street2;
	private $city;
	private $state;
	private $zip;

	
	public function __construct($PP, $SID, $SSN, $street, $street2, $city, $state, $zip) 
	{
		$this->setPP($PP);
		$this->setSID($SID);
		$this->setSSN($SSN);
		$this->setStreet($street);
		$this->setStreet2($street2);
		$this->setCity($city);
		$this->setState($state);
		$this->setZip($zip);
	}
	// setters
	public function setPP($PP) { $this->PP = $PP; }
	public function setSID($SID) { $this->SID = $SID; }
	public function setSSN($SSN) { $this->SSN = $SSN; }
	public function setStreet($street) { $this->street = $street; }
	public function setStreet2($street2) { $this->street2 = $street2; }
	public function setCity($city) { $this->city = $city; }
	public function setState($state) { $this->state = $state; }
	public function setZip($zip) { $this->zip = $zip; }	
	
	// getters
	public function getPP() { return $this->PP; }
	public function getSID() { return $this->SID; }
	public function getSSN() { return $this->SSN; }
	public function getStreet() { return $this->street; }
	public function getStreet2() { return $this->street2; }
	public function getCity() { return $this->city; }
	public function getState() { return $this->state; }
	public function getZip() { return $this->zip; }	
	
}

?>