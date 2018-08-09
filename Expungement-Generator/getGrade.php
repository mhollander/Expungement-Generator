<?php

require_once("config.php");

$db = new mysqli($chargeDBHost, $chargeDBUser, $chargeDBPassword, "cpcms_aopc_summary");
if ($db->connect_error) 
    die('Error connecting to the db: Connect Error (' . $db->connect_errno . ') ' . $db->connect_error);

$title = $db->real_escape_string($_GET['title']);
$section = $db->real_escape_string($_GET['section']);

$query1 = "SELECT * FROM crimes_wo_subsection WHERE TITLE=$title AND Section like '$section%' ORDER BY Number DESC";
$query2 = "SELECT * FROM crimes_w_subsection WHERE TITLE=$title AND Section like '$section%' ORDER BY Number DESC";

$data = array();
$data["section"] = array();
$data["subsection"] = array();

$result = $db->query($query2);
while ($row = $result->fetch_assoc())
  $data["subsection"][] = $row;

$result = $db->query($query1);
while ($row = $result->fetch_assoc())
  $data["section"][] = $row;

echo json_encode($data);
    

?>
