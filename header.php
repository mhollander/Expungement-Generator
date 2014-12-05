<?php

/*********************************************
*
*	header.php
*	The header of every page (top level links, login information, etc....)
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
********************************************/


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
