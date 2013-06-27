<?php

if(PHP_VERSION < "5.2.0") {
	// --------------------------------------------------
	// json_encode
	// --------------------------------------------------
	if(!function_exists("json_encode")) {
		function json_encode($a=false) {
			if (is_null($a)) return 'null';
			if ($a === false) return 'false';
			if ($a === true) return 'true';
			if (is_scalar($a))
			{
				if (is_float($a))
				{
					// Always use "." for floats.
					return floatval(str_replace(",", ".", strval($a)));
				}
	
				if (is_string($a))
				{
					static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
					return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
				}
				else
				return $a;
			}
			$isList = true;
			for ($i = 0, reset($a); $i < count($a); $i++, next($a))
			{
				if (key($a) !== $i)
				{
					$isList = false;
					break;
				}
			}
			$result = array();
			if ($isList)
			{
				foreach ($a as $v) $result[] = json_encode($v);
				return '[' . join(',', $result) . ']';
			}
			else
			{
				foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
				return '{' . join(',', $result) . '}';
			}
		}
	}
	
	// --------------------------------------------------
	// http_build_query
	// --------------------------------------------------
	if(!function_exists("http_build_query")) {
		function http_build_query($formData, $numericPrefix="") {
			$parts = array();
			
			foreach($formData as $k=>$v) {
				if(is_numeric($k)) $k = "$numericPrefix$k";
				$v = urlencode($v);
				$parts[] = "$k=$v";
			}
			
			return implode("&", $parts);
		}
	}
}
