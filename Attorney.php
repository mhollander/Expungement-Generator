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
	
	// getters
	public function getFirstName() { return $this->firstName; }
	public function getLastName() { return $this->lastName; }
	public function getPetitionHeader() { return $this->petitionHeader; }
	public function getPetitionSignature() { return $this->petitionSignature; }
	public function getUserID() { return $this->userid; }
	public function getIFP() { return $this->ifp; }
	public function getEmail() { return $this->email; }
	
	// Sets the attorney information by taking a userID, connecting to the database, and pulling
	// the attorney information from the database.  Requires a db handle to be passed in.
	public function setAttorneyInfoFromUserID($db)
	{
		if (isset($this->userid))
		{
			$query = "SELECT * FROM user, userinfo WHERE user.userid='".$this->userid."' AND user.userid=userinfo.userid";
			$result = mysql_query($query, $db);
			if (!$result) 
			{
				if ($GLOBALS['debug'])
					die('Could not set the Attorney Information from the DB:' . mysql_error());
				else
					die('Could not set the Attorney Information from the DB');
			}
			$row = mysql_fetch_assoc($result);
			$this->setFirstName($row['firstName']);
			$this->setLastName($row['lastName']);
			$this->setPetitionHeader($row['petitionHeader']);
			$this->setPetitionSignature($row['petitionSignature']);
			$this->setIFP($row['ifp']);
			$this->setEmail($row['email']);			
			
			mysql_free_result($result);
			}
	}
	
	// prints attorney information to the screen in basic format
	public function printAttorneyInfo()
	{
		printf("Attorney: %s %s <br/>Attorney Signature: %s <br/>Attorney Header: %s <br />Attorney IFP: %s", $this->getFirstName(), $this->getLastName(), $this->getPetitionSignature(), $this->getPetitionHeader(), $this->getIFP());
	}
}

?>