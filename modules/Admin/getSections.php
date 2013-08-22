<?php

/**
 * Gets all the modules from setting "admin/modules" and returns the section information
 * 
 * This method will iterate through the admin/modules setting and get the 'name' property of each, hashing
 * the setting on the name and returning the new hash.
 * 
 * Example: some-admin.yaml
 * <code language="yaml">
 *  	admin:
 *  		modules:
 *  			foo:
 *  				name: Foo / Stuff
 * </code>
 * 
 * Example: This is what will be returned
 * <code language="php">
 *  	$result = array(
 *  		"Foo" => array(
 *  			"Stuff" => array()
 *  		)
 *  	);
 * </code>
 * 
 * @return array
 */

// Prepare the result to return
$result = array();

// Go through all the modules in the config
foreach((array) Jackal::setting("modules") as $module) {
    // Go through all the sections in this module
    foreach((array) @$module as $section) {
        // Break apart the name of this section by '/'
        list($a, $b) = explode('/', (string) @$section["name"]);
        // Add this section to the results
        $result[$a][$b][] = $section;
    }
}

return $result;

