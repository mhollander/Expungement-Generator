<?php
	// manage.php
	// Contains the main page to manage new users
	
	require_once("config.php");
	require_once("Attorney.php");
	require_once("utils.php");
	
	include('head.php');
	include('header.php');
?>

<div class="main">
	<div class="content-left">&nbsp;
		<?php include("messagedisplay.php"); ?>
	</div>
	<div class="content-center">

<?php
// if the user isn't logged in, then don't display this page.  Tell them they need to log in.
if (!isLoggedIn())
	include("displayNotLoggedIn.php");
else
{
	$attorney = new Attorney($_SESSION["loginUserID"], $db);
	if($GLOBALS['debug'])
		$attorney->printAttorneyInfo();

	// only certain users can see this page
	if ($attorney->getUserLevel() != 1)
		print "You must have permission to view this page.";

	else
	{	
		// create a new user?
		displayCreateUser();
	
		displayAllUsers();		
	} 	
}
?>	
	</div> <!-- content-center -->
	<div class="content-right"><?php // include right column? ?></div>
	</div>
<?php
	include ('foot.php');

function displayCreateUser()
{
?>
	<div class="guideStep guideStepCounter">Create New User</div>
	<div class="guideStepDesc">
		<form action="manage.php" method="post">
		<div class="form-item">
			<label for="createFirst">New User's Name</label>
			<div class="form-item-column">
				<input type="text" name="createFirst" id="createFirst" class="form-text" value="" /> 
			</div>
			<div class="form-item-column">
				<input type="text" name="createLast" id="createLast" class="form-text" value="" />
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
			<label for="createEmail">Email Address</label>
			<input type="text" name="createEmail" id="createEmail" class="form-text" value=""/> 
			<div class="description">The email address of the user.</div>
		</div> 
		<div class="form-item">
			<label for="createBarID">PA Bar ID</label>
			<input type="text" name="createBarID" id="createBarID" class="form-text" value=""/> 
			<div class="description">The email address of the user.</div>
		</div> 

		<div class="form-item">
			<label for="password">Password</label>
			<input type="password" name="createPassword" id="createPassword" class="form-text" />
		</div>
		<div class="form-item">
			<label for="retypePassword">Retype Password</label>
			<input type="password" name="createRetypePassword" id="createRetypePassword" class="form-text" />
		</div>
		
		<div class="form-item">
			<label for="createHeader">Header text for expungement petitions</label>
			<textarea name="createHeader" id="createHeader" class="form-text form-text-area-big">ORGANIZATION
BY: <first> <last>
Identification No.: <barid> 

<address> 
<phone></textarea>
			<div class="description">This will appear at the top of your petition, above the caption.</div>
		</div>
		<div class="form-item">
			<label for="createSignature">Expungement petition signature</label>
			<textarea name="createSignature" id="createSignature" class="form-text form-text-area-small"><Name>, Esquire</textarea>
			<div class="description">The signature line on your petition</div>
		</div>
		
		<div class="form-item">
			<label for="createProgram">Program</label>
			<select name="createProgram" id="createProgram" class="">
<?php
	// get all of the programs from the database and list them
	$sql = "SELECT * from program";
	$result = mysql_query($sql, $GLOBALS['db']);
	if (!$result) 
	{
		if ($GLOBALS['debug'])
			die('Could not get the Expungement Information from the DB:' . mysql_error());
		else
			die('Could not get the Expungement Information from the DB');
	}
	while ($row = mysql_fetch_assoc($result))
		print "<option value='{$row['programID']}'>{$row['programName']}</option>";
?>
			</select>
		</div>
		
		<div class="form-item">
			<input type="hidden" name="create" value="1" />
			<input type="submit" value="Create User" />
		</div>
		</form>
	</div>
<?php
}

// nothing more than the code to display all users in a nice table	
function displayAllUsers() 
{
	// first, do a query of all expungements
	$query = "SELECT user.userid as userid, user.email as email, userinfo.firstName as firstName, userinfo.lastName as lastName, userinfo.userlevel as userLevel, program.programName as programName, program.ifp as ifp, userinfo.pabarid as pabarid FROM user, userinfo, program WHERE user.userid=userinfo.userid AND program.programid=userinfo.programid";
	
	$result = mysql_query($query, $GLOBALS['db']);
	if (!$result) 
	{
		if ($GLOBALS['debug'])
			die('Could not get the user information from the DB:' . mysql_error());
		else
			die('Could not get the user information from the DB');
	}
	
	print <<<END
	<table>
	<th>Name</th><th>email</th><th>Userlevel</th><th>Program</th><th>Is IFP?</th><th>Bar ID</th>
END;
	while ($row = mysql_fetch_assoc($result))
	{
		print "<tr>";
		print "<td><a href='editAttorney.php?id={$row['userid']}'>{$row['firstName']} {$row['lastName']}</a></td>";
		print "<td>{$row['email']}</td>";
		print "<td>{$row['userLevel']}</td>";
		print "<td>{$row['programName']}</td>";
		if ($row{'ifp'})
			print "<td>Yes</td>";
		else
			print "<td>No</td>";
		print "<td>{$row['pabarid']}</td>";
		print "</tr>";
	}


}
?>