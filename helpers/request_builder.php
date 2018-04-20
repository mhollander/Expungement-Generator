<?php
// Function parses $_REQUEST and json post data to return the information that the user sent to the api
// This is because we want to be able to accept JSON info sent with the HTTP headers that may not be in the post-request object.  
// First checks to see if there is JSON not in the 

function request_builder() 
{
        if (array_key_exists("apikey", $_REQUEST))
            return $_REQUEST;
      
        $data = file_get_contents('php://input');
	$json = json_decode($data, true);
    	return $json;
} 


?>
