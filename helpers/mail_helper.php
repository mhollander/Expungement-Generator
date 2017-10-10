<?php 

// A helper to send an email.


function mailPetition($addr, $username, $response, $file_path) {
	/** Send an email 
	*
	* Args:
	* $addr (string): An email address
	* $user (string): A user's name
	* $response (Array): An array of information to be sent to the
	* 	user.
	* $file_path (string): The path to a file that will be emailed to the user
	**/
	require_once("vendor/autoload.php");
	global $sendGridApiKey;
	$msg = "Thank you for using the Expungement Generator.\r\n";
	$msg .= json_encode($response);
	$from = new SendGrid\Email("Expungement Generator API", "mhollander@clsphila.org");
	$subject = "EG API Generator Search";
	$to = new SendGrid\Email($username, $addr);
	$content = new SendGrid\Content("text/plain", $msg);
	$mail = new SendGrid\Mail($from, $subject, $to, $content);
	if ( !is_null($file_path) ) {
		$petition = new SendGrid\Attachment();
		$petition->setContent(base64_encode(file_get_contents($file_path)));
		$petition->setType("application/zip");
		$petition->setFilename("GeneratedPetition");
		$petition->setDisposition("attachment");
		$mail->addAttachment($petition);
	}
	$sg = new \SendGrid($sendGridApiKey);
	if ( is_null($sg) ) {throw new Exception("sg is null");}
	if ( is_null($sg->client) ) {throw new Exception("sg->client is null");}
	#print_r($sg);
	$response = $sg->client->mail()->send()->post($mail);
}

?>
