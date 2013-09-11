<?php

/**
 * Show information about the system
 * 
 * show modules - Shows a list of every module in the system
 * 
 */

// Get the command
$command = @$URI[2];

// Load all modules
$modules = $this->_getModuleList();

// show modules
if (strtolower($command)=="modules") {
	foreach ($modules as $module=>$path) {
		echo "<p>$module</p>";
	}
}

// Add more stuff to show