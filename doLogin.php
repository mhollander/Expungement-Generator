<?php

/* @todo add error printing system
/* @todo upgrade registration--set up js checking of fields in login and registration
/****************************************************************
*
*	doLogin.php
*	contains the general utilities to log someone in to the system and set a session
*
*	Copyright 2011 Michael Hollander
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
	$password = md5($db->real_escape_string($_POST['password']));
	
	$query = "SELECT userinfo.firstName, userinfo.lastName, user.userid FROM user, userinfo WHERE email='$username' AND password='$password' AND user.userid=userinfo.userid";

	$result = $db->query($query);
	if (!$result) 
	{
		if ($GLOBALS['debug'])
			die('Could not login:' . $db->error);
		else
			die('Could not login for some strange reason.');
	}
	$row = mysqli_fetch_assoc($result);
	
	if ($row['firstName'])
	{
		// the login was a success!  grab the username info and start a session
		
		// if we want to "remember login"
		if (isset($_POST['rememberMe']))
		{
			session_set_cookie_params('60000000'); // more than a year.
			session_regenerate_id(true); 
		}

		$_SESSION['loginUserFirst'] = $row['firstName'];
		$_SESSION['loginUserLast'] = $row['lastName'];
		$_SESSION['loginUserID'] = $row['userid'];
	}
	else
	{
		// there was a problem logging in; let the user know
		session_destroy();
		print "There was a problem logging you in.  Return to the login screen to try again: <a href='login.php'>Retry Login</a>";
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
		$password = md5($db->real_escape_string($_POST['registerPassword']));
	
		$query = "SELECT * FROM user WHERE userid='" . $_SESSION["loginUserID"] . "' AND password='$password'";

		$result = $db->query($query);
		if (!$result || $result->num_rows == 0) 
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