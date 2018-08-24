<html>
<?php

require_once("library/odf.php");
require_once("Arrest.php");
require_once("Person.php");

$dataDir = "c:\wamp\www\crep\data\\";
$templateDir = "c:\wamp\www\crep\\templates\\";
$test = "test.odt";

$dataDir = "c:\wamp\www\crep\data\\";
$toolsDir = "c:\wamp\\tools\\";

$dataFile = $dataDir . "test.pdf";
$outputFile = $dataDir . "tester.txt";

$dataFile = $dataDir . "test.pdf";
$outputFile = $dataDir . "tester.txt";
$arrests = array();

$person = new Person("fakePP", "fakeSID", "fakeSSN", "fakeStreet", "fakeStreet2", "fakeCity", "fakeState", "fakeZip");

foreach(glob($dataDir . "*.aspx") as $filename)
{
	$command = $toolsDir . "pdftotext.exe" . " -layout \"" . $filename . "\" " . $outputFile;
	system($command, $ret);

	if ($ret == 0)
	{
		//print $filename . "<br />";
		$thisRecord = file($outputFile);

		$arrest = new Arrest();
		$arrest->readArrestRecord($thisRecord);
		$arrests[] = $arrest;
	}
	unlink($outputFile);
}

// start by comparing the arrests and combining the ones with matching OTNS or DC numbers
foreach ($arrests as $key=>$arrest)
{
	$innerArrests = $arrests;
	foreach ($innerArrests as $innerKey=>$innerArrest)
	{
		if($arrest->combine($innerArrest))
		{
			print "combining " . $arrest->getFirstDocketNumber() . " | " . $innerArrest->getFirstDocketNumber() . "<br />";
			unset($arrests[$innerKey]);
		}
	}
}
$arrests = array_values($arrests);


foreach ($arrests as $key=>$arrest)
{
		if ($arrest->isArrestExpungement() || $arrest->isArrestRedaction())
			$arrest->writeExpungement($templateDir, $dataDir, $person);
		
		print "expungement? " . $arrest->isArrestExpungement() . "<br />";
		print "redaction? " . $arrest->isArrestRedaction() . "<br />";
		print "held for court? ". $arrest->isArrestHeldForCourt() . "<br />";
		$arrest->simplePrint();
		print "<br />";

}


?>
</html>