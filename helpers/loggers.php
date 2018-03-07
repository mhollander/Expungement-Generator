<?php

//Log calls to resources.
//
//Args: 
//  UserID: ID of the user authenticated to call a resource
//  Resource: Name of the resource called. Probably an endpoint
//  Action: Description of the action called on the resource requested
//  Timestamp: A timestamp
//
//Side Effect:
//  Writes a record with the input information to the resource log db table
//
//Returns:
//  Nothing.
//
function writeToResourceLog($userid, $resource, $action) {
	$stmt = $GLOBALS['db']->prepare("INSERT INTO resource_calls (userid, resource, action) VALUES (?, ?, ?)");
	$stmt->bind_param("sss",$userid,$resource,$action);
	if( $stmt->execute() ) {
		error_log("Resource call successfully logged.");
	} else {
		error_log("Resource call log failed.");
	}
	$stmt->close();
	
	return;
}


?>
