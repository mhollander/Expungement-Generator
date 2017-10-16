<?php 

	function validAPIKey($request, $db) {
		//
		//
		// Args:
		// 	$request (Array): An array that looks like the $_REQUEST array
		//	$db (mysqli): A connection to a mysql database.
		if (!isset($request['current_user'])) {
			error_log("invalid b/c current_user isn't set");
			return False;
		}
		$useremail = $db->real_escape_string($request['current_user']);
		if (isset($request['apikey'])) {
			$query = $db->prepare("SELECT apiKey from user as u left join userinfo as ui on u.userid=ui.userid left join program as p on ui.programID=p.programid WHERE u.email=?");
			$query->bind_param("s", $useremail);
			$query->execute();
			$query->bind_result($apikey_hashed);
			$query->fetch();
			$query->close();
			if (!$apikey_hashed) {
				return False;
			};
		if (password_verify($request['apikey'], $apikey_hashed)) {
			// The user submitted the correct api key, so now find the userid number.
			$query = $db->prepare("SELECT userid from user where email = ?");
			$query->bind_param("s",$useremail);
			$query->execute();
			$query->bind_result($userid);
			$query->fetch();
			$query->close();
			if (!$userid) {
				return False;
				error_log("invalid because user id not found");
			};
			return $userid;
			};
		}; 
		error_log("invalid because api key did not verify");
		return False;
	};

?>
