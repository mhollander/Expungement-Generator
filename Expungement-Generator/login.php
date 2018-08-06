<?php
/* @todo add password reset 
/* @todo add register link
/****************************************************************
*
*	login.php
*	login form for AutoExpunge
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
	include("head.php");

	// the page header, including the menu bar, etc...
	include("header.php");
?>

<div class="main">
	<div class="content-left">&nbsp;
		<?php include("messagedisplay.php"); ?>
	</div>
	<div class="content-center">

<?php
// if the user is logged in, tell them they they are already logged in
if (isLoggedIn())
{
?>
	<div class="titleMessage">You are logged in as <?php print(getLoggedInUserName());?></div>
	<div><a href="index.php">Click here to start preparing expungement petitions.</a></div>
<?php
}

// else, we are not logged in and we need to display the login or registration screen
else
{

// check to see if we are going to the login page or the register page;  default to login page
if(!isset($_GET['register']))
{
?>
		<form action="login.php" method="post">
		<div class="form-item">
			<label for="username">Email Address</label>
			<input type="text" name="username" id="username" class="form-text" /> 
		</div> 
		<div class="form-item">
			<label for="password">Password</label>
			<input type="password" name="password" id="password" class="form-text" />
		</div>
<!--		<div class="form-item">
			<label for="rememberMe">Stay signed in?</label>
			<input type="checkbox" name="rememberMe" id="rememberMe" CHECKED="checked" />
		</div>
-->
		<div class="form-item">
			<input type="submit" value="Login" />
		</div>
		</form>
<?php
} // end login form
else
{
// display registration form
?>

		<form action="login.php" method="post">
		<div class="form-item">
			<label for="registerEmail">Email Address</label>
			<input type="text" name="registerEmail" id="registerEmail" class="form-text" /> 
			<div class="description">Your email address will be used as your username</div>
		</div> 
		<div class="form-item">
			<label for="registerPassword">Password</label>
			<input type="password" name="registerPassword" id="registerPassword" class="form-text" />
		</div>
		<div class="form-item">
			<label for="retypePassword">Retype Password</label>
			<input type="password" name="registerRetypePassword" id="registerRetypePassword" class="form-text" />
		</div>
		<div class="form-item">
			<label for="registerFirst">Your Name</label>
			<div class="form-item-column">
				<input type="text" name="registerFirst" id="registerFirst" class="form-text" value="<?php printIfSet('personFirst');?>" /> 
			</div>
			<div class="form-item-column">
				<input type="text" name="registerLast" id="registerLast" class="form-text" value="<?php printIfSet('personLast');?>" />
			</div>
			<div class="space-line"></div>
			<div class="description">
				<div class="form-item-column">
					First Name
				</div>
				<div class="form-item-column">
					Last Name
				</div>
			</div>
			<div class="space-line"></div>
		</div> 

		<div class="form-item">
			<label for="registerBarID">PA Bar ID</label>
			<input type="text" name="registerBarID" id="registerBarID" class="form-text" />
		</div>
		<div class="form-item">
			<label for="registerHeader">Header text for expungement petitions</label>
			<textarea name="registerHeader" id="registerHeader" class="form-text form-text-area-big">MY LAW FIRM NAME
BY:	Jane Lawyer, Esquire
Identification No. 123456

123 Fake Street
Springfield, PA 19102
Phone: 215.555.1212</textarea>
			<div class="description">Replace the sample text with your information.</div>
			<div class="description">This will appear at the top of your petition, above the caption.</div>
		</div>
		<div class="form-item">
			<label for="registerSignature">Expungement petition signature</label>
			<textarea name="registerSignature" id="registerSignature" class="form-text form-text-area-small">Jane Lawyer, Esquire</textarea>
			<div class="description">Replace the sample text with your information.</div>

		</div>

		<div class="form-item">
			<input type="hidden" name="register" value="1" />
			<input type="submit" value="Register" />
		</div>
		</form>


<?php
} // end register form
} // end else !isLoggedIn();
?>
	</div>
	<div class="content-right">&nbsp;</div>
</div>

<?php 
	include("foot.php");
?>