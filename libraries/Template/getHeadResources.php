<?php

// LPK ~ Fix so that I don't have to use Template/head in order to get resources loaded with js() and css()

$css = (array) @$this->exResources["head"]["css"];
$js = (array) @$this->exResources["head"]["js"];

if(Jackal::flag("styling")) {
	$existingJS = $existingCSS = array();
} elseif(Jackal::flag("partial")) {
	$existingCSS = explode(" ", @$_COOKIE["css"]);
	$existingJS = explode(" ", @$_COOKIE["js"]);
} else {
	$existingJS = $existingCSS = array();
}

// See if we're in debug mode
$debugging = Jackal::debugging();
$partial = Jackal::flag("partial");
// Store the headers in order to reset the content type later
$headers = headers_list();

ob_start();

foreach($css as $entry) {
	$token = md5($entry["file"]);
	if(in_array($token, $existingCSS)) continue;
	else $existingCSS[] = $token;
	$debug_ = $debugging ? "origin='$entry[origin]' file='$entry[file]' md5='$token'" : "";
	
	if($partial) {
		$data = Jackal::handleRequest($entry["file"], true);
		echo "\n<style type='text/css' location='head' $debug_>$data</style>";
	} else {
		$file = Jackal::siteURL($entry["file"]);
		echo "\n<link rel='stylesheet' type='text/css' href='$file' $debug_ />";
	}
}

foreach($js as $entry) {
	$token = md5($entry["file"]);
	if(in_array($token, $existingJS)) continue;
	else $existingJS[] = $token;
	$debug_ = $debugging ? "origin='$entry[origin]' file='$entry[file]'" : "";
	
	if($partial) {
		$data = Jackal::handleRequest($entry["file"], true);
		echo "\n<script type='text/javascript' location='head' $debug_>$data</script>";
	} else {
		$file = Jackal::siteURL($entry["file"]);
		echo "\n<script type='text/javascript' src='$file' $debug_></script>";
	}
}

return ob_get_clean();