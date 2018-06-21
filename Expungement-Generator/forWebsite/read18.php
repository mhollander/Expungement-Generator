<html>
<?php
$recordDir = "http://localhost/crep/data/";
$record = "test24.txt";

$thisRecord = file($recordDir . $record);


foreach ($thisRecord as $line_num => $line)
{
	print "$line_num: $line <br />";
}
?>
</html>