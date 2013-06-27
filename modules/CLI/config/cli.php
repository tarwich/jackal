<?php

Jackal::putSettings("
	jackal:
		flaggers: [ cli ]
		
	cli:
");

if(@$GLOBALS["argv"]) {
	$argv = $GLOBALS["argv"];
	
	// Set the CLI flag
	Jackal::flag("cli", true);
	// Fix the request data
	$_SERVER["argv"] = $argv;
	$_SERVER["argc"] = $GLOBALS["argc"];
	foreach($argv as $i=>$value) $_GET[$i] = $value;
	$_SERVER["REQUEST_URI"] = dirname($_SERVER["PHP_SELF"]) . @"/$argv[1]";
	// Turn off the template
	Jackal::call("Template/disable");
}
