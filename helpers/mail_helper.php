<?php 

// A helper to send an email.
function mailPetition($addr, $username, $response, $file_name) {
	/** Send an email 
	*
	* Args:
	* $addr (string): An email address
	* $user (string): A user's name
	* $response (Array): An array of information to be sent to the
	* 	user.
	* $file_name (string): The name of a file that will be emailed to the user
	**/
	require_once("vendor/autoload.php");
	//require_once("config.php");
	global $sendGridApiKey;
	global $dataDir;
	$msg = createHumanReadableExpungementResponseFromJSON(json_encode($response));
	$from = new SendGrid\Email("Expungement Generator API", "mhollander@clsphila.org");
	$subject = "EG API Generator Search";
	$to = new SendGrid\Email($username, $addr);
	$content = new SendGrid\Content("text/plain", $msg);
	$mail = new SendGrid\Mail($from, $subject, $to, $content);
	$file_path = join(DIRECTORY_SEPARATOR, array($dataDir, $file_name));

	// if we already created petitions, then we should be uploading those petitions.
    if ( !is_null($file_name) && file_exists($file_path) ) {
		$petition = new SendGrid\Attachment();
		$petition->setContent(base64_encode(file_get_contents($file_path)));
		$petition->setType("application/zip");
		$petition->setFilename("GeneratedPetitions.zip");
		$petition->setDisposition("attachment");
		$mail->addAttachment($petition);
	}
    
    // otherwise we should upload the downloaded dockets.  
    else 
    {
        // get each arrest grouping
        foreach ($response['results']['expungements_redactions'] as $arrest)
        {
            // now explode all of the docket numbers
            $dockets = explode(",", $arrest['docket']);
            foreach ($dockets as $docket)
            {
                // and find the pdf for each of the dockets and attach it to the email
                $file_path = join(DIRECTORY_SEPARATOR, array($dataDir, trim($docket))) . ".pdf";
                if(!is_null($file_path) && file_exists($file_path))
                {
                    $pdf = new SendGrid\Attachment();                                                                   
                    $pdf->setContent(base64_encode(file_get_contents($file_path)));                                     
                    $pdf->setType("application/pdf");                                                                   
                    $pdf->setFilename(trim($docket) . ".pdf");                                                             
                    $pdf->setDisposition("attachment");                                                                 
                    $mail->addAttachment($pdf);
                }
            }
        }
    }
    
	$sg = new \SendGrid($sendGridApiKey);
	if ( is_null($sg) ) {throw new Exception("sg is null");}
	if ( is_null($sg->client) ) {throw new Exception("sg->client is null");}
	#print_r($sg);
	$response = $sg->client->mail()->send()->post($mail);
}


//Helper to identify where an email should go.
function mailDestination($request) {
	// Given a request object, if the fields emailAddressField and 
	// emailDomainField are set and have valid characters,
	// use them to build an email address. Otherwise return the 'current_user' from the request object.
	//error_log("building email");
	if ( isset($request['emailAddressField']) && preg_match( '/^[a-z]{0,20}$/i', $request['emailAddressField'] )===1 
			&& isset($request[$request['emailAddressField']]) ) {
		//error_log("emailaddressfield is " . $request['emailAddressField']);
		//emailAddressField is valid, so we can use it for the emailAddress
		$emailAddress = $request[$request['emailAddressField']];
		if ( isset($request['emailDomain'] ) && preg_match('/^[a-z\-\.]{0,30}\.(org|com|net)$/', $request['emailDomain'])===1 ) {
			//emailDomain is valid (something like casemanager.com)
			error_log("emailDomain is " . $request['emailDomain']);
			$emailDomain = $request['emailDomain'];
			return($emailAddress . "@" . $emailDomain);
		}
	}
	//something failed. Return the userid.
	return($request['current_user']);
}

?>

