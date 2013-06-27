<?php

function pluralize($word) {
	$length = strlen($word);
	
	if($word[$length-1] == "s") {
	} elseif($word[$length-1] == "y") {
		$word = substr($word, 0, $length - 1) . "ies";
	} else {
		$word = $word . "s";
	}
	
	return $word;
}
