<?php

class Attorney
{
	private $firstName;
	private $lastName;
	private $petitionHeader;
	private $petitionSignature;
	private $userid;
	private $ifp;
	private $email;
	private $programID;
	private $programName;
	
	public function __construct($userid, $db) 
	{
		$this->setUserID($userid);
		$this->setAttorneyInfoFromUserID($db);
	}
	// setters
	public function setFirstName($firstName) { $this->firstName = $firstName; }
	public function setLastName($lastName) { $this->lastName = $lastName; }
	public function setPetitionHeader($petitionHeader) { $this->petitionHeader = $petitionHeader; }
	public function setPetitionSignature($petitionSignature) { $this->petitionSignature = $petitionSignature; }
	public function setUserID($userid) { $this->userid = $userid; }
	public function setIFP($ifp) { $this->ifp = $ifp; }
	public function setEmail($email) { $this->email = $email; }
	public function setProgramID($programID) { $this->programID = $programID; }
	public function setProgramName($programName) { $this->programName = $programName; }

	// getters
	public function getFirstName() { return $this->firstName; }
	public function getLastName() { return $this->lastName; }
	public function getPetitionHeader() { return $this->petitionHeader; }
	public function getPetitionSignature() { return $this->petitionSignature; }
	public function getUserID() { return $this->userid; }
	public function getIFP() { return $this->ifp; }
	public function getEmail() { return $this->email; }
	public function getProgramID() { return $this->programID; }
	public function getProgramName() { return $this->programName; }
	
	// Sets the attorney information by taking a userID, connecting to the database, and pulling
	// the attorney information from the database.  Requires a db handle to be passed in.
	public function setAttorneyInfoFromUserID($db)
	{
		if (isset($this->userid))
		{
			$query = "SELECT * FROM user, userinfo, program WHERE user.userid='".$this->userid."' AND user.userid=userinfo.userid AND program.programID=userinfo.programID";
			$result = mysql_query($query, $db);
			if (!$result) 
			{
				if ($GLOBALS['debug'])
					die('Could not get the Attorney Information from the DB:' . mysql_error());
				else
					die('Could not get the Attorney Information from the DB');
			}
			$row = mysql_fetch_assoc($result);
			$this->setFirstName($row['firstName']);
			$this->setLastName($row['lastName']);
			$this->setPetitionHeader($row['petitionHeader']);
			$this->setPetitionSignature($row['petitionSignature']);
			$this->setIFP($row['ifp']);
			$this->setEmail($row['email']);			
			$this->setProgramID($row['programID']);
			$this->setProgramName($row['programName']);
			
			mysql_free_result($result);
			}
	}
	
	public function getIFPMessage()
	{
		return $this->getProgramName() . " is a non-profit legal services organization that provides free legal assistance to low-income individuals.  I, attorney for the petitioner, certify that petitioner meets the financial eligibility standards for representation by " . $this->getProgramName() . " and that I am providing free legal service to petitioner.";
	}
	
	// prints attorney information to the screen in basic format
	public function printAttorneyInfo()
	{
		printf("Attorney: %s %s <br/>Attorney Signature: %s <br/>Attorney Header: %s <br />Attorney IFP: %s", $this->getFirstName(), $this->getLastName(), $this->getPetitionSignature(), $this->getPetitionHeader(), $this->getIFP());
	}
}

?>