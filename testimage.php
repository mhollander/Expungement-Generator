<?php
	// @todo replace mysql_escape_string with mysql_real_escape_string
	// @todo check login

	require_once("config.php");
	require_once("library/odf.php");
	require_once("utils.php");
	
	include('head.php');
	include('header.php');
?>

<div class="main">
	<div class="content-left">&nbsp;</div>
	<div class="content-center">

<?php
$odf = new odf($templateDir . "imagetest.odt");
$odf->setImageResize('SIG_IMAGE', "./data/mikeleesig.jpg", 0, 80);
$outputFile = $dataDir . "testimage.odt";
$odf->saveToDisk($outputFile);	
//$odf->exportAsAttachedFile();
?>
		</div> <!-- content-center -->
	<div class="content-right"><?php include("expungementDisclaimers.php");?></div>
	</div>

<?php
	include ('foot.php');
?>

		
		
