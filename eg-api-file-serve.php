<?php 

//Endpoint for serving files via api. 

	include('utils.php');
	include('helpers/api_validator.php');
	include_once('config.php');
	//Validate api key
	//If api key is valid for the user making the request, then return the requested file. 
	//error_log("Logging a request for a file.");
	if(!validAPIKey($_REQUEST, $db)) {
		http_response_code(403);
	} else {
		http_response_code(200);
		serveFile(cleanFilename($_REQUEST['filename']));
		exit;
	}

	function cleanFilename($fname) {
		// Strip a string of characters that shouldn't be in 
		// a filename.
		if (!(preg_match("/[^a-z0-9\.]/i", $fname) || preg_match("/[.]{2,}/i", $fname))) {
			return($fname);
		} else {
			$fname = preg_replace("/[^a-z0-9\.]/i","", $fname); //remove non-alphanumeric charaters or .
			$fname = preg_replace("/[\.]{2,}/i", ".", $fname);  //remove multiple .. characters.
			return(cleanFilename($fname));
		}
	}

?>
