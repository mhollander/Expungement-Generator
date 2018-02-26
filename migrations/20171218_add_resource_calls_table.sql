CREATE TABLE IF NOT EXISTS resource_calls (
		id int(11) NOT NULL, 
		userid smallint(6) NOT NULL COMMENT 'the user that made the resource call',
		resource VARCHAR(100) NOT NULL COMMENT 'The endpoint called, i.e. eg-api.php',
		action VARCHAR(100) COMMENT 'The action performed on the resource, i.e. searchCPCMS or GeneratePetitions', 
		timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,   
		PRIMARY KEY(id)) 
	ENGINE=InnoDB DEFAULT CHARSET=utf8; 
