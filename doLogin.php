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

session_start();

// check to see if someone is trying to login
if (isset($_POST['username']))
{
	// this is a login attempt; run through the login stuff
	$username = mysql_escape_string($_POST['username']);
	$password = md5(mysql_escape_string($_POST['password']));
	
	$query = "SELECT userinfo.firstName, userinfo.lastName, user.userid FROM user, userinfo WHERE email='$username' AND password='$password' AND user.userid=userinfo.userid";

	$result = mysql_query($query, $db);
	if (!$result) 
	{
		if ($GLOBALS['debug'])
			die('Could not login:' . mysql_error());
		else
			die('Could not login for some strange reason.');
	}
	$row = mysql_fetch_assoc($result);
	
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

// check to see if someone is trying to register
else if (isset($_POST['register']) && $_POST['register']=="1")
{	

	// first validate all of the input
	// check the email address
	if (isset($_POST['registerEmail']) && $_POST['registerEmail'] != "")
	{
		if (!filter_var($_POST['registerEmail'], FILTER_VALIDATE_EMAIL))
			$errorMessages->addMessage("Registration Error", "You entered an invalid email address.");
		
		else
		{
			// check to see that this email address is NOT already in the database
			$query = "SELECT COUNT(email) FROM user WHERE email='".mysql_escape_string($_POST['registerEmail'])."'";
			$result = mysql_query($query, $db);
			if (!$result) 
			{
				if ($GLOBALS['debug'])
					die('Could not query the DB for email address during registration:' . mysql_error());
				else
					die('Could not check the DB for an email address while registering you, for some strange reason.');
			}
			$total = mysql_fetch_array($result); 
			if ($total[0] > 0)
				$errorMessages->addMessage("Registration Error", "You entered an email address that is already being used by another user.");
		}
	}
	else
		$errorMessages->addMessage("Registration Error", "You did not enter an email address");

	// check the passwords
	if ((isset($_POST['registerPassword']) && isset($_POST['registerRetypePassword'])) && $_POST['registerPassword'] != "")
	{
		if ($_POST['registerPassword'] != $_POST['registerRetypePassword'])
			$errorMessages->addMessage("Registration Error", "You did not entering matching passwords.  You need to type the same password in twice.");
	}
	else
		$errorMessages->addMessage("Registration Error", "You forgot to put a password or password confirmation in.");

		// make sure they entered their name
	if (!isset($_POST['registerFirst']) || $_POST['registerFirst'] == "")
		$errorMessages->addMessage("Registration Error", "You didn't enter your first name!");
	if (!isset($_POST['registerLast']) || $_POST['registerLast'] == "")
		$errorMessages->addMessage("Registration Error", "You didn't enter your last name!");

	// bar id needs to be checked to be a number and to exit
	if (!isset($_POST['registerBarID']) || $_POST['registerBarID'] == "")
		$errorMessages->addMessage("Registration Error", "You didn't enter a PA Bar ID.");
	else if (!filter_var($_POST['registerBarID'], FILTER_VALIDATE_INT))
		$errorMessages->addMessage("Registration Error", "You didn't enter a number for your PA Bar ID.  A PA Bar ID must be a number.");
	else
	{
		// check to see that this email address is NOT already in the database
		$query = "SELECT COUNT(userid) FROM userinfo WHERE pabarid=".mysql_escape_string($_POST['registerBarID'])."";
		$result = mysql_query($query, $db);
		if (!$result) 
		{
			if ($GLOBALS['debug'])
				die('Could not query the DB for a bar ID during registration:' . mysql_error());
			else
				die('Could not check the DB for your bar ID while registering you, for some strange reason.');
		}
		$total = mysql_fetch_array($result); 
		if ($total[0] > 0)
			$errorMessages->addMessage("Registration Error", "You entered a bar ID that is already being used by another user.  You can only have one registration per bar ID.");
	}
		
	if (!isset($_POST['registerHeader']) || $_POST['registerHeader'] == "")
		$errorMessages->addMessage("Registration Error", "You didn't enter anything for the petition header.");
	
	if (!isset($_POST['registerSignature']) || $_POST['registerSignature'] == "")
		$errorMessages->addMessage("Registration Error", "You didn't enter anything for the petition signature.");
	
	if (!$errorMessages->hasMessages())
	{
		// if we get to here, then all is well; register the user
		$query = "INSERT INTO user (email, password) VALUES('". mysql_escape_string($_POST['registerEmail']) . "', '" . mysql_escape_string(md5($_POST['registerPassword'])) . "')";
		$result = mysql_query($query, $db);
		if (!$result) 
		{
			if ($GLOBALS['debug'])
				die('There was a problem registering your email and password in the database:' . mysql_error());
			else
				die('There was a problem registering your email and password in the database.');
		}
		$registerUserID = mysql_insert_id();
		
		// now insert information into userinfo
		$query = "INSERT INTO userinfo (userid, firstName, lastName, petitionHeader, petitionSignature, pabarid) VALUES($registerUserID, '" . mysql_escape_string($_POST['registerFirst']) . "', '" . mysql_escape_string($_POST['registerLast']) . "', '" . mysql_escape_string($_POST['registerHeader']) . "', '" . mysql_escape_string($_POST['registerSignature']) . "', '" . mysql_escape_string($_POST['registerBarID']) . "')";
		$result = mysql_query($query, $db);
		if (!$result) 
		{
			if ($GLOBALS['debug'])
				die('There was a problem registering your user information in the database:' . mysql_error());
			else
				die('There was a problem registering your user information in the database.');
		}
		
		print "Registration successful!  Please login to start creating expungement petitions.";
		
	}
}
	
// check to see if someone is trying to edit their user account
else if (isset($_POST['edit']) && $_POST['edit']=="1")
{	
	// make sure the user is logged in first!
	if (!isLoggedIn())
		$errorMessages->addMessage("Edit User Error", "You must be logged in to edit a user");

	// check to see that they entered a valid password; if not, don't allow the edit.
	if (isset($_POST['registerPassword']))
	{
		$password = md5(mysql_escape_string($_POST['registerPassword']));
	
		$query = "SELECT * FROM user WHERE userid='" . $_SESSION["loginUserID"] . "' AND password='$password'";

		$result = mysql_query($query, $db);
		if (!$result || mysql_num_rows($result) == 0) 
			$errorMessages->addMessage("Edit User Error", "The password you entered was incorrect.");
	}
	else 
		$errorMessages->addMessage("Edit User Error", "You didn't enter your password.  You have to enter your password to edit your profile.");
	
	
	// if they are logged in and they entered the correct pw, validate the input and update the user
	if (!$errorMessages->hasMessages())
	{
		// first validate all of the input
		// check the email address
		if (isset($_POST['registerEmail']) && $_POST['registerEmail'] != "")
		{
			if (!filter_var($_POST['registerEmail'], FILTER_VALIDATE_EMAIL))
				$errorMessages->addMessage("Registration Error", "You entered an invalid email address.");
			
			else
			{
				// check to see that this email address is NOT already in the database by someone else
				$query = "SELECT COUNT(email) FROM user WHERE email='".mysql_escape_string($_POST['registerEmail'])."' AND userid != '" . $_SESSION["loginUserID"] . "'";
				$result = mysql_query($query, $db);
				if (!$result) 
				{
					if ($GLOBALS['debug'])
						die('Could not query the DB for email address during registration:' . mysql_error());
					else
						die('Could not check the DB for an email address while registering you, for some strange reason.');
				}
				$total = mysql_fetch_array($result); 
				if ($total[0] > 0)
					$errorMessages->addMessage("Edit User Error", "You entered an email address that is already being used by another user.");
			}
		}
		else
			$errorMessages->addMessage("Registration Error", "You did not enter an email address");

		// check the passwords
		if ((isset($_POST['newregisterPassword']) && isset($_POST['newregisterRetypePassword'])) && $_POST['newregisterPassword'] != "")
		{
			if ($_POST['newregisterPassword'] != $_POST['newregisterRetypePassword'])
				$errorMessages->addMessage("Edit User", "You did not entering matching passwords.  You need to type the same password in twice.");
		}

			// make sure they entered their name
		if (!isset($_POST['registerFirst']) || $_POST['registerFirst'] == "")
			$errorMessages->addMessage("Registration Error", "You didn't enter your first name!");
		if (!isset($_POST['registerLast']) || $_POST['registerLast'] == "")
			$errorMessages->addMessage("Registration Error", "You didn't enter your last name!");
			
		// header information
		if (!isset($_POST['registerHeader']) || $_POST['registerHeader'] == "")
			$errorMessages->addMessage("Registration Error", "You didn't enter anything for the petition header.");
		
		// signature information
		if (!isset($_POST['registerSignature']) || $_POST['registerSignature'] == "")
			$errorMessages->addMessage("Registration Error", "You didn't enter anything for the petition signature.");
		
		if (!$errorMessages->hasMessages())
		{
			// if we get to here, then all is well; update the user
			$query = "UPDATE user SET email='". mysql_escape_string($_POST['registerEmail']) . "' WHERE userid='" . $_SESSION["loginUserID"] . "'";
			$result = mysql_query($query, $db);

			if (!$result) 
			{
				if ($GLOBALS['debug'])
					die('There was a problem updating your email in the database:' . mysql_error());
				else
					die('There was a problem updating your email in the database.');
			}
			
			//update the password only if they set a new password
			if (isset($_POST['newregisterPassword']) && $_POST['newregisterPassword'] != "")
			{
				$password = md5(mysql_escape_string($_POST['newregisterPassword']));
				$query = "UPDATE user SET password='". $password . "' WHERE userid='" . $_SESSION["loginUserID"] . "'";

				$result = mysql_query($query, $db);
				if (!$result) 
				{
					if ($GLOBALS['debug'])
						die('There was a problem updating your password in the database:' . mysql_error());
					else
						die('There was a problem updating your password in the database.');
				}	
			}
			
			// now update information into userinfo
			$query = "UPDATE userinfo SET firstName='" . mysql_escape_string($_POST['registerFirst']) . "', lastName='" . mysql_escape_string($_POST['registerLast']) . "', petitionHeader='" . mysql_escape_string($_POST['registerHeader']) . "', petitionSignature='" . mysql_escape_string($_POST['registerSignature']) . "' WHERE userid='".$_SESSION["loginUserID"]."'";
			$result = mysql_query($query, $db);
			if (!$result) 
			{
				if ($GLOBALS['debug'])
					die('There was a problem updating your user information in the database:' . mysql_error());
				else
					die('There was a problem updating your user information in the database.');
			}
			
			print "Update successful!";
			
		}
	} // end !isLoggedIn();
}
?>