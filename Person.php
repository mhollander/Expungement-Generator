<?php

class Person
{
	private $first;
	private $last;
	private $PP;
	private $SID;
	private $SSN;
	private $street;
	private $street2;
	private $city;
	private $state;
	private $zip;
	private $alias = array();

	
	public function __construct($first, $last, $PP, $SID, $SSN, $street, $street2, $city, $state, $zip, $alias) 
	{
		$this->setFirst($first);
		$this->setLast($last);
		$this->setPP($PP);
		$this->setSID($SID);
		$this->setSSN($SSN);
		$this->setStreet($street);
		$this->setStreet2($street2);
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
	public function setStreet2($street2) { $this->street2 = $street2; }
	public function setCity($city) { $this->city = $city; }
	public function setState($state) { $this->state = $state; }
	public function setZip($zip) { $this->zip = $zip; }	
	public function setAlias($alias) { $this->alias = $alias; }	
	
	// getters
	public function getFirst() { return $this->first; }
	public function getLast() { return $this->last; }
	public function getPP() { return $this->PP; }
	public function getSID() { return $this->SID; }
	public function getSSN() { return $this->SSN; }
	public function getStreet() { return $this->street; }
	public function getStreet2() { return $this->street2; }
	public function getCity() { return $this->city; }
	public function getState() { return $this->state; }
	public function getZip() { return $this->zip; }	
	public function getAlias() { return $this->alias; }	
	public function getAliasCommaList() { return implode(", ", $this->alias); }
}

?>