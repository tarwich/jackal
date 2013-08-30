<?php

// Make styles
function layout_makeStyles($URI) {
	$styles = "";
	foreach ($URI as $key=>$value) {
		$styles .= "$key: $value; ";
	}
	return $styles;
}

// Get dimensions
function layout_getSize($size, $default="%") {
	$numbers = layout_numbersOnly($size);
	$symbols = layout_symbolsOnly($size);
	if (!$symbols) $symbols = $default;
	return $numbers.$symbols;
}

function layout_numbersOnly($text) {
	return preg_replace("/[^0-9]/i", '', $text);
}

function layout_symbolsOnly($text) {
	return preg_replace("/[A-Za-z0-9]/i", '', $text);
}

function layout_lettersOnly($text) {
	return preg_replace("/[^A-Z]/i", '', $text);
}

/**
 * makeAttributes()
 * 
 * @param	$array			Array containing named parameters
 * @param	$exclude		Array containing keys to excluded from $array
 * @return	$attributes_	String of HTML friendly attributes
 */
function layout_makeAttributes($array, $exclude=array()) {
	if (is_string($array)) {
		$array = layout_makeArray($array);
	}
	$attributes = array_diff_key($array, array_flip($exclude));
	$attributes_ = "";
	foreach($attributes as $name=>$value) {
		if(!is_numeric($name)) {
			@$attributes_ .= " $name=\"$value\"";
		}
	}
	return $attributes_;
}

/**
 * makeArray()
 * 
 * @param $queryString	String	Contains "field1=value&field2=value"
 * @param $d1 			String 	First delimiter
 * @param $d2			String	Second delimiter
 * @return 				Arrau	Containing key=>value
 */
function layout_makeArray($queryString="", $d1="&", $d2="=") {
	$array = array();
	$queryString = "1|$queryString$d1";
	strtok($queryString, "|");
	while($name = strtok($d1)) {
		@list($key, $val) = explode($d2, trim($name));
		$array[$key] = $val;
	}
	return $array;
}