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
		// show all of the interesting queries
		$queries = gatherQueries();
		foreach ($queries as $query)
		{
			displayQuery($query);
		}
	} 	
}
?>	
	</div> <!-- content-center -->
	<div class="content-right"><?php // include right column? ?></div>
	</div>
<?php
	include ('foot.php');


function gatherQueries()
{
	$sql = array();
	$sql[] = "SELECT d.firstName as ClientFirst, d.lastName as ClientLast, e.timestamp, a.costsTotal, u.firstName as AttnyFirst, u.lastName as AttnyLast FROM `expungement` as e LEFT JOIN arrest as a ON e.arrestID = a.arrestID LEFT JOIN defendant as d ON e.defendantID = d.defendantID LEFT JOIN userinfo as u ON e.userid = u.userid WHERE e.isExpungement = 1 and a.costsTotal > 0 ORDER BY e.timestamp DESC";
	return $sql;
}

function displayQuery($query)
{
	$result = mysql_query($query, $GLOBALS['db']);
	print "<p>" . $query . "</p>";
	
	if (!$result) 
	{
		die('Could not run your query:' . mysql_error());
	}
	
	print "<table border='1'><tr>";
	$numFields = mysql_num_fields($result);
	for ($i = 0; $i < $numFields; $i += 1) {
        $field = mysql_fetch_field($result, $i);
        echo '<th>' . $field->name . '</th>';
    }
	print "</tr>";
	while ($row = mysql_fetch_array($result))
	{
		print "<tr>";
		for ($i=0; $i < $numFields; $i++)
			print "<td>$row[$i]</td>";
		print "</tr>";
	}
	print "</table>";
}
