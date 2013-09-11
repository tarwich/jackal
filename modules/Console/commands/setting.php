<?php

/**
 * Shows the value of a setting
 * 
 * Pass in the path to a setting separated by slashes. To see all settings, 
 * do not pass anything.
 * 
 * Example:
 * setting jackal/default-module
 * 
 */
($path = @$URI[2]);

if($path) {
	$setting = Jackal::setting($path);
	echo "<p>Setting $path: ".htmlentities(print_r($setting, 1))."</p>";
} else {
	echo "<p>All settings:".htmlentities(Jackal::query("_settings"))."</p>";
}

