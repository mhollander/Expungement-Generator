<html>
<?php

require_once("Arrest.php");

$dataDir = "c:\wamp\www\crep\data\\";
$toolsDir = "c:\wamp\\tools\\";

$dataFile = $dataDir . "test.pdf";
$outputFile = $dataDir . "tester.txt";

foreach(glob($dataDir . "*.aspx") as $filename)
{ 
	$command = $toolsDir . "pdftotext.exe" . " -layout \"" . $filename . "\" " . $outputFile;
	system($command, $ret);

	if ($ret == 0)
	{
		print $filename . "<br />";
		$thisRecord = file($outputFile);

		$arrest = new Arrest();
		$arrest->readArrestRecord($thisRecord);

		$arrest->simplePrint();
		print "<br />";
	}

	unlink($outputFile);
}

?>
</html>