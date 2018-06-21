<?php
/****************************************************************
*
*	secureServe.php
*	Serves any file from the data dir, but only to users who are logged in.  This is done to avoid
 *  anyone from coming and downloading files from teh data dir, which may have client info in it.
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
****************************************************************/


	require_once("config.php");
	require_once("utils.php");
	require_once("Attorney.php");

// if the user is logged in, tell them they they are already logged in
if (!isLoggedIn())
{
    print "<div class='main'><div class='content-left'>&nbsp;";
    include("messagedisplay.php");
    print "</div><div class='content-center'>";
    require_once("head.php");
    require_once("header.php");
    print "<div class='titleMessage'>You must be logged in to download this file. </div>";
	print "</div><div class='content-right'>&nbsp;</div></div>";
	include("foot.php");
}

// else, we want to serve the file
else
{
    serveFile($_REQUEST['serveFile']);
} // end else !isLoggedIn();
              
?>
