<?php

/*****************************************
*
*	trackingsheet.php
*	A page that allows for generation of a spreadsheet for tracking cases post expungement clincs
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
*********************************************/

	require_once("config.php");
	require_once("utils.php");
	include("head.php");

	// the page header, including the menu bar, etc...
	include("header.php");
?>

<div class="main">
	<div class="pure-u-8-24">&nbsp;</div>
	<div class="pure-u-12-24">
<?php
// if the user isn't logged in, then don't display this page.  Tell them they need to log in.
if (!isLoggedIn())
{
	include("displayNotLoggedIn.php");
}
// only display the rest of the page if the person is part of an organization that writes to the database
else if ($attorney->getSaveCIToDatabase()==1) 
{ 
    // kill old session information that might screw things up on the search page
    resetSession();
    
    // check to see if we want to create a CSV file b/c there was a previous submit
    if (isset($_POST['submit']) and isset($_POST['names']))
    {
        $link = createTrackingSpreadsheet($_POST['names']);
        print "<a href='secureServe.php?serveFile=" . basename($link). "'>Download Tracking Sheet</a><br/><br/>";
    }
?>
		<form action="trackingsheet.php" method="post" enctype="multipart/form-data">
		<div class="pure-input-1">
			<label for="names">Client Names</label>
			<div class="pure-input-1">
				<textarea rows="20" name="names" id="names" class="pure-input-1" placeholder="Bob, Barker,
Phillis, Diller
Holden, Caulfield
First, Last"><?php printIfSet('names');?></textarea>
			</div>
		</div> 
    	<div class="form-item">
			<input type="submit" name="submit" value="Create Spreadsheet" />
		</div>
		</form>
<?php 
}  // else isloggedin()
?>
	</div>
	<div class="content-right">&nbsp;</div>
</div>

<?php 
	include("foot.php");
?>