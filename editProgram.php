<?php
/****************************************************************
*
*	editProgram.php
*	Allows a superadministrator to edit a program's name, IFP language, IFP status, and reset the apiKey
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
if (!isLoggedIn()  || $attorney->getUserLevel() != 1)
{
?>
	<div class="titleMessage">You must be logged in as a superuser to edit program information.</div>
<?php
}

// else, we want to display all of the relevant information about the user so it can be editted.
else
{
    // first run a query to get the current program's information
    $sql = "SELECT * FROM program WHERE programid = " . $GLOBALS['db']->real_escape_string($_REQUEST['id']) . ";";
    if (!$result = $GLOBALS['db']->query($sql))                                                                
        die('There was a problem getting information about the program:' . $GLOBALS['db']->error);
    $program = $result->fetch_assoc();
    
// display registration form
?>
		<div class="titleMessage">Edit <?php print $program['programName'];?> Profile</div>
		<div>Use the form below to edit this program's profile.</div> <!-- ' -->

		<div>&nbsp;</div>
		<form action="editProgram.php" method="post">
		<div>
		<div class="form-item">
			<label for="programName">Program Name</label>
			<input type="text" name="programName" id="programName" class="form-text" value="<?php print $program['programName'];?>"/>
			<div class="description">Type a new program name if you want to change the program's name.</div> <!--'-->
		</div> 
		<div class="form-item">
			<label for="programIFP">IFP Status</label>
				<select name="programIFP" id="programIFP" class="form-text">
                    <option value="0" <?php if($program['ifp']=="0") print "selected";?>>0</option>
                    <option value="1" <?php if($program['ifp']=="1") print "selected";?>>1</option>
                    <option value="2" <?php if($program['ifp']=="2") print "selected";?>>2</option>
                </select> 
		</div>
		<div class="form-item">
			<label for="programIFPLanguage">IFP Language for petitions from this program</label>
			<textarea name="programIFPLanguage" id="programIFPLanguage" class="form-text form-text-area-big"><?php print $program['ifpLanguage'];;?></textarea>
			<div class="description">Change the text if you want to change the IFP language put on all petitions created under this program.</div>
		</div>
        <div class="form-item">
		<?php if (isset($_REQUEST['id']) && $_REQUEST['id'] != "") { print "<input type='hidden' name='id' value='".$_REQUEST['id']."'/>";} ?>
			<input type="hidden" name="editProgram" value="1" />
			<input type="submit" value="Edit Program" />
		</div>
		</form>
        <!-- this is a form just for resetting the API key -->
		<form action="editProgram.php" method="post">
        <div class="form-item">
		<?php if (isset($_REQUEST['id']) && $_REQUEST['id'] != "") { print "<input type='hidden' name='id' value='".$_REQUEST['id']."'/>";} ?>
			<input type="hidden" name="editProgramKey" value="1" />
			<div class="description"><b>Resetting a program API key is some serious shit.  Only do this if you really want to.  Because after you change the key, you have to change the key value on all systems that use the key for this program, which is annoying.  Also, resetting the API key will only reset the API key.  It won't change any of the fields you may have editted above</b></div>
			<input type="submit" value="Reset API Key.  Only do this if you mean it." />
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