<?php
/*************************************
* config.php
* configuration settings for the expungement generator
*
*	Copyright 2011-2015 Community Legal Services
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
**************************/

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