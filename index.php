<?php
	require_once("config.php");
	require_once("utils.php");
	include("head.php");

	// the page header, including the menu bar, etc...
	include("header.php");
?>

<div class="main">
	<div class="content-left">&nbsp;</div>
	<div class="content-center">
<?php
// if the user isn't logged in, then don't display this page.  Tell them they need to log in.
if (!isLoggedIn())
	include("displayNotLoggedIn.php");
else
{
?>
		<form action="expunge.php" method="post" enctype="multipart/form-data">
		<div class="form-item">
			<label for="personFirst">Client's Name</label>
			<div class="form-item-column">
				<input type="text" name="personFirst" id="personFirst" class="form-text" value="<?php printIfSet('personFirst');?>" /> 
			</div>
			<div class="form-item-column">
				<input type="text" name="personLast" id="personLast" class="form-text" value="<?php printIfSet('personLast');?>" />
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
		</div> 
		<div class="form-item">
			<label for="personStreet">Address</label>
			<input type="text" name="personStreet" id="personStreet" class="form-text" value="<?php printIfSet('personStreet');?>" />
			<div class="description">Street Name, Number</div>
			<div class="space-line"></div>
			<div class="form-sub-item">
				<div class="form-item-column">
					<input type="text" name="personCity" class="form-text" value="<?php printIfSet('personCity');?>" />
				</div>
				<div class="form-item-column form-item-column-state">
					<input type="text" name="personState" class="form-text form-text-state" value="<?php printIfSet('personState');?>"/>
				</div>
				<div class="form-item-column">
					<input type="text" name="personZip" class="form-text form-text-zip" value="<?php printIfSet('personZip');?>"/>
				</div>
				<div class="space-line"></div>
				<div class="description">
					<div class="form-item-column">
						City
					</div>
					<div class="form-item-column form-item-column-state">
						State
					</div>
					<div class="form-item-column">
						Zip
					</div>
				</div>
				<div class="space-line"></div>
			</div> 
		</div>
		<div class="form-item">
		<label for="personSSN">Social Security Number</label>
			<input type="text" name="personSSN" id="personSSN" value="<?php printIfSet('personSSN');?>" />
			<div class="description">###-##-####</div>
		</div> 
		<div class="form-item">
		<label for="edit-name">PP Number/PPID</label>
			<input type="text" name="personPP" value="<?php printIfSet('personPP');?>" />
		</div> 
		<div class="form-item">
		<label for="edit-name">SID</label>
			<input type="text" name="personSID" value="<?php printIfSet('personSID');?>" />
		</div> 
		<div class="form-item">
		<label for="edit-name">Aliases</label>
			<input type="text" name="personAlias" value="<?php printIfSet('personAlias');?>" /> 
			<div class="description">(comma separated list--eg: Bill Smith, Billy Smith, William Smith)</div>
		</div> 
		<div class="form-item">
			<label for="userFile1">Send these files (contrl click to select multiple files)</label>
			<input type="hidden" name="MAX_FILE_SIZE" value="300000" />
			<input name="userFile[]" type="file" multiple="true" name="userFile1"/>
		</div>
		<div class="form-item">
		<label for="newStylePetition">Use 790/490 Statewide Petition?</label>
			<input type="checkbox" name="newStylePetition" value="TRUE" checked="checked"/> 
		</div> 
		<div class="form-item">
			<input type="submit" value="Send files" />
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