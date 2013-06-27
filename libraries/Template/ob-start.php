<?php

/**
 * Start output buffering and output compression
 * 
 * Internal method called at the beginning of a script to start output buffering.
 * This method may be called directly if needed, but that is not optimal.
 * This method starts output buffering and compression automatically if they
 * are enabled.
 * 
 * @return void
 */

// Find out if the ajax flag was set
if(Jackal::flag("AJAX")) {
	$this->disable_compression();
	while(ob_get_level()) ob_end_clean();
	$this->change(array("Template/ajax"));
	ob_start();
	return;
} elseif(Jackal::flag("PARTIAL")) { // Find out if the partial flag was set
	if(Jackal::setting("gzip")) $this->enable_compression();
	$this->ignoredResources = (array) $this->getResources("head");
	$this->change(array("Template/partial"));
	ob_start();
	return;
} else {
	if(Jackal::setting("gzip")) $this->enable_compression();
	ob_start();
}

?>
