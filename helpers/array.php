<?php 

if(!function_exists("coalesce")) {
	function coalesce() {
		// Get the arguments
		$arguments = func_get_args();
		// If there is only 1 argument and it's an array, then coalesce that array
		if(count($arguments) == 1) if(is_array($arguments[0])) $arguments = $arguments[0];
		
		eval('($result = $arguments['.implode(']) || ($result = $arguments[', array_keys($arguments)).']);');
		return $result;
	}
}

// --------------------------------------------------
// extractFields
// --------------------------------------------------
/**
 * Get a certain field (or fields) from an array of arrays
 *
 * @param array $array
 * @param array/string $field[s] either the name of the field or an array of field names
 */
function extractFields($array, $fields) {
	$result = array();
	$item = array();
	
	// Convert field[s] into keys
	$filter = array_flip((array) $fields);
	
	// Add each item to the result 
// Old way: Was re-ordering keys, so removed
//	foreach($array as $row) $result[] = array_intersect_key($row, $filter); 
	foreach($array as $row) {
		foreach((array) $fields as $name) $item[$name] = @$row[$name];
		$result[] = $item;
	}
	
	return $result;
}

/**
 * Return an associative array with groupField as the key and containing all 
 * of the original items as children of those entries.
 * 
 * @param array $array
 * @param string $groupField
 */
function groupArray($array, $groupField) {
	foreach($array as $i=>$item) {
		@$result[$item[$groupField]][$i] = $item;
	}
	
	return (array) @$result;
}

/**
 * Return a single item from an array 
 * 
 * @param array $array | The source array from which to pull an item
 * @param mixed $index | The index of the item to return. This may be a string
 *                     | or an integral index
 *
 * @return mixed       | The item from $array at $index
 */
function array_item($array, $item=0) {
	return @$array[$item];
}

/**
 * Map an array of items to an array with $key as the key
 *
 * @param array $array
 * @param string $key
 * @return array $array
 * 
 */
function rekey($array, $key) {
	$result = array();
	
	foreach($array as $item) {
		(is_object($item)) && ($newKey = $item->$key) || ($newKey = $item[$key]);
		$result[$newKey] = $item;
	}
	
	$result = array_reverse($result, true);
	
	return $result;
}

/**
 * Merge two or more arrays together. Method is a hybrid of PHP's built-in functions
 * array_merge_recursive() and array_replace_recursive()
 * 
 * - Associative keyed values are replaced
 * - Numerically keyed values are added
 * 
 * @param	array	$array1	The base array
 * @param	array	$array2	An array to merge into the first array
 * 
 * @return array $result
 */
function merge_arrays() {
	// Copy the arguments into a new array so that we can easier operate on them
	$arguments = func_get_args();
	// Prepare the result array in order to prevent any errors
	$result = $arguments[0];
	
	// Go through all the arguments
	while($array = next($arguments)) {
		// Go through all the items in this array
		while(@list($name, $value) = each($array)) {
			if(is_numeric($name)) {
				// Numeric indices are appended with new indexes  
				$result[] = $value;
			} elseif(is_array($value)) {
				// Arrays are sub-merged
				$result[$name] = merge_arrays((array) @$result[$name], $value);
			} else {
				// Simple values override
				$result[$name] = $value;
			}			
		}
	}
	
	// Return the result array
	return $result;
}

/**
 * 
 * Sort an array by the value of a sub array
 * 
 * @param Array $array
 * @param String $key
 * @return Array $array
 * 
 */
function arraySortBySubValue($array, $key) {
	usort($array, create_function('$a, $b', "return strcmp(\$a['$key'], \$b['$key']);"));
	return $array;
}

?>
