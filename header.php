<?php
	require_once("Attorney.php");
?>

<div class="header">
	<div class="header-left">
	<a href="index.php">Home</a>
	<a href="index.php">Expunge</a>
	<a href="userguide.php">Help</a>
	</div>
	<div class="header-right">
<?php
if (isLoggedIn())
{
	print "Logged in as <a href='editAttorney.php'>" . getLoggedInUserName() . "</a> | <a href='index.php?logout=true'>Logout</a>";
	
	$attorney = new Attorney($_SESSION["loginUserID"], $db);
	if ($attorney->getUserLevel() == 1)
		print " | <a href='report.php'>Reporting</a> | <a href='manage.php'>Manage Users</a>";
}
else
	print "<a href='login.php'>Login</a>"
?>
	</div>
</div>
<div class="headerLine">&nbsp;</div>
