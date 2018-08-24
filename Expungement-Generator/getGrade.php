<?php

require_once("config.php");


$aopc_db = new mysqli($chargeDBHost, $chargeDBUser, $chargeDBPassword, "cpcms_aopc_summary");
if ($aopc_db->connect_error) { 
    error_log("Error connecting to aopc db");
    die('Error connecting to the db: Connect Error (' . $aopc_db->connect_errno . ') ' . $aopc_db->connect_error);
}


$title = $aopc_db->real_escape_string($_GET['title']);
$section = $aopc_db->real_escape_string($_GET['section']);

$query1 = "SELECT * FROM crimes_wo_subsection WHERE TITLE='$title' AND Section like '$section%' ORDER BY Number DESC";
$query2 = "SELECT * FROM crimes_w_subsection WHERE TITLE='$title' AND Section like '$section%' ORDER BY Number DESC";


$data = array();
$data["section"] = array();
$data["subsection"] = array();

$result = $aopc_db->query($query2);
while ($row = $result->fetch_assoc())
  $data["subsection"][] = $row;

$result = $aopc_db->query($query1);
while ($row = $result->fetch_assoc())
  $data["section"][] = $row;

echo json_encode($data);
    

?>
