<?php

function url($url, $flags=array()) {
	return Jackal::siteURL($url, $flags);
}

function ajax($url) {
	return Jackal::siteURL($url, "ajax");
}

function redirect($url) {
	if(false);
	
	//
	// HTTP:// HTTPS:// FTP:// TV://
	//
	elseif(strpos($url, "://")!==false) {
		header("Location: $url");
	}
	
	//
	// Starts with ?, so it's already ready
	//
	elseif(substr($url, 0, 1)=="?") {
		header("Location: $url");
	}
	
	// Pass it through URL
	else {
		$url = url($url);
		
		header("Location: $url");
	}
}

//
// Import Jackal's javascript (which has url in it)
//
Jackal::call("Template/addResource/head/js", "resources/url.js");

?>