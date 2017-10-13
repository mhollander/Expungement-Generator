<?php

/******************************************
*
*	Attorney.php
*	The main container for an Attorney.  Describes an attorney, has read/write/other helper functions
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
**************************************************/

class Attorney
{
	private $firstName;
	private $lastName;
	private $petitionHeader;
	private $petitionSignature;
	private $userid;
	private $ifp;
    private $ifpLanguage;
	private $email;
	private $programID;
	private $programName;
	private $userLevel;
	
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
	public function setIFPLanguage($ifpLanguage) { $this->ifpLanguage = $ifpLanguage; }
	public function setEmail($email) { $this->email = $email; }
	public function setProgramID($programID) { $this->programID = $programID; }
	public function setProgramName($programName) { $this->programName = $programName; }
	public function setUserLevel($userLevel) { $this->userLevel = $userLevel; }
	public function setIsAnon($anon) { $this->anon = $anon; }
	
	// getters
	public function getFirstName() { return $this->firstName; }
	public function getLastName() { return $this->lastName; }
	public function getPetitionHeader() { return $this->petitionHeader; }
	public function getPetitionSignature() { return $this->petitionSignature; }
	public function getUserID() { return $this->userid; }
	public function getIFP() { return $this->ifp; }
    public function getIFPLanguage() { return $this->ifpLanguage; }
	public function getEmail() { return $this->email; }
	public function getProgramID() { return $this->programID; }
	public function getProgramName() { return $this->programName; }
	public function getUserLevel() { return $this->userLevel; }
	public function getIsAnon() { return $this->anon; }
	
	// Sets the attorney information by taking a userID, connecting to the database, and pulling
	// the attorney information from the database.  Requires a db handle to be passed in.
	public function setAttorneyInfoFromUserID($db)
	{
		if (isset($this->userid))
		{
			$query = "SELECT * FROM user, userinfo, program WHERE user.userid='".$this->userid."' AND user.userid=userinfo.userid AND program.programID=userinfo.programID";
			$result = $db->query($query);
			if (!$result) 
			{
				if ($GLOBALS['debug'])
					die('Could not get the Attorney Information from the DB:' . $db->error);
				else
					die('Could not get the Attorney Information from the DB');
			}
			$row = $result->fetch_assoc();
			$this->setIsAnon($row['anonymous']);
			
			// if this is an anonymous user, we want to blank out their name, etc.... so that it doesn't appear on petitions	
			if ($this->getIsAnon())
			{
				$this->setFirstName("");
				$this->setLastName("");
				$this->setPetitionHeader("");
				$this->setPetitionSignature("");
				$this->setIFP(0);
                $this->setIFPLanguage("");
				$this->setEmail($row['email']);			
				$this->setProgramID($row['programID']);
				$this->setProgramName($row['programName']);
				$this->setUserLevel($row['userLevel']);
			}
			else
			{
				$this->setFirstName($row['firstName']);
				$this->setLastName($row['lastName']);
				$this->setPetitionHeader($row['petitionHeader']);
				$this->setPetitionSignature($row['petitionSignature']);
				$this->setIFP($row['ifp']);
                $this->setIFPLanguage($row['ifpLanguage']);
				$this->setEmail($row['email']);			
				$this->setProgramID($row['programID']);
				$this->setProgramName($row['programName']);
				$this->setUserLevel($row['userLevel']);
			}			
			$result->close();
		}
	}
	
	public function getIFPMessage()
	{
        return $this->getIFPLanguage();
#		return $this->getProgramName() . " is a non-profit legal services organization that provides free legal assistance to low-income individuals.  I, attorney for the petitioner, certify that petitioner meets the financial eligibility standards for representation by " . $this->getProgramName() . " and that I am providing free legal service to petitioner.";
	}
	
	// prints attorney information to the screen in basic format
	public function printAttorneyInfo()
	{
		printf("Attorney: %s %s <br/>Attorney Signature: %s <br/>Attorney Header: %s <br />Attorney IFP: %s", $this->getFirstName(), $this->getLastName(), $this->getPetitionSignature(), $this->getPetitionHeader(), $this->getIFP());
	}
	
	// takes all of the relevant information about an attorney and creates on.  Modifies the errorMessages object if there are
	// errors.
	public static function createNewAttorneyInDatabase($first, $last, $email, $barID, $password, $retypePassword, $header, $signature, $program, &$errorMessages, $db)
	{
		// first validate all of the input
		// check the email address
		if (!(isset($email) && $email != ""))
			$errorMessages->addMessage("Create Error", "You did not enter an email address");
		else 
		{
			if (!filter_var($email, FILTER_VALIDATE_EMAIL))
				$errorMessages->addMessage("Create Error", "You entered an invalid email address.");
			
			else
			{
				$emailExists = Attorney::checkIfEmailExists($email, '', $db);
			
				if ($emailExists > 0)
					$errorMessages->addMessage("Create Error", "You entered an email address that is already being used by another user.");
			}
		}
			

		// check the passwords
		if (!((isset($password) && isset($retypePassword)) && $password != ""))
			$errorMessages->addMessage("Create Error", "You forgot to put a password or password confirmation in.");
		else
		{
			if ($password != $retypePassword)
				$errorMessages->addMessage("Create Error", "You did not entering matching passwords.  You need to type the same password in twice.");
		}
		
		// make sure they entered their name
		if (!isset($first) || $first == "")
			$errorMessages->addMessage("Create Error", "You didn't enter your first name!");
		if (!isset($last) || $last == "")
			$errorMessages->addMessage("Create Error", "You didn't enter your last name!");

		// bar id needs to be checked to be a number and be unique in the DB
		if (!isset($barID) || $barID == "")
			$errorMessages->addMessage("Create Error", "You didn't enter a PA Bar ID.");
		else if (!filter_var($barID, FILTER_VALIDATE_INT))
			$errorMessages->addMessage("Create Error", "You didn't enter a number for your PA Bar ID.  A PA Bar ID must be a number.");
		else
		{
			$barIDExists = Attorney::checkIfBarIDExists($barID, $db);
			if ($barIDExists > 0)
				$errorMessages->addMessage("Create Error", "You entered a bar ID that is already being used by another user.  You can only have one registration per bar ID.");
		}
			
		if (!isset($header) || $header == "")
			$errorMessages->addMessage("Create Error", "You didn't enter anything for the petition header.");
		
		if (!isset($signature) || $signature == "")
			$errorMessages->addMessage("Create Error", "You didn't enter anything for the petition signature.");
		
		if (!$errorMessages->hasMessages())
		{
			// if we get to here, then all is well; register the user
			$query = "INSERT INTO user (email, password) VALUES('" . $db->real_escape_string($email) . "', '" . $db->real_escape_string(password_hash(md5($password), PASSWORD_BCRYPT)) . "')";
			if (!$db->query($query))
			{
				if ($GLOBALS['debug'])
					die('There was a problem registering your email and password in the database:' . $db->error);
				else
					die('There was a problem registering your email and password in the database.');
			}
			$registerUserID = $db->insert_id;
			
			// now insert information into userinfo
			$query = "INSERT INTO userinfo (userid, firstName, lastName, petitionHeader, petitionSignature, pabarid, programID) VALUES($registerUserID, '" . $db->real_escape_string($first) . "', '" . $db->real_escape_string($last) . "', '" . $db->real_escape_string($header) . "', '" . $db->real_escape_string($signature) . "', '" . $db->real_escape_string($barID) . "', '" . $db->real_escape_string($program) . "')";
			if (!$db->query($query))
			{
				if ($GLOBALS['debug'])
					die('There was a problem registering your user information in the database:' . $db->error);
				else
					die('There was a problem registering your user information in the database.');
			}
			
		}

	}

	public static function editUser($email, $password, $retypePassword, $first, $last, $header, $signature, $attorneyID, &$errorMessages, $db)
	{
		// first validate all of the input
		// check the email address
		if (isset($email) && $email != "")
		{
			if (!filter_var($email, FILTER_VALIDATE_EMAIL))
				$errorMessages->addMessage("Edit User Error", "You entered an invalid email address.");
			
			else
			{
				// check to see that this email address is NOT already in the database by someone else
				$emailExists = Attorney::checkIfEmailExists($email, $attorneyID, $db);
			
				if ($emailExists > 0)
					$errorMessages->addMessage("Edit User Error", "You entered an email address that is already being used by another user.");
			}
		}
		else
			$errorMessages->addMessage("Edit User Error", "You did not enter an email address");

		// check the passwords
		if ((isset($password) && isset($retypePassword)) && $password != "")
		{
			if ($password != $retypePassword)
				$errorMessages->addMessage("Edit User Error", "You did not entering matching passwords.  You need to type the same password in twice.");
		}

			// make sure they entered their name
		if (!isset($first) || $first == "")
			$errorMessages->addMessage("Edit User Error", "You didn't enter your first name!");
		if (!isset($last) || $last == "")
			$errorMessages->addMessage("Edit User Error", "You didn't enter your last name!");
			
		// header information
		if (!isset($header) || $header == "")
			$errorMessages->addMessage("Edit User Error", "You didn't enter anything for the petition header.");
		
		// signature information
		if (!isset($signature) || $signature == "")
			$errorMessages->addMessage("Edit User Error", "You didn't enter anything for the petition signature.");
		
		if (!$errorMessages->hasMessages())
		{
			// if we get to here, then all is well; update the user
			$query = "UPDATE user SET email='".$db->real_escape_string($email) . "' WHERE userid='" . $attorneyID . "'";
			if(!$db->query($query))
			{
				if ($GLOBALS['debug'])
					die('There was a problem updating your email in the database:' . $db->error);
				else
					die('There was a problem updating your email in the database.');
			}
			
			//update the password only if they set a new password
			if (isset($password) && $password != "")
			{
				$password = password_hash(md5($password), PASSWORD_BCRYPT); 
                $password = $db->real_escape_string($password);
				$query = "UPDATE user SET password='". $password . "' WHERE userid='" . $attorneyID . "'";

				if(!$db->query($query))
				{
					if ($GLOBALS['debug'])
						die('There was a problem updating your password in the database:' . $db->error);
					else
						die('There was a problem updating your password in the database.');
				}
			}
			// now update information into userinfo
			$query = "UPDATE userinfo SET firstName='" . $db->real_escape_string($first)  . "', lastName='" . $db->real_escape_string($last) . "', petitionHeader='" . $db->real_escape_string($header) . "', petitionSignature='" . $db->real_escape_string($signature) . "' WHERE userid='" . $attorneyID ."'";
			if(!$db->query($query))
			{
				if ($GLOBALS['debug'])
					die('There was a problem updating your user information in the database:' . $db->error);
				else
					die('There was a problem updating your user information in the database.');
			}
		}
	}
	
	// returns an array with the total number of users with this email already in it
	public static function checkIfEmailExists($email, $id, $db)
	{
		// check to see that this email address is NOT already in the database
		$query = "SELECT COUNT(email) FROM user WHERE email='".$db->real_escape_string($email)."'";
		
		if ($id != "")
			$query .= " AND userid != '".$id."'";
			
		$result = $db->query($query);
		if (!$result) 
		{
			if ($GLOBALS['debug'])
				die('Could not query the DB for email address:' . $db->error);
			else
				die('Could not check the DB for an email address.');
		}
		$total = $result->fetch_array(); 
		$result->close();
		return $total[0];
	}
	
	public static function checkIfBarIDExists($barID, $db)
	{
		// check to see that this bar idis NOT already in the database
		$query = "SELECT COUNT(userid) FROM userinfo WHERE pabarid=" . $db->real_escape_string($barID);
		$result = $db->query($query);
		if (!$result) 
		{
			if ($GLOBALS['debug'])
				die('Could not query the DB for a bar ID during registration:' . $db->error);
			else
				die('Could not check the DB for your bar ID while registering you, for some strange reason.');
		}
		$total = $result->fetch_array(); 
		$result->close();
		return $total[0];
	}
	
	// goes into the DB and updates the userinfo table to increase the total number of petitions created by this attorney
	public function updateTotalPetitions($add, $db)
	{
		$query = "UPDATE userinfo SET totalPetitions = totalPetitions + $add WHERE userID = " . $this->getUserID();
		error_log("Using query: " . $query);
		if(!$db->query($query))
		{
			if ($GLOBALS['debug']) {
				die('Could not query the DB for a bar ID during registration:' . $db->error);
			} else {
				error_log("could not check the db for users bar id.");
				die('Could not check the DB for your bar ID while registering you, for some strange reason.');
			}
		}
		
		return;
	}
    
    // returns the individual's electronic signature, which is generally their first and last name, 
    // although is a series of underscores for people who don't want an electronic signature
    public function getElectronicSig()
    {
        // returns the electronic signature of each attorney.  
        // this shoudl really be int he database, but isn't worth the time for a schema change.
        // Only one person has requested that he have a different sig; I will leave this as is until
        // more people ask
        
        if (in_array($this->getUserID(), array("27", "36"))) // Dean Beer and Erica Briant
            return "____________";
        else
          return $this->getFirstName() . " " . $this->getLastName();
    }
	
}
?>
