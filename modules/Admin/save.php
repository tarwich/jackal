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
	// Special case for boolean value
	if($value === "false") $value = false;
	// If the value hasn't changed, then leave it alone
	if(Jackal::setting($name) === $value) continue;
	// Deep set this value
	eval('$settings["' . implode('"]["', explode("/", $name)) . '"] = $value;');
	// Update Jackal with the setting
	Jackal::putSettings($name, $value);
}

// Convert settings to YAML
$settings = $spyc->YAMLDump($settings);

// Write the settings to the config file
if(@file_put_contents($configFile, $settings)) {
	Jackal::call("Admin/showSection/$section");
}

else {
	// Store the settings in the session so the user can download it
	Jackal::call("Session/store/admin/file", $settings);
	// Get the url to the file image
	$iconURL = Jackal::siteURL("Admin/resources/page.png");
	// Get the url to download the settings file
	$fileURL = Jackal::siteURL("Admin/download/admin_.yaml");
	
	echo "
		<span class='error'>
			I was unable to save the file. You need to copy the text below and save it to
			<blockquote>$configFile</blockquote>
			(Or <a href='$fileURL'><i><img src='$iconURL' /></i>click here</a> to download it)
		</span>
		<textarea>$settings</textarea>";
}
