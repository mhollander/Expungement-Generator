<?php
// config.php
// configuration settings for the expungement generator
//

$debug=false;


/*
$basedir = join(DIRECTORY_SEPARATOR, array("home", "expungem");
$toolsDir = join(DIRECTORY_SEPARATOR, array($basedir, "tools"));
$wwwdir = join(DIRECTORY_SEPARATOR, array ($basedir, "www"));
$baseURL = "https://expungementgenerator.org/";
$pdftotext = $toolsDir . DIRECTORY_SEPARATOR . "pdftotext";

*/

$basedir = join(DIRECTORY_SEPARATOR, array("c:", "mikes program files", "wamp"));
$toolsDir = join(DIRECTORY_SEPARATOR, array($basedir, "tools"));
$wwwdir = join(DIRECTORY_SEPARATOR, array ($basedir, "www", "eg"));
$baseURL = "http://localhost/eg/";
$pdftotext = "\"" . $toolsDir . DIRECTORY_SEPARATOR . "pdftotext.exe\"";


require_once($toolsDir . DIRECTORY_SEPARATOR . "dbinfo.php");

$dataDir = join(DIRECTORY_SEPARATOR, array($wwwdir, "data")) . DIRECTORY_SEPARATOR;
$templateDir = join(DIRECTORY_SEPARATOR, array($wwwdir, "templates")) . DIRECTORY_SEPARATOR;
$docketSheetsDir = join(DIRECTORY_SEPARATOR, array($wwwdir, "docketsheets")) . DIRECTORY_SEPARATOR;


$tempFile = tempnam($dataDir, "FOO");


// setup DB
require_once("dbconnect.php");

// set up the Message handler
require_once("Message.php");
$errorMessages = new Message();

// this logs a user in; must happen early on b/c of header requirements with session vars
require_once("doLogin.php");

?>