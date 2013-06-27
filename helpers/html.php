<?php

function e($text="", $quote_style=ENT_COMPAT) {
	echo htmlentities($text, $quote_style);
}

function html_quote($text) {
	return htmlentities($text, ENT_QUOTES);
}

/**
 * Returns string of tag attributes from name=>value array
 *
 * @elements	array	array
 *
 */
function attr($elements=array()) {
	if (is_array($elements)) {
		$attributes = array();
		
		foreach ($elements as $key=>$value) {
			if(in_array(strtolower($key), array("nowrap", "checked", "selected"))) {
				$attributes[] = $key;
			} else {
				if(!is_numeric($key))
					$attributes[] = "$key=\"$value\"";
			}
		}
		
		return join(" ", $attributes);
	}
	if (is_string($elements)) {
		parse_str($elements, $elements);
		return attr($elements);
	}
	return false;
}

/**
 * Performs a Jackal::call and returns the output buffer as a string.  This is
 * useful for calling a page and sending the result somewhere else.  For
 * example, an email.
 *
 */
function returnCall() {
	// Find out what arguments we were called with
	$arguments = func_get_args();
	// Start output buffering so that nothing goes to the browser
	ob_start();
	// Execute the call we're supposed to
	call_user_func_array("Jackal::call", $arguments);
	// Get the output buffer
	$buffer = ob_get_contents();
	// Erase the output buffer
	ob_end_clean();
	// Return the output buffer as a string
	return $buffer;
}

/**
 * This will go through URI and pull out all the items in availableParameters, 
 * then return the leftovers as an attribute array
 * 
 * @param array   $URI                     An associative array of things to parse
 * @param mixed   $parameterNames          An array of words, or a single word 
 *                                        specifying the names of parameters to 
 *                                        be split out
 * @param boolean $returnAttributesAsArray True if splitAttributes should return 
 *                                        an attribute array instead of string. 
 *                                        If "string" is provided, then it will 
 *                                        ONLY return the attribute string 
 *                                        
 * @return array An associative array with [attributes] = attributes
 */
function splitAttributes($URI, $parameterNames, $returnAttributesAsArray=false) {
	// LPK ~ Added this so I can pass in strings like "user_id=1"
	if(is_string($URI)) $URI = stringToArray($URI);
	// Get the parameter keys that we're looking for
	$keyArray = array_flip((array) $parameterNames);
	// Get the parameters
	$parameters = array_intersect_key((array) $URI, $keyArray);
	// Get the attributes
	$attributes = array_diff_key((array) $URI, $keyArray);
	// Return the attributes as a string
	if($returnAttributesAsArray === "string") return rtrim(" ".attr($attributes));
	// Convert the attributes to a string
	if($returnAttributesAsArray != true) $attributes = rtrim(" ".attr($attributes));
	// Put attributes back into array
	$parameters["attributes"] = $attributes;
	
	// And give it all back
	return $parameters;
}

/**
 * Convert a query string into an array of name=>value parameters
 * 
 * @param $queryString	String	Contains "field1=value&field2=value"
 * @param $d1 			String 	First delimiter
 * @param $d2			String	Second delimiter
 * @return 				Arrau	Containing key=>value
 */
function stringToArray($queryString="", $d1="&", $d2="=") {
	$array = array();
	$queryString = "1|$queryString$d1";
	strtok($queryString, "|");
	while($name = strtok($d1)) {
		@list($key, $val) = explode($d2, trim($name));
		$array[$key] = $val;
	}
	return $array;
}

/**
 * Spits information out into the page in a format that makes it easy for a 
 * human to read, but hard for a computer to read
 * 
 * @param string $data The text to obfuscate
 */
function obfuscate($data="") {
	$id = md5($data);
	$obfuscated = '';
	
	for($i=0, $iMax=strlen($data); $i<$iMax; ++$i) {
		$obfuscated .= '<b>' . htmlentities($data[$i]) . '</b>';
		
		for($j=0, $jMax=rand(1,3); $j<$jMax; ++$j) {
			$obfuscated .= '<i>'.htmlentities(chr(rand(32, 126))).'</i>';
		}
	}
	
	if(!isset($GLOBALS['html/obfuscate/cssed'])) {
		$GLOBALS['html/obfuscate/cssed'] = true;
		?>
		<style type="text/css">
		
		.etacsufbo b {
			font-weight: normal;
		}
		
		.etacsufbo i {
			position : absolute;
			left     : -1000px;
			top      : -1000px;
		}
		
		</style>
		<?php 
	}
	
	echo '<div class="etacsufbo" style="display: none;" id="', $id,'">', $obfuscated,'</div>';
	
	?>
	<script type='text/javascript'>
		document.getElementById('<?php echo @$id; ?>').style.display = 'inline';
	</script>
	<?php 
}
