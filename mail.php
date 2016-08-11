<?php

/*****************************************
*
*	mail.php
*	Accepts a form with a legalserver case id and docket numbers
 * and sends an email to legalserver(@clsphila) with the numbers and a link
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
*********************************************/

//	require_once("config.php");
//	require_once("utils.php");
    require_once("CPCMS.php");
	include("head.php");

?>
    <div class='main'>
    <div class='pure-u-8-24'>&nbsp;</div>
    <div class='pure-u-12-24'> 

<?php
if (!empty($_POST))
{
    // do some stuff - this is a submit;
    $message = "The EG found the following cases for this individual:\r\n\r\n";
    $message .= "Summary Docket: " . $_POST['bestDocket'] . " | " . CPCMS::$summaryURL . $_POST['bestDocket'] . "\r\n\r\n";
    $message .= "All dockets: \r\n\r\n";
    foreach ($_POST['dockets'] as $docket)
    {
        $message .= $docket . " | " . CPCMS::$docketURL . $docket . "\r\n";
    }
    
    $emailAddress = $_POST['lsNumber'] . "@clsphila.legalserver.org";
    mail($emailAddress, 'EG Generated Docket Search', $message);
    print "Email successfully sent to $emailAddress";
}

else
{
    print "You didn't properly submit anything for me to mail.  Blech!";
}

?>
    </div>
	<div class="content-right">&nbsp;</div>
</div>

<?php 
	include("foot.php");
?>