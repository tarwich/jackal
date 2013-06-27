<?php

function css($path, $top=false) {
	foreach((array) $path as $path) {
		$pieces = explode("/", $path, 3);
		
		if(count($pieces) == 1) {
			list($module, $action) = Jackal::scope();
			$path = "$module/resources/$path";
		}
		
		Jackal::call("Template/addResource/head/css", $path, (bool) $top);
	}
}

function js($path, $top=false) {
	foreach((array) $path as $path) {
		$pieces = explode("/", $path, 3);
		
		if(count($pieces) == 1) {
			list($module, $action) = Jackal::scope();
			$path = "$module/resources/$path";
		}
		
		Jackal::call("Template/addResource/head/js", $path, (bool) $top);
	}
}
