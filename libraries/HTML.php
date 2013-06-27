<?php

class HTML extends JackalModule {
	/**
	 * makeAttributes()
	 * 
	 * @param	$array			Array containing named parameters
	 * @param	$exclude		Array containing keys to excluded from $array
	 * @return	$attributes_	String of HTML friendly attributes
	 */
	public function makeAttributes($URI) {
		@( ($array = $URI["array"]) || ($array = $URI[0]) || ($array = array()) );
		@( ($exclude = (array) $URI["exclude"]) || ($exclude = (array) $URI["1"]) || ($exclude = array()) );

		// If the value is a string, then convert before parsing
		if(is_string($array)) $array = makeArray($array);
		// Remove items that should be excluded
        $attributes = array_diff_key($array,      // The values
                array_flip($exclude),             // The items to exclude
                array_keys(array_values($array)), // Any numeric items
                array("segments" => 0));          // Remove 'segments'
		// Initialize the output string
		$attributes_ = "";
		
		foreach($attributes as $name=>$value) {
			@$attributes_ .= ' '.$name.'="'.htmlentities($value, ENT_QUOTES).'"';
		}

		return $attributes_;
	}
}
