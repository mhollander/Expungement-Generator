<html>
<?php
/************************
*
*
* Copyright 2011-2015 Community Legal Services
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
*
***************************/
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