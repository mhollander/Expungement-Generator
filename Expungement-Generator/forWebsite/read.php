<html>
<?php
$recordDir = "c:\wamp\www\crep\data\\";

$toolsDir = "c:\wamp\\tools\\";

$record = $recordDir . "test.pdf";
$outputFile = $recordDir . "tester.txt";

$command = $toolsDir . "pdftotext.exe" . " -layout \"" . $record . "\" " . $outputFile;
system($command, $ret);

if ($ret == 0)
{

	$thisRecord = file($outputFile);


	foreach ($thisRecord as $line_num => $line)
	{
		print "$line_num: $line <br />";
	}
}
?>
</html>