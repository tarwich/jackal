<?php

/**
 * Outputs the template
 * 
 * Internal method used by template to output the current template and end
 * output buffering
 * 
 * @return void
 */

// Allow template to be disabled
if(@$this->disabled) return; 

//
// Find out if jackal is ajaxing =)
//
$ajax = Jackal::flag("AJAX");

if ($ajax) {
	Jackal::call("Template/change/Template/ajax");
	return;
}

// See if a message has been provided
$message = Jackal::setting("template/template-message");

if($message) {
	Jackal::call($message);
}

?>
