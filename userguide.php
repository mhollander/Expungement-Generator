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
		<div class="guideStepDesc">
			<hr class="guideStepDesc"/>
			<ul class="guideStepDesc">
				<li><a href="http://www.openoffice.org/">Open Office</a> or Word 2007 or later</li>
				<li>A login, which you can get by <a href="mailto:mhollander@clsphila.org?subject=login request for the expungement generator">emailing mhollander@clsphila.org.</a></li>
			</ul>
			<hr class="guideStepDesc"/>
		</div>
		<div class="guideStep">Step 1: Create a folder on your computer for your client</div>
		<div class="guideStepDesc">
			<hr class="guideStepDesc"/>
			Navigate to a place that makes sense to store client files and make a folder for your client.  You will be placing all of your docket sheets and the expungement petitions in this folder through these instructions.
			<hr class="guideStepDesc"/>
		</div>
		<div class="guideStep">Step 2: Go to the court website</div>
		<div class="guideStepDesc">
			<hr class="guideStepDesc"/>
			The court website can be found here: <a href="http://ujsportal.pacourts.us/DocketSheets/CP.aspx" target="_blank">http://ujsportal.pacourts.us/DocketSheets/CP.aspx</a>.
			<hr class="guideStepDesc"/>
		</div>
		<div class="guideStep">Step 3: Search for your client on the court website</div>
		<div class="guideStepDesc">
			<hr class="guideStepDesc"/>
			You can search for your client in any number of ways:
			<ul class="guideStepDesc">
				<li><strong>Docket Number:</strong> Select "CP/MC Docket Number."  If you know your client's docket number, you can find an individual case by inputing it.</li>
				<li><strong>OTN:</strong> If you know a case's OTN number, you can search directly with that number.  An OTN number is commonly found on a Pennsylvania State Police Report.</li>
				<li><strong>Name:</strong> Select "Participant Name" in the dropdown.  You should search not only for your client's full name, but you should search for spelling variations or just partials of your client's name.  This is because whoever entered your client's name into the online database may have spelled your client's name incorrectly.  <p>As an example, you might want to enter "Julius Ceasar" as "Jul Ceas" in order to capture mispellings of his name.</p><p>Entering a date of  birth is optional.  You may want to leave it off because dates of birth are sometimes mis-entered as well.</p><p>You should leave the other fields blank except for Date Filed.  To capture all cases, enter the range 01/01/1900 to today's date:<br><img border="1" src="images/cpcms_byname.png"></p></li>
				<li><strong>SID:</strong> If you have your client's SID, you can find all cases associated with that SID by searching for it directly.  This may pull cases where the client has used a name different from his real name when arrested.  The SID for an individual can usually be obtained by contacting your local clerk of courts.</li>
			</ul>
			<hr class="guideStepDesc"/>
		</div>
		<div class="guideStep">Step 4: Download your client's docket sheets from the court website</div>
		<div class="guideStepDesc">			
			<hr class="guideStepDesc"/>
			<ul class="guideStepDesc">
				<li>Place your mouse cursor over the document/magnifying glass icon at the left hand side of the each row:<br/><img src="images/cpcms_mouseover.png"></li>
				<li>Click "Docket Sheet."  Your computer will open a new page and download the docket sheet for the case that you selected.</li>
				<li>Repeat this process for each case associated with your client</li>
				<li>Save each of the PDFs that is downloaded into the folder that you created in step one.</li>
				<li><Strong>Tip:</strong>If you want to speed up the process, you can hold the "control" key while you click on each docket sheet.  This will open each docket in a new "tab" on your web browser and will allow you to download all of the docket sheets more quickly.</li>
			</ul>
			<hr class="guideStepDesc"/>
		</div>
		<div class="guideStep">Step 5: Download the summary docket sheet from the court website</div>
		<div class="guideStepDesc">
			<hr class="guideStepDesc"/>
			<ul class="guideStepDesc">
				<li>On one of your client's cases (it generally doesn't matter which one as long as the case is newer than around 1990), place your mouse cursor over the document/magnifying glass icon at the left hand side of the each row:<br/><img src="images/cpcms_mouseover.png"></li>
				<li>Click "Court Summary."  Your computer will open a new page and download a summary of all of the arrests that your client has.  You only need to download this file one time.</li>
				<li>Save this court summary into the folder that you created in step one.  </li>
			</ul>
			<hr class="guideStepDesc"/>
		</div>
		<div class="guideStep">Step 6: Go to the expungement generator website</div>
		<div class="guideStepDesc">
			<hr class="guideStepDesc"/>
			The expungement generator website can be found at <a href="http://www.ronhollander.com/crepdb" target="_blank">http://www.ronhollander.com/crepdb"</a>.
			<ul class="guideStepDesc">
				<li>If you aren't already logged in, login.</li>
				<li>Click on the "Expunge" link at the top of the page if you aren't already there:<br/><img border="1" src="images/eg_expungeclick.png"></li>
			</ul>
			<hr class="guideStepDesc"/>
		</div>
		<div class="guideStep">Step 7: Enter your client's information into the expungement generator</div>
		<div class="guideStepDesc">
			<hr class="guideStepDesc"/>
			<ul class="guideStepDesc">
				<li>Put in as much information as you have.  Expungements will be rejected if you are missing any informaiton that is readily obtainable.</li>
				<li>For the name field, enter the person's real name.  Do not enter any aliases.</li>
				<li>If there is no SID or PID available for the person, you can leave SID and/or PID blank and the generator will put "N/A" in for them.</li>
				<li>If you don't know the SID or PID, try calling your local clerk of courts to see if they will give you that information.</li>
				<li>If you have access to the "Secure Summary" you can include that with the files, below, and skip typing in the SID and PID.  The generator will pick up the SID and PID from the Secure Summary and include them on all petitions.</li>
			</ul>
			<hr class="guideStepDesc"/>
		</div>
		<div class="guideStep">Step 8: Enter all of your client's aliases, separated by commas</div>
		<div class="guideStepDesc">
			<hr class="guideStepDesc"/>
			This is pretty self explanatory.  It is a good idea to include the client's real name (as typed in the first two fields) in the list of aliases as well.</div>
		<div class="guideStep">Step 9: Upload all of your client's docket sheets and the summary docket to the expungement generator</div>
		<div class="guideStepDesc">
			<hr class="guideStepDesc"/>
			<ul class="guideStepDesc">
				<li>Click on the "Choose Files" at the bottom of the page.<br/><img border="1" src="images/eg_fileUpload.png"></li>
				<li>You should be presented with a dialog box that looks something like this: <br/><img border="0" src="images/eg_fileUploadDialog.png"></li>
				<li>Use the navigation tools to find the folder where you downloaded your docket sheets and court summary.</li>
				<li>When you have found the correct folder, click on the first docket sheet, hold shift and click on the last docket sheet.  This should highlight all of the docket sheets from the first to the last one.  Be careful to not include non-docket sheets.  You can alternatively hold the "ctrl" key on your keyboard and click on each docket sheet you want to upload.</li>
				<li>Press "open" and you should be returned to the form used to input information about your client<br/><img border="0" src="images/eg_selectMultiple.gif"></li>
			</ul>
			<hr class="guideStepDesc"/>
		</div>
		<div class="guideStep">Step 10: Click "Send Files" and wait for the magic to happen</div>
		<div class="guideStepDesc">
			<hr class="guideStepDesc"/>
			This is self explanatory.  Click only once.  In a few seconds (to as long as 20-30 seconds if you have a slow internet connection), you should be taken to a confirmation page.
			<hr class="guideStepDesc"/>
		</div>
		<div class="guideStep">Step 11: You should see the expungement confirmation page</div>
		<div class="guideStepDesc">
			<hr class="guideStepDesc"/>
			The expungement confirmation page contains some basic information that is helpful to understanding your case, including:
			<ul class="guideStepDesc">
				<li>Whether the generator combined docket sheets that were related to the same case (MC and CP cases with the same OTN or DC number)</li>
				<li>What cases the generator created expungements and redactions for, and for what cases no expungement or redaction was possible</li>
				<li>What types of cases the generator still has problems with.  Look at the right sidebar for this information</li>
			</ul>
			<br/>The confirmation page should look something like this: <br /><img border="1" src="images/eg_Confirmation.png">
			<hr class="guideStepDesc"/>
		</div>
		<div class="guideStep">Step 12: On the expungement confirmation page, download the expungement zip file</div>
		<div class="guideStepDesc">
			<hr class="guideStepDesc"/>
			All of the expungements performed for your client have been placed into a zip file.  This zip file also contains an overview of all of your client's cases, whether each case can be expunged or redacted, and whether an fines, costs, or bail are owed to the court.
			<p>To download the petition, click the link just after the text "Download Petition and Overview":<br/><img src="images/eg_confirmationDownload.png" border="1"></p>
			<hr class="guideStepDesc"/>
		</div>
		<div class="guideStep">Step 13: Copy the expungements and overview file from the zip file to your computer</div>
		<div class="guideStepDesc">
			<hr class="guideStepDesc"/>
			<ul class="guideStepDesc">
				<li>Clicking the link above will download the file.  Click on the file once it has been downloaded to see the contents of the zip file.</li>
				<li>When you open the zip archive, you should see all of the expungement petitions prepared for your client as well as an overview file:<br/><img src="images/eg_zip.png"></li>
				<li>Select all of the files in this folder by clicking the first file, pressing and holding the shift key, and clicking the last file.</li>
				<li>In the "edit" menu at the top of the dialog box, select "copy."</li>
				<li>Open the folder that you previously downloaded all of your expungement petitions into.  In the "edit" menu of this folder, select "paste."  This will copy all of the files from the zip file to your computer.</li>
			</ul>
			<hr class="guideStepDesc"/>
		</div>
		<div class="guideStep">Step 14: Open the individual expungement files and check their accuracy.</div>
		<div class="guideStepDesc">
			<hr class="guideStepDesc"/>
			Remember, the expungement generator is only a computer.  While it is very accurate, it does make mistakes, especially with more complicated docket sheets.  <strong>You should always check the expungement petitions that the generator prepares against the downloaded docket sheets to make sure that they are accurate before printing them out.</strong>
			<hr class="guideStepDesc"/>
		</div>
		
<?php
} // end else !isLoggedIn();
?>
</div>
	<div class="content-right">&nbsp;</div>
</div>

<?php 
	include("foot.php");
?>