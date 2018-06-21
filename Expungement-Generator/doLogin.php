<?php

/* @todo add error printing system
/* @todo upgrade registration--set up js checking of fields in login and registration
/****************************************************************
*
*	doLogin.php
*	contains the general utilities to log someone in to the system and set a session
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
****************************************************************/

require_once("config.php");
require_once("utils.php");
require_once("Attorney.php");

session_start();

// check to see if someone is trying to login
if (isset($_POST['username']))
{
	// this is a login attempt; run through the login stuff
	$username = $db->real_escape_string($_POST['username']);
    $query = "SELECT password FROM user WHERE email='$username'";
    $result = $db->query($query);
	if (!$result) 
	{
		if ($GLOBALS['debug'])
			die('Could not connect to database during login:' . $db->error);
		else
			die('Could not connect to database during login.');
	}
	$row = mysqli_fetch_assoc($result);
    $password = $row['password'];
    
    // use bcrypt to verify the pw
    if(!password_verify(md5($_POST['password']),$password))
    {
        // there was a problem logging in; let the user know
		session_destroy();
		print "There was a problem logging you in.  Return to the login screen to try again: <a href='login.php'>Retry Login</a>";
    }
    
    // successful login
    else
    {
        // gather info about the currently logged in user as the login was successful
    	$query = "SELECT userinfo.firstName, userinfo.lastName, user.userid FROM user, userinfo WHERE email='$username' AND user.userid=userinfo.userid";

	    $result = $db->query($query);
    	if (!$result) 
	    {
		    if ($GLOBALS['debug'])
    			die('Could not properly connect to the database during:' . $db->error);
	    	else
		    	die('Could not properly connect to the databse during login.');
    	}
	    $row = mysqli_fetch_assoc($result);
	
        $_SESSION['loginUserFirst'] = $row['firstName'];
		$_SESSION['loginUserLast'] = $row['lastName'];
		$_SESSION['loginUserID'] = $row['userid'];
	}

	$result->close();
}

// check to see if someone is trying to logout
else if (isset($_GET['logout']))
{
	session_destroy();
	$session_name = session_name();
	if ( isset( $_COOKIE[ $session_name ] ) ) 
	{
		if ( setcookie($session_name, '', time()-3600, '/') ) 
		{
			header("Location: " . $baseURL . "/login.php");
			exit();    
		}
	}
}

// check to see if someone is trying to create a new user
else if (isset($_POST['create']) && $_POST['create']=="1")
{	
	// first make sure that this is a superuser
	if (!isLoggedIn())
		$errorMessages->addMessage("Create User Error", "You must be logged in as a superuser to create a user");
	else
	{	
		$attorney = new Attorney($_SESSION["loginUserID"], $db);
		if($GLOBALS['debug'])
			$attorney->printAttorneyInfo();

		// only the superuser can handle this kind of thing 
		if ($attorney->getUserLevel() != 1)
			$errorMessages->addMessage("Create User Error", "You must be the super user logged in to create a user");
			
		// we are a superuser - we can do the creation
		else
			Attorney::createNewAttorneyInDatabase($_POST['createFirst'], $_POST['createLast'], $_POST['createEmail'], $_POST['createBarID'], $_POST['createPassword'], $_POST['createRetypePassword'], $_POST['createHeader'], $_POST['createSignature'], $_POST['createProgram'], $errorMessages, $GLOBALS['db']);
		
		// if errorMessages is still blank, then we can print out a success message!
		if (!$errorMessages->hasMessages())
			print "Registration successful!  Please login to start creating expungement petitions.";
	}
}

// check to see if someone is trying to create a new program
else if (isset($_POST['createProgram']) && $_POST['createProgram']==1)
{
	// first make sure that this is a superuser
	if (!isLoggedIn())
		$errorMessages->addMessage("Create Program Error", "You must be logged in as a superuser to create a program");
    else
    {
		$attorney = new Attorney($_SESSION["loginUserID"], $db);
		if($GLOBALS['debug'])
			$attorney->printAttorneyInfo();

		// only the superuser can handle this kind of thing 
		if ($attorney->getUserLevel() != 1)
			$errorMessages->addMessage("Create Program Error", "You must be the super user to create a program");
			
		// we are a superuser - we can do the creation
		else
        {
            # check to make sure there is a program name.  We probably should check to make sure this
            # program doesn't already exist in the DB, but that isn't a huge deal so I'm not
            # going to add it in for now
            if (!(isset($_POST['createProgramName']) && $_POST['createProgramName'] != ""))
                $errorMessages->addMessage("Create Error", "You did not enter a program name");
            else
            {                           
                // there is a program name, so do the creation
                $sql = "INSERT INTO program (programName, ifp, ifpLanguage, apiKey) values ('" . $GLOBALS['db']->real_escape_string($_POST['createProgramName']) . "', " . $GLOBALS['db']->real_escape_string($_POST['createProgramIFP']) . ", '" . $GLOBALS['db']->real_escape_string($_POST['createProgramIFPLanguage']) . "', '" . password_hash($_POST['createProgramAPIKey'], PASSWORD_DEFAULT) . "');";
                if (!$GLOBALS['db']->query($sql))
                {                     
                    if ($GLOBALS['debug'])
                        die('There was a problem creating the program you entered:' . $GLOBALS['db']->error);  
                    else
                      die('There was a problem creating the program you entered.');
                }                                                                                          
            }
        }
		
		// if errorMessages is still blank, then we can print out a success message!
		if (!$errorMessages->hasMessages())
			print "Registration successful!  You created a program called " . $_POST['createProgramName'] . " with apiKey '" . $_POST['createProgramAPIKey'] . "'.  You should save this key because you will never be able to retrieve it again!  Muwhahahaha.";
	}
    
    
    
}

// check to see if someone is trying to create a new program
else if (isset($_POST['editProgram']) && $_POST['editProgram']==1)
{
	// first make sure that this is a superuser
	if (!isLoggedIn())
		$errorMessages->addMessage("Edit Program Error", "You must be logged in as a superuser to edit a program");
    else
    {
		$attorney = new Attorney($_SESSION["loginUserID"], $db);
		if($GLOBALS['debug'])
			$attorney->printAttorneyInfo();

		// only the superuser can handle this kind of thing 
		if ($attorney->getUserLevel() != 1)
			$errorMessages->addMessage("Edit Program Error", "You must be the super user to edit a program");
			
		// we are a superuser - we can do the edit
		else
        {
            # check to make sure there is a program name.  We probably should check to make sure this
            # program doesn't already exist in the DB, but that isn't a huge deal so I'm not
            # going to add it in for now
            if (!(isset($_POST['programName']) && $_POST['programName'] != ""))
                $errorMessages->addMessage("Edit Error", "You did not enter a program name");
            else
            {                           
                // there is a program name, so do the edit
                $sql = "UPDATE program SET programName='" . $GLOBALS['db']->real_escape_string($_POST['programName']) . "', ifp=" . $GLOBALS['db']->real_escape_string($_POST['programIFP']) . ", ifpLanguage='" . $GLOBALS['db']->real_escape_string($_POST['programIFPLanguage']) . "' WHERE programid=" . $GLOBALS['db']->real_escape_string($_POST['id']) . ";";

                if (!$GLOBALS['db']->query($sql))
                {                     
                    if ($GLOBALS['debug'])
                        die('There was a problem editting the program you entered:' . $GLOBALS['db']->error);  
                    else
                      die('There was a problem editting the program you entered.');
                }
            }
        }
    }
}

// check if someone is trying to edit an API key
else if (isset($_POST['editProgramKey']) && $_POST['editProgramKey']==1)
{
	// first make sure that this is a superuser
	if (!isLoggedIn())
		$errorMessages->addMessage("Reset Program APIKey Error", "You must be logged in as a superuser to edit a program");
    else
    {
		$attorney = new Attorney($_SESSION["loginUserID"], $db);
		if($GLOBALS['debug'])
			$attorney->printAttorneyInfo();

		// only the superuser can handle this kind of thing 
		if ($attorney->getUserLevel() != 1)
			$errorMessages->addMessage("Edit Program APIKey Error", "You must be the super user to edit a program");
			
		// we are a superuser - we can do the edit
		else
        {
            // first generate an API key
            $newAPIKey = bin2hex(openssl_random_pseudo_bytes(32));
            
            // then add it to the database
            $sql = "UPDATE program SET apiKey='" . password_hash($newAPIKey, PASSWORD_DEFAULT) . "' WHERE programid=" . $GLOBALS['db']->real_escape_string($_POST['id']) . ";";

            if (!$GLOBALS['db']->query($sql))
                die('There was a problem editting the apiKey:' . $GLOBALS['db']->error);  
        }
    }
    // if errorMessages is still blank, then we can print out a success message!
    if (!$errorMessages->hasMessages())
		print "API Key updated.  The new API key is '$newAPIKey'.  Save this key now as you can never again retrieve it.  Muwhahahaha.";
}


// check to see if someone is trying to edit their user account
else if (isset($_POST['edit']) && $_POST['edit']=="1")
{	
	// make sure the user is logged in first!
	if (!isLoggedIn())
		$errorMessages->addMessage("Edit User Error", "You must be logged in to edit a user");

	// set the edit attorney to the be the attorney currently logged in
	$thisAttorney = $attorney = new Attorney($_SESSION["loginUserID"], $db);
		
	// if this is the superuser AND there is a field called "id" passed in, then we change the attorney to that attorney
	// This allows superuser editing of other users
	if ($attorney->getUserLevel()==1 && (isset($_REQUEST['id']) && $_REQUEST['id'] != ""))
		$attorney = new Attorney($_REQUEST['id'], $db);
		
	// check to see that they entered a valid password; if not, don't allow the edit.
	if (isset($_POST['registerPassword']) && $_POST['registerPassword'] != '')
	{
        $query = "SELECT password FROM user WHERE userid='" . $_SESSION["loginUserID"] . "';";
        $result = $db->query($query);           
        if (!$result)
        {
            if ($GLOBALS['debug'])
                die('Could not connect to database during user edit:' . $db->error);
            else
                die('Could not connect to database during user edit.');
        }
        
        $row = mysqli_fetch_assoc($result);
        $password = $row['password'];
        
        // use bcrypt to verify the pw
        if(!password_verify(md5($_POST['registerPassword']),$password))
			$errorMessages->addMessage("Edit User Error", "The password you entered was incorrect.");
		$result->close();
	}
	elseif ($thisAttorney->getUserLevel() != 1)
		$errorMessages->addMessage("Edit User Error", "You didn't enter your password.  You have to enter your password to edit your profile.");
	
	
	// if they are logged in and they entered the correct pw, validate the input and update the user
	if (!$errorMessages->hasMessages())
	{
		Attorney::editUser($_POST['registerEmail'], $_POST['newregisterPassword'], $_POST['newregisterRetypePassword'], $_POST['registerFirst'], $_POST['registerLast'], $_POST['registerHeader'], $_POST['registerSignature'], $attorney->getUserID(), $errorMessages, $db);

	}		
	// if there are no error messages, the update was successful
	if (!$errorMessages->hasMessages())
		print "Update successful!";
		
} // end !isLoggedIn();

?>