<?php

/**
 * Run the tests to check for database settings being correct
 * 
 * Returns a test result array
 * 
 * @example Test results:
 * <code type='php'>
 * 	return array(
 * 		"ERROR"   => array("This is an error message"),
 * 		"WARNING" => array("This is a warning message"),
 * 		"OK"      => array("This is an informational message"),
 * 	);
 * </code>
 * 
 * @return array
 */

// Create an array that we will populate with messages generated from the test(s)
$messages = array(
    "WARNING" => array(),
    "ERROR"   => array(),
    "OK"      => array(),
);

// Get the database settings
$database = Jackal::setting("database");

// If no host, then database disabled
if(!$database["host"]) $messages["OK"][] = "Database disabled";

else {
	// Run the connection test
	$mysql = @new mysqli(
		$host    = $database["host"],
		$username= $database["username"],
		$passwd  = $database["password"], 
		$dbname  = $database["database"],
		$port    = $database["port"], 
		$socket  = $database["socket"]
	);

	// Document any connection errors
	if($mysql->connect_error) $messages["ERROR"][] = "Database: $mysql->connect_error";
	else $messages["OK"][] = "Connected to $dbname@$host successfully";
}

return $messages;