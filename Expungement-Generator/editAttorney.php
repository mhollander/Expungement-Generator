<?php
/* @todo add password reset 
/* @todo add register link
/****************************************************************
*
*	editAttorney.php
*	Allows attorneys to edit their own information and update their profiles.
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
if (!isLoggedIn())
{
?>
	<div class="titleMessage">You must login to edit your information.</div>
	<div><a href="login.php">Click here to login.</a></div>
<?php
}

// else, we want to display all of the relevant information about the user so it can be editted.
else
{
// get the attorney info from the database
// if there is an id in the URL and this user is a superuser, then let them view the user with that ID
// if there isn't an ID in the URL or this isn't a superuser, then they can only view themselves
	$attorney = new Attorney($_SESSION["loginUserID"], $db);
	if ((isset($_REQUEST['id']) && $_REQUEST['id'] !=""))
	{
		if ($attorney->getUserLevel() == 1)
			$attorney = new Attorney($_REQUEST['id'], $db);
	}


// display registration form
?>
		<div class="titleMessage">Edit <?php print(getLoggedInUserName());?>'s Profile</div>
		<div>Use the form below to edit your profile.</div>
		
<?php
	// only certain users can see this page
	// we're being overly restrictive right now - only letting CLS lawyers see it
	if ($attorney->getProgramId() == 1)
		print "<div><a href='attorneyExpInfo.php'>Click to view your expungement history.</a></div>";
?>
	
		<div>&nbsp;</div>
		<form action="editAttorney.php" method="post">
		<div>
		<div class="form-item">
			<label for="registerPassword">Password* (you must enter your password to change anything below)</label>
			<input type="password" name="registerPassword" id="registerPassword" class="form-text" />
		</div>
		<div class="form-item">
			<label for="registerEmail">Email Address</label>
			<input type="text" name="registerEmail" id="registerEmail" class="form-text" value="<?php print $attorney->getEmail();?>"/> 
			<div class="description">Type a new email address if you want to change your email address.</div>
		</div> 
		<div class="form-item">
			<label for="registerPassword">New Password (only if you want to change your pw)</label>
			<input type="password" name="newregisterPassword" id="registerPassword" class="form-text" />
		</div>
		<div class="form-item">
			<label for="retypePassword">Retype New Password (only if you want to change your pw)</label>
			<input type="password" name="newregisterRetypePassword" id="registerRetypePassword" class="form-text" />
		</div>
		<div class="form-item">
			<label for="registerFirst">Your Name</label>
			<div class="form-item-column">
				<input type="text" name="registerFirst" id="registerFirst" class="form-text" value="<?php print $attorney->getFirstName();?>" /> 
			</div>
			<div class="form-item-column">
				<input type="text" name="registerLast" id="registerLast" class="form-text" value="<?php print $attorney->getLastName();?>" />
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
			<label for="registerHeader">Header text for expungement petitions</label>
			<textarea name="registerHeader" id="registerHeader" class="form-text form-text-area-big"><?php print $attorney->getPetitionHeader();?></textarea>
			<div class="description">Change the text if you want to change the header on your petitions.</div>
			<div class="description">This will appear at the top of your petition, above the caption.</div>
		</div>
		<div class="form-item">
			<label for="registerSignature">Expungement petition signature</label>
			<textarea name="registerSignature" id="registerSignature" class="form-text form-text-area-small"><?php print $attorney->getPetitionSignature();?></textarea>
			<div class="description">Change the text of your signature if you want to change it on your petitions.</div>

		</div>

		<div class="form-item">
		<?php if (isset($_REQUEST['id']) && $_REQUEST['id'] != "") { print "<input type='hidden' name='id' value='".$_REQUEST['id']."'/>";} ?>
			<input type="hidden" name="edit" value="1" />
			<input type="submit" value="Edit Profile" />
		</div>
		</form>


<?php
} // end else !isLoggedIn();
?>
	</div>
	<div class="content-right">&nbsp;</div>
</div>

<?php 
	include("foot.php");
?>