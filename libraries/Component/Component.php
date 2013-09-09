<?php

class Component {
	protected $_node = NULL;
	
	/**
	 * A list of the classes that should be added to the base tag of this 
	 * component.
	 * 
	 * @var array
	 */
	protected $_classes 	= array();
	protected $_attributes 	= array();
	
	public function __construct($URI=array()) {
		// The domnode for this object
		$this->_node = @$URI["node"];
		// Remove the 'node' element from the URI
		unset($URI["node"]);
		// Assign all the parameters from URI
		foreach($URI as $name=>$value) $this->$name = $value;
		// Assign all the child nodes as parameters
		if($this->_node instanceof DOMNode)
		if(@$this->_node->childNodes)
		foreach($this->_node->childNodes as $child) $this->{$child->nodeName} = $child;
		if($this->_node instanceof HTML_Node)
		if(@$this->_node->children)
		foreach((array) $this->_node->children as $child) $this->{$child->tag} = $child;
	}
	
	/**
	 * Adds a class to the list of classes that may later be included in the 
	 * component
	 * 
	 * The classes are in an associative array where the class names are the 
	 * keys of the array. This prevents duplicates in a clean and efficient
	 * manner.
	 * 
	 * @param String $class The name of the class to add. In order to add 
	 * 						multiple classes at once you can put them all in
	 * 						one space-separated string (just like html)
	 * 
	 * @return void
	 */
	public function addClass($class) {
		// Break apart the classes by css rules 
		$classes = str_word_count($class, 1);
		// Add all the classes to our existing array
		$this->classes = array_merge((array) @$this->classes, array_fill_keys($classes, true));
	}
	
	/**
	 * Updates the attribute with $name and returns it. If $value is omitted, 
	 * then the attribute will only be returned and will not be updated. 
	 * 
	 * @param String $name The name of the attribute to update
	 * @param mixed $value The new value of the attribute
	 * 
	 * @return mixed The new value of the attribute
	 */
	public function attr($name /*, $value=""*/) {
		$_name = "_" . ltrim($name, "_");
		
		switch(func_num_args()) {
			case 1: return @$this->attributes[$name];
			case 2: return $this->attributes[$name] = $this->$_name = func_get_arg(1);
		}
	}
	
	/**
	 * Returns the attributes of the node as an array
	 *
	 * @param DOMNode $node The node to process
	 * 
	 * @return array The attributes
	 */
	public static function nodeAttributes($node) {
		$attributes = array();
		
		foreach($node->attributes as $name=>$value) 
			$attributes[$name] = $value->textContent;
		
		return $attributes;
	}
	
	/**
	 * Convert a DOMNode to a matrix (N) deep
	 *
	 * @param int $depth How many levels deep the matrix should be (0 = infinity)
	 *
	 * @return array The matrix
	 */
	public static function nodeToMatrix($node, $depth=0) {
		$result = array();
		
		switch($depth) {
			case 0:
				foreach($node->childNodes as $child) {
					if($child instanceof DOMText) if(!trim($child->textContent, " \t\r\n")) continue;
					$URI = array("contents" => self::nodeXML($child->childNodes));
					$result[$child->nodeName][] = $URI + self::nodeAttributes($child);
				}
				return $result;

			case 1:
				foreach($node->childNodes as $child) {
					if($child instanceof DOMText) if(!trim($child->textContent, " \t\r\n")) continue;
					$URI = array("contents" => self::nodeXML($child->childNodes));
					$result[$child->nodeName][] = $URI + self::nodeAttributes($child);
				}
				return $result;

			default:
				foreach($node->childNodes as $child) {
					if($child instanceof DOMText) if(!trim($child->textContent, " \t\r\n")) continue;
					$URI = self::nodeToMatrix($child, $depth-1);
					$result[$child->nodeName][] = $URI + self::nodeAttributes($child);
				}
				return $result;
		}
	}
	
	public static function nodeXML($node) {
		if($node instanceof DOMNodeList) {
			$result = "";
			foreach($node as $child) $result .= self::nodeXML($child);
			return $result;
		}

		return $node->ownerDocument->saveXML($node);
	}
	
	/**
	 * Converts $name to _$name, creates the variable, and returns a reference
	 * to it. 
	 * 
	 * @param String $name The name of the variable to return
	 */
	public function &__get($name) {
		$_name = "_" . ltrim($name, "_");
		$this->$_name = @$this->$_name;
		
		return $this->$_name;
	}

	/**
	 * Removes a class from the list of classes 
	 * 
	 * @param String $name The name of the class to remove
	 */
	public function removeClass($name) {
		$this->classes = (array) @$this->classes;
		unset($this->classes[$name]);
	}

	/**
	 * Returns an array, regardless of the input
	 * 
	 * @param mixed $input Whatever you want to convert
	 * 
	 * @return array The array
	 */
	protected static function safeArray($input, $depth=1) {
		if(is_array($input)) {
			// Return scalar arrays untouched
			if(array_values($input) === $input) return $input;
			return $input;
		}

		if(is_string($input)) {
			$array = Jarkup::parse($input, $depth);
			$array = self::_simplify($array);
			return $array;
			foreach($array["children"] as $i=>$item) $result[] = @$item["contents"];
			return (array) @$result;
		}
	}

	/**
	 * Returns a matrix, regardless of the input
	 * 
	 * @param $input The content to matrix
	 * @param $depth When to stop performing the conversion
	 * @param $path  Only return items in the path specified
	 * 
	 * @return array
	 */
	protected static function safeMatrix($input, $depth=2, $path="/") {
		// Initialize
		$array = array(); 

		if(is_array($input)) {
			if(@$input["contents"]) $input = $input["contents"];

			else {
				return $input;
			}
		}

		if(is_string($input)) {
			$array = Jarkup::parse($input, $depth);
			$array = self::_matrix($array);
		}

		if($path) {
			foreach(array_filter(explode("/", $path)) as $branch) {
				$array = (array) @$array[$branch];
			}
		}

		return $array;
	}

	private static function _matrix($array) {
		if(@$array["children"])
		foreach($array["children"] as $child) {
			if(is_array($child)) $child = self::_matrix($child);
			$name = $child["tagName"];
			unset($child["tagName"]);
			$array[$name][] = $child;
		}

		unset($array["children"]);

		return $array;
	}

	/**
	 * Returns a string, regardless of the input
	 *
	 * @param mixed $input Whatever you want to convert
	 *
	 * @return string The content as a string
	 */
	protected static function safeString($input) {
		if($input instanceof HTML_Node) return $input->getInnerText();
		
		return @(string) $input;
	}
	
	/**
	 * Sets the property $name to $value and adds an entry to the attributes
	 * array
	 * 
	 * @param String $name The name of the property to update
	 * @param mixed $value The new value of the property
	 * 
	 * @return void
	 */
	public function __set($name, $value) {
		// Don't set numeric properties
		if(is_numeric($name)) return;

		switch($name) {
			case "contents": $name = "_$name";
		}
		
		// Set only the property if it starts with _
		if(@$name[0] == "_") $this->$name = $value;
		// Set the property and the attribute
		else $this->attr($name, $value);
	}

	/**
	 * Simplifies a node structure array into an array with contets as values
	 * 
	 * @param array $array | The array to simplify
	 *
	 * @return array       | The simplified array
	 */
	private static function _simplify($array) {
		foreach($array["children"] as $i=>$child) {
			if(@$child["children"]) {
				$array["children"][$i] = self::_simplify($child);
			}

			else {
				$array["children"][$i] = @$child["contents"];
			}
		}

		return (array) @$array["children"];
	}

	/**
	 * Allows this component to be included in a string by performing automatic
	 * conversion
	 * 
	 * @return String 
	 */
	public function __toString() {
		return "";
	}

	/**
	 * Convert a string of XML to an array where the keys are the names of
	 * the attributes and the values are the values of those attributes. 
	 *
	 * If multiple tags are found with the same name, then they are converted
	 * to an array
	 * 
	 * @Example: XML Containing Array Data
	 * <code type='xml'>
	 * 	<items>
	 * 		<item>Item A</item>
	 * 		<item>Item B</item>
	 * 	</items>
	 * </code>
	 * 
	 * <code type='php'>
	 * 	// Produces:
	 * 	array(
	 * 		"item" => array(
	 * 			"Item A",
	 * 			"Item B"
	 * 		)
	 * 	);
	 * </code>
	 *
	 * @return array The converted content
	 */
	protected static function xmlToArray($xml, $forceArray=false) {
		if(is_array($xml)) return $xml;
		return Jackal::call("Jarkup/XML2Array", $xml, $forceArray);
	}
}


