<?php
// config.php
// configuration settings for the expungement generator
//

$debug=FALSE;

// database connection information
$dbPassword = "xxxxxxxx";
$dbUser = "ronholla_ExpUser";
$dbName = "ronholla_expungementsite";
$dbHost = "localhost";


/*
require_once("/home/ronholla/tools/dbconnect.php");

$dataDir = "/home/ronholla/www/crepdb/data/";
$baseURL = "http://www.ronhollander.com/crepdb/";
$templateDir = "templates/";
$signatureDir = "/home/ronholla/www/crepdb/images/sigs/";
$toolsDir = "/home/ronholla/tools/";
$pdftotext = "pdftotext";
$tempFile = tempnam($dataDir, "FOO");
*/

require_once("c:\Mikes Program Files\wamp\\tools\dbconnect.php");

$dataDir = "c:\Mikes Program Files\wamp\www\crepdb\data\\";
$templateDir = "c:\Mikes Program Files\wamp\www\crepdb\\templates\\";
$signatureDir = "./images/sigs/";
$toolsDir = "c:\Mikes Program Files\wamp\\tools\\";
$baseURL = "http://localhost/crepdb/";
$tempFile = tempnam($dataDir, "FOO");
$pdftotext = "pdftotext.exe";


// set up the Message handler
require_once("Message.php");
$errorMessages = new Message();

// this logs a user in; must happen early on b/c of header requirements with session vars
require_once("doLogin.php");

?>