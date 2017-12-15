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


// for a linux system
$basedir = join(DIRECTORY_SEPARATOR, array("", "srv", "EG"));
$toolsDir = join(DIRECTORY_SEPARATOR, array("", "usr", "local", "bin"));
$includeDir = join(DIRECTORY_SEPARATOR, array("", "usr", "local", "include"));
$wwwdir = join(DIRECTORY_SEPARATOR, array ($basedir, "eg-test"));
$casperScript = join(DIRECTORY_SEPARATOR, array($includeDir, "cpcmsNavigate", "searchCPCMS.js"));
$casperjsCommand = join(DIRECTORY_SEPARATOR, array($toolsDir, "casperjs"));
$baseURL = "https://eg-test.clsphila.org/";
$pdftotext = $toolsDir . DIRECTORY_SEPARATOR . "pdftotext";
$sendGridApiKey = "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";

/*
// for a windows system
$basedir = join(DIRECTORY_SEPARATOR, array("c:", "wamp"));
$toolsDir = join(DIRECTORY_SEPARATOR, array($basedir, "tools"));
$wwwdir = join(DIRECTORY_SEPARATOR, array ($basedir, "www", "eg"));
$baseURL = "http://localhost/eg/";
// windows version of pdftotext must be 3.03.  Although it may work with a later version, I have actually run into problems where the parser parses pdfs differently in the newer version
$pdftotext = "\"" . $toolsDir . DIRECTORY_SEPARATOR . "pdftotext.exe\"";
*/

// these shouldn't ever need to change
$dataDir = join(DIRECTORY_SEPARATOR, array($wwwdir, "data")) . DIRECTORY_SEPARATOR;
$templateDir = join(DIRECTORY_SEPARATOR, array($wwwdir, "templates")) . DIRECTORY_SEPARATOR;
$docketSheetsDir = join(DIRECTORY_SEPARATOR, array($wwwdir, "docketsheets")) . DIRECTORY_SEPARATOR;


// db information
$dbPassword = "Wanda fish taco";
$dbUser = "eg_test";
$dbName = "eg_test_site";
$dbHost = "localhost";

// this is only needed in the CLS production environmnet
/*
$crepDBPassword = "fakepassword";
$crepDBUser = "fakeusername";
$crepDBName = "eg";
$crepDBHost = "mydburl.org";
*/


$tempFile = tempnam($dataDir, "FOO");


// setup DB
require_once("dbconnect.php");

// set up the Message handler
require_once("Message.php");
$errorMessages = new Message();

// this logs a user in; must happen early on b/c of header requirements with session vars
require_once("doLogin.php");

?>
