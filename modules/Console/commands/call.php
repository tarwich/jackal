<?php

/**
 * Passed to Jackal::call
 * 
 * This command is used to Jackal::call something. Pass in an object/method
 * pair as the first argument and any additional arguments you desire.
 * Feel free to experiment.
 * 
 * Example:
 * call Users/editUser user_id=1
 */

$command = @$URI[2];

if(!$command) {
	echo "Ambiguous command";
	return;
}

$parameters = implode(" ", array_slice(array_intersect_key($URI, array_values($URI)), 3));
preg_match_all('/(.*?)\s*=>?\s*("[^"]*"|\'[^\']*\'|.*?|.*?)(?:[,\s]+|&|$)/', $parameters, $parameters);
@list($nothing, $parameters["name"], $parameters["value"]) = $parameters;

$parameters = array_diff_key($parameters, array_values($parameters));
$parameters = (array) @array_combine($parameters["name"], $parameters["value"]);

foreach($parameters as $i=>$value) {
	if( ($value[0] == "'") || ($value[0] == '"') ) 
	if(@$value[0] == $value[strlen($value)-1]) {
		$parameters[$i] = substr($value, 1, -1);
	}
}

$result = Jackal::call($command, $parameters);

if($result !== 1) {
	echo $result;
}
