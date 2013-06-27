<?php

/**
 * The purpose of this library is to provide functions to help manipulate 
 * strings.  
 * 
 */

class Strings {
	/**
	 * Parse a querystring (?foo=bar&baz=bag) and return an associative array
	 * of the result
	 * 
	 * This function will always return an array. Even if the input is invalid.
	 * However, if the input is an array, then that array will be returned.
	 * 
	 * @param String $string The input string to chew
	 * 
	 * @return array The processed querystring
	 */
	public function parseStr($URI) {
		// This function uses ordered arguments
		list($string) = $URI;
		// If the input is an array, then reuturn intact
		if(is_array($string)) return $string;
		// Initialize a new empty array
		$result = array();
		
		// We can only chew on strings
		if(is_string($string)) {
			// Initialize strtok to the string by padding with junk and searching for it
			strtok("0&$string", "&");
			while($part = strtok("&")) {
				list($key, $value) = explode("=", $part);
				$result[$key] = $value;
			}
		}
		
		// Return the array
		return $result;
	}
}
