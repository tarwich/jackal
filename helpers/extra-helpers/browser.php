<?php

// Test for IE
if(!function_exists("IE")) {
	function IE() {
	    if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) return true;
	    	else return false;
	}
}

// Test for IE6
if(!function_exists("IE6")) {
	function IE6() {
	    if ( preg_match('/^Mozilla\/4\.0 \(compatible; MSIE 6/', @$_SERVER['HTTP_USER_AGENT'])) return true;
	    	else return false;
	}
}

function iPad() {
	if (strstr(strtoupper($_SERVER['HTTP_USER_AGENT']), "IPAD")) return true;
    	else return false;
}

function Firefox() {
	if (strstr(strtoupper($_SERVER['HTTP_USER_AGENT']), "FIREFOX")) return true;
		return false;
}