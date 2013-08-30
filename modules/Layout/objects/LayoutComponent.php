<?php

// Get necessary resources and styles
css("Layout/resources/styles.css");

Jackal::loadLibrary("Component");

class Layout__LayoutComponent extends Component {
	// Make styles
	public static function makeStyles($URI) {
		$styles = "";
		foreach ($URI as $key=>$value) {
			$styles .= "$key: $value; ";
		}
		return $styles;
	}
	
	public static function str2Bool($value) {
		return strtolower($value)=="false" ? false : $value ;
	}

	// Get dimensions
	public static function getSize($size, $default="%") {
		$letters = self::lettersOnly($size);
		$numbers = self::numbersOnly($size);
		if ($letters != "px" && $letters != "%") $letters = $default;
		return $numbers.$letters;
	}

	public static function numbersOnly($text) {
		return preg_replace("/[^0-9]/i", '', $text);
	}

	public static function lettersOnly($text) {
		return preg_replace("/[^A-Z]/i", '', $text);
	}

	/**
	 * makeAttributes()
	 * 
	 * @param	$array			Array containing named parameters
	 * @param	$exclude		Array containing keys to excluded from $array
	 * @return	$attributes_	String of HTML friendly attributes
	 */
	public static function makeAttributes($array, $exclude=array()) {
		if (is_string($array)) {
			$array = self::makeArray($array);
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
	public static function makeArray($queryString="", $d1="&", $d2="=") {
		$array = array();
		$queryString = "1|$queryString$d1";
		strtok($queryString, "|");
		while($name = strtok($d1)) {
			@list($key, $val) = explode($d2, trim($name));
			$array[$key] = $val;
		}
		return $array;
	}
}

