<?php

/**
 * Executes a Jackal::model call
 * 
 * Executes a Jackal::model call with the parameters passed 
 * Example:
 * model Foo/find
 * 
 * Later we're support passing parameters
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

$results = Jackal::model($command, $parameters);

$table = $this->asciiTable(array($results));

echo "<p>\n$table</p>";
