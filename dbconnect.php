<?php
/*************************
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
***********************************/
	require_once("config.php");
	$db = new mysqli($dbHost,  $dbUser,  $dbPassword, $dbName);
	if ($db->connect_error)
		die('Error connecting to the db: Connect Error (' . $db->connect_errno . ') ' . $db->connect_error);
    $db->set_charset("utf8");

    // charge database, used to assess Act 5 compliance
    $chargeDB = new mysqli($chargeDBHost, $chargeDBUser, $chargeDBPassword, $chargeDBName);        
    if ($chargeDB->connect_error)
       die('Error connecting to the charge db: Connect Error (' . $chargeDB->connect_errno . ') ' . $chargeDB->connect_error);

?>
