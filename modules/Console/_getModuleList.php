<?php

/**
 * Internal function used by Documentation to get the list of modules in the 
 * system.
 */

//  __________________________________________________
// / Find modules                                     \

// Get all the modules and libraries in the system
$glob = Jackal::setting("class-path");
$paths = Jackal::files($glob, array("MODULE" => "*", "OTHER" => ""));
// Get the actual NAME of the module
foreach($paths as $path) $modules[str_replace(".php", "", basename($path))] = $path;
// Sort the modules
uksort($modules, "strnatcasecmp");

$ignore = Jackal::setting("documentation/ignore");

$modules = array_diff_key($modules, array_flip($ignore));

// \__________________________________________________/

return $modules;