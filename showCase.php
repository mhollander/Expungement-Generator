<?php
	// showCase.php
	// Displays a single case's information
	
	require_once("config.php");
	require_once("Arrest.php");
	require_once("ArrestSummary.php");
	require_once("Person.php");
	require_once("Attorney.php");
	require_once("utils.php");
	require_once("Case.php");
	
	include('head.php');
	include('header.php');
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
	$attorney = new Attorney($_SESSION["loginUserID"], $db);
	if($GLOBALS['debug'])
		$attorney->printAttorneyInfo();

	// only certain users can see this page
	if ($attorney->getProgramId() != 1)
		print "You must have permission to view this page.";

	else
	{	
		// first, get basic info about the expungement
		$query = "SELECT expungement.*, userinfo.*, arrest.*, defendant.firstName as dFirst, defendant.lastName as dLast from expungement LEFT JOIN userinfo on (expungement.userid = userinfo.userid) LEFT JOIN arrest on (expungement.arrestID = arrest.arrestID) LEFT JOIN defendant on (arrest.defendantID = defendant.defendantID) WHERE expungement.expungementID = {$_GET['id']}";
		
		// if this is not an admin user, then you should only display this to a user
		// who actually did the expungement.
		if ($attorney->getUserLevel() != 1)
			$query .= " AND expungement.userid = {$attorney->getUserID()}";
		
		$result = $db->query($query);
		if (!$result) 
		{
			if ($GLOBALS['debug'])
				die('Could not get the Expungement Information from the DB:' . $db->error);
			else
				die('Could not get the Expungement Information from the DB');
		}
		
		print <<<END
		<table>
		<th>Docket Number</th><th>Defendant Name</th><th>Attorney Name</th><th>Date Prepared</th><th>Charges Redacted</th><th>Type</th><th>Costs Owed</th><th>Bail Owed</th>
END;
		while ($row = $result->fetch_assoc())
		{
			$redactionType = getRedactionType($row['isExpungement'],$row['isRedaction'],$row['isSummaryExpungement']);
			
			print "<tr>";
			print "<td><a href='showCase.php?id={$row['expungementID']}'>{$row['docketNumRelated']}</a></td>";
			print "<td>{$row['dFirst']} {$row['dLast']} </td>";
			print "<td>{$row['firstName']}  {$row['lastName']} </td>";
			print "<td>{$row['timestamp']}</td>";
			print "<td>{$row['numRedactableCharges']}</td>";
			print "<td>$redactionType</td>";
			print "<td>$" . number_format($row['costsTotal'] - $row['bailTotal'],2) . "</td>";
			print "<td>$" . number_format($row['bailTotalToal'],2) . "</td>";
			print "</tr>";
		}
		print "</table>";
		$result->close();
		
		if (doesPDFExistForCaseId($_GET['id']))
			print "<br /><a href='displayPDF.php?id={$_GET['id']}'>Show stored PDF docket sheet for this case</a><br />";
	
		// now, query all of the charges associated with this expungement
		$query = "SELECT expungement.*, arrest.*, defendant.*, charge.* from expungement LEFT JOIN arrest on (expungement.arrestID = arrest.arrestID) LEFT JOIN defendant on (arrest.defendantID = defendant.defendantID) LEFT JOIN charge on (arrest.arrestID = charge.arrestID) WHERE expungement.expungementID = {$_GET['id']}";
		
		if ($attorney->getUserLevel() != 1)
			$query .= " AND expungement.userid = {$attorney->getUserID()}";
		
		$result = $db->query($query);
		if (!$result) 
		{
			if ($GLOBALS['debug'])
				die('Could not get the Expungement Information from the DB:' . $db->error);
			else
				die('Could not get the Expungement Information from the DB');
		}
		
		print <<<END
		<table>
		<th>Charge Name</th><th>Charge Disposition</th><th>Code Section</th><th>Disp Date</th><th>Is Charge Expungeable?</th>
END;
		while ($row = $result->fetch_assoc())
		{
			print "<tr>";
			print "<td>{$row['chargeName']}</td>";
			print "<td>{$row['disposition']}</td>";
			print "<td>{$row['codeSection']}</td>";
			print "<td>{$row['dispDate']}</td>";
			print "<td>{$row['isExpungeableNow']}</td>";
			print "</tr>";
		}
		$result->close();
	}
}
?>	
	</div> <!-- content-center -->
	<div class="content-right"><?php // include right column? ?></div>
	</div>
<?php
	include ('foot.php');

	
?>