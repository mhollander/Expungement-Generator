<?php
// config.php
// configuration settings for the expungement generator
//

$debug=false;


/*
require_once("/home/ronholla/tools/dbinfo.php");
require_once("dbconnect.php");
$dataDir = "/home/ronholla/www/crepdb/data/";
$baseURL = "http://www.ronhollander.com/crepdb/";
$templateDir = "templates/";
$signatureDir = "/home/ronholla/www/crepdb/images/sigs/";
$toolsDir = "/home/ronholla/tools/";
$pdftotext = "pdftotext";
$tempFile = tempnam($dataDir, "FOO");

*/
require_once("c:\wamp\\tools\dbinfo.php");

$dataDir = "c:\wamp\www\Expungement-Generator\data\\";
$templateDir = "c:\wamp\www\Expungement-Generator\\templates\\";
$signatureDir = "./images/sigs/";
$toolsDir = "c:\wamp\\tools\\";
$baseURL = "http://localhost/Expungement-Generator/";
$tempFile = tempnam($dataDir, "FOO");
$pdftotext = "pdftotext.exe";


// setup DB
require_once("dbconnect.php");

// set up the Message handler
require_once("Message.php");
$errorMessages = new Message();

// this logs a user in; must happen early on b/c of header requirements with session vars
require_once("doLogin.php");

?>