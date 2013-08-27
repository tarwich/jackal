<?php

/**
 * Save settings to disk
 * 
 * This method is intended to be called with form data.
 * 
 * @param array $settings Associative array of settings to save
 * 
 * @return void
 */

// Parse URI
// - section
($section = @$URI["section"]) || ($section = @$URI[0]);

// Make an array of the settings we're going to save
$settings = array();
// Load the SPYC library for writing the yaml
$spyc = Jackal::loadLibrary("spyc");
// Get the admin config folder
list($configFolder) = Jackal::files(dirname(__FILE__) . "/config/");
// Setup the config file
$configFile = "$configFolder/admin_.yaml";
// Load settings from config file
$settings = $spyc->YAMLLoad((string) @file_get_contents($configFile));

// Go through all items in the uri
foreach($URI as $name=>$value) {
	// These items are invalid
	if(is_numeric($name)) continue;
	if($name == "undefined") continue;
	// Deep set this value
	eval('$settings["' . implode('"]["', explode("/", $name)) . '"] = $value;');
}

// Convert settings to YAML
$settings = $spyc->YAMLDump($settings);

// Write the settings to the config file
if(@file_put_contents($configFile, $settings)) {
	Jackal::call("Admin/section/$section");
}

else {
	echo "
		<span class='error'>
			I was unable to save the file. You need to copy the text below and save it to
			<blockquote>$configFile</blockquote>
		</span>
		<textarea>$settings</textarea>";
}
