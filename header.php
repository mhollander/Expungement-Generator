<div class="header">
	<div class="header-left">
	<a href="index.php">Home</a>
	<a href="index.php">Expunge</a>
	</div>
	<div class="header-right">
<?php
if (isLoggedIn())
	print "Logged in as <a href='editAttorney.php'>" . getLoggedInUserName() . "</a> | <a href='index.php?logout=true'>Logout</a>";
else
	print "<a href='login.php'>Login</a> | <a href='login.php?register'>Register</a>"
?>
	</div>
</div>
<div class="headerLine">&nbsp;</div>
