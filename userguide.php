<?php

/****************************************************************
*
*	userGuide.php
*	userGuide for the EG
*
*	Copyright 2012 Michael Hollander
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
// if the user is logged in, tell them they they aren't logged in
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
?>
		<div class="titleMessage">Expungement Generator User Guide</div>
		<div class="guideStep">Prerequisites: What you need before you can start to use the Expungement Generator</div>
		<div class="guideStepDesc">Openoffice or Word 2007 or later; a login</div>
		<div class="guideStep">Step 1: Go to the court website</div>
		<div class="guideStepDesc">And this is a description</div>
		<div class="guideStep">Step 2: Search for your client on the court website</div>
		<div class="guideStepDesc">And this is a description</div>
		<div class="guideStep">Step 3: Download your client's docket sheetse from the court website</div>
		<div class="guideStepDesc">And this is a description</div>
		<div class="guideStep">Step 4: Download the summary docket sheet from the court website</div>
		<div class="guideStepDesc">And this is a description</div>
		<div class="guideStep">Step 5: Go to the expungement generator website</div>
		<div class="guideStepDesc">And this is a description</div>
		<div class="guideStep">Step 6: Enter your client's information into the expungement generator</div>
		<div class="guideStepDesc">And this is a description</div>
		<div class="guideStep">Step 7: Enter all of your client's aliases, separated by commas</div>
		<div class="guideStepDesc">And this is a description</div>
		<div class="guideStep">Step 8: Upload all of your client's docket sheets and the summary docket to the expungement generator</div>
		<div class="guideStepDesc">And this is a description</div>
		<div class="guideStep">Step 9: Click "submit"</div>
		<div class="guideStepDesc">And this is a description</div>
		<div class="guideStep">Step 10: Download the expungement zip file</div>
		<div class="guideStepDesc">And this is a description</div>
		<div class="guideStep">Step 11: Copy the expungements and overview file from the zip file to your computer</div>
		<div class="guideStepDesc">And this is a description</div>


		
<?php
} // end else !isLoggedIn();
?>
	</div>
	<div class="content-right">&nbsp;</div>
</div>

<?php 
	include("foot.php");
?>