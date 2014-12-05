<?php


/****************************************************************
*
*	Message.php
*	contains the general messaging system to display error type messages to the user
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

class Message
{

	private $messages = array();
	private $errors = array();
	
	
	// constructor
	public function __construct()
	{
		// nothing really to do here
	}
	
	// add a $message to the $messages array with $class as the key
	public function addMessage($class, $message)
	{
		// push this message onto the messages array
		$this->messages[] = array($class, $message);
	}

	// add an $error to the $errors array with $class as the key
	public function addError($class, $error)
	{
		// push this message onto the errors array
		$this->errors[] = array($class, $error);
	}

	// @return none; prints out all of the messages on the $messages has as separate divs	
	public function displayMessages()
	{
		if (count($this->messages) > 0)
		{
			foreach ($this->messages as $message)
				print "<div class='message'>$message[0]: $message[1]</div>";
		}
	}
	
	// @return none; prints out all of the errors on the $errors has as separate divs
	public function displayErrors()
	{
		if (count($this->errors) > 0)
		{
			foreach ($this->errors as $error)
				print "<div class='error'>$error[0]: $error[1]</div>";
		}
	}
	
	// @return TRUE if there is at least one message on the messages hash
	public function hasMessages()
	{
		if (count($this->messages) > 0)
			return TRUE;
		else
			return FALSE;
	}

}


?>