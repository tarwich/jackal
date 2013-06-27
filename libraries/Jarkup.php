<?php 

class Jarkup {
	private $_ladder = array();
	private $_selfClosingTags = array(
		"br", "img"
	);
	private $_shrinkWrappedTags = array(
		
	);
	
	public function __construct() {
	}
	
	public function __shutdown() {
		
		Jackal::loadHelper("lambda"); // For create_function2

		// For certain content types we do not perform output translation, see if any of those are present
		foreach(headers_list() as $header) {
			@list($tag, $value) = explode(":", $header, 2);
			
			if(trim(strtolower($tag)) == "content-type") {
				// If we're not supposed to perform translation, then quit
				if(!preg_match('_(html|^\s*$)_i', $value)) return;
			}
		}
		
		// Get the current contents of the output buffer
		$html = ob_get_contents();
		
		// Erase the output buffer, but do not stop buffering
		@ob_clean();
		
		$this->parseHTML(array("html"=>$html));
	}

	public function parseHTML($URI) {
		
		$html = $URI["html"];
		
		// Loop condom
		$condom = 0;

		//  __________________________________________________
		// / Test 5 - Regex                                   \
		
		/*
		*/
		while(preg_match_all('/<([\w-]+):([\w-]+)((?:\s+|[\w-]+\s*=\s*\'.*?\'|[\w-]+\s*=\s*".*?")*)(\/?)>/', $html, $matches, PREG_OFFSET_CAPTURE|PREG_SET_ORDER)) {
			list(list($match, $offset), list($namespace), list($component), list($attributes), list($selfClosing)) = end($matches);

			// Parse the attributes
			preg_match_all('/([\w-]+)\s*=\s*([\'"])(.*?)\2/s', $attributes, $matches);
			$URI = @array_combine($matches[1], $matches[3]);

			// Self closing tags have different offsets
			if($selfClosing) {
				$newMarkup = (string) Jackal::make("$namespace:$component", $URI);
				$html = substr_replace($html, $newMarkup, $offset, strlen($match));
			}

			// Find the end tag
			else {
				preg_match_all("_</$namespace:$component>_i", $html, $matches, PREG_OFFSET_CAPTURE);
				$matches = $matches[0];
				list($endTag, $endOffset) = end($matches);
				$endOffset += strlen($endTag);

				$inside = substr($html, $offset, $endOffset - $offset);

				$parameters = $this->parse($inside, 1);


				$test = $parameters + ((array)$URI);

				// Incorporate children as attributes
				foreach((array) @$parameters["children"] as $child) {
					// If the tag has no attributes, then add it as a property
					if(array_diff_key($child, array("tagName" => 1, "contents" => 1)) === array()) {
						$URI[$child["tagName"]][] = $child["contents"];
					}

					else {
						$URI[$child["tagName"]][] = $child["contents"];
					}
				}

				// Flatten singles
				foreach((array) @$parameters["children"] as $child) {
					if(!array_diff_key($child, array("tagName" => 1, "contents" => 1))) 
					if(count($URI[$child["tagName"]]) == 1) $URI[$child["tagName"]] = $URI[$child["tagName"]][0];
				}

				// Add contents as parameter
				$URI["contents"] = substr($html, $offset + strlen($match), $endOffset - $offset - strlen($match) - strlen($endTag));
				
				//echo "<pre>".htmlentities(print_r($URI, 1))."</pre>";
				$newMarkup = (string) Jackal::make("$namespace:$component", $URI);
				$html = substr_replace($html, $newMarkup, $offset, $endOffset-$offset);
			}
		}

		echo $html;

		return;

		// \__________________________________________________/
		

		//  __________________________________________________
		// / Test 4 - Ganon                                   \
		
		Jackal::loadLibrary("Ganon");
		
		$dom = str_get_dom($html);
		$condom = 0;

		while(true) {
			$all = $dom->select("*:element");

			for($i=count($all)-1; $i>=0; --$i) {
				$element = $all[$i];

				if(preg_match('/(\w+):([^\s>]+)/', $element->tag, $matches)) {
					list($discard, $namespace, $component) = $matches;
					
					$URI = (array) $element->attributes;
					$URI["node"] = $element;

					$newMarkup = (string) Jackal::make("$namespace:$component", $URI);
					$newElement = @str_get_dom($newMarkup);
					$element->clear();
					$newElement->changeParent($element);
					$element->detach(true);
					break;
				}
			}
			
			// If $i ever gets to zero, then we've finished
			if(!$i) break;

			if(++$condom == 100) {
				break;
			}
		}

		$html = $dom->getInnerText();
		$html = preg_replace('_</?~root~>_', '', $html);

//		echo "<pre>".htmlentities(print_r($html, 1))."</pre>";
		echo $html;

		return;
		/*
		*/

		// \__________________________________________________/
		
		//  __________________________________________________
		// / Test 3 - XMLReader                               \

		/*
		$reader = new XMLReader();
		$document = $reader->xml("<root>".preg_replace('/<(\/?\w+):/', '<\1.', $html)."</root>");
		
		while($reader->read()) {
		}
		*/

		// \__________________________________________________/


		//  __________________________________________________
		// / Test 2 - SimpleXML                               \
		
		//echo $this->process($html);

		/*
		$simpleParse = create_function2('function($a) {return new SimpleXMLElement("<root>".preg_replace(\'/<(\/?\w+):/\', \'<\1.\', $a)."</root>");}');
		$document = $simpleParse($html);

		$nodes = $document->xpath("//*[contains(name(), '.')]");

		foreach($nodes as $node) {
			$node->addChild("foo", "B<b>a</b>r");
		}

		echo "<pre>".htmlentities(print_r($document->asXML(), 1))."</pre>";
		*/

		// \__________________________________________________/




		//  __________________________________________________
		// / Test 1 - DomDocument                             \
		
		$document = $this->XML2DOM($html);
		$all = $document->getElementsByTagName("*");
		$condom = 0;

		while(true) {
			for($i=$all->length-1; $i>=0; --$i) {
				$element = $all->item($i);

				if(preg_match('/^(\w+)__(.*)/', $element->nodeName, $matches)) {
					list($discard, $namespace, $component) = $matches;
					
					$URI = array();
					if($element->attributes) foreach($element->attributes as $name=>$value) $URI[$name] = $value->textContent;
					$URI["node"] = $element;
					
					$newMarkup = (string) Jackal::make("$namespace:$component", $URI);

					/*
					$document2 = $this->XML2DOM($newMarkup);
					echo "<pre>".htmlentities(print_r($newMarkup, 1))."</pre>";

					foreach($document2->childNodes as $child) {
						$child = $document->importNode($child, true);
						$element->parentNode->insertBefore($child, $element);
					}

					$element->parentNode->removeChild($element);
					*/

					$fragment = $document->createDocumentFragment();
					$level = error_reporting(0);
					$fragment->appendXML($newMarkup);
					error_reporting($level);
					$element->parentNode->replaceChild($fragment, $element);
					break;
				}
			}

			if($i==0) break;
			if(++$condom > 100) break;
			break;
		}

		$html = $this->nodeXML($document->childNodes->item(0)->childNodes);
		echo $html;

		return;

		/*
		$document = $this->_parseXML(preg_replace('/<(\/?\w+):/', '<\1.', $html));
		$elements = $document->getElementsByTagName("*");

		for($i=$elements->length-1; $i>=0; --$i) {
			$element = $elements->item($i);

			if(preg_match('/\w+\.\w+/', $element->nodeName, $matches)) {
				$URI = array();
				list($namespace, $component) = explode(".", $matches[0]);
				
				if($element->attributes)
				foreach($element->attributes as $name=>$attribute) $URI[$name] = $attribute->value;
				
				$content = $this->_parseArray($element);
				if(is_array($content)) $URI += $content;
				else $URI[0] = $content;

				$newMarkup = (string) Jackal::make("$namespace:$component", $URI);
				$newNode = $this->_parseXML(preg_replace('/<(\/?\w+):/', '<\1.', $newMarkup))->childNodes->item(0);
				$newNode = $document->importNode($newNode, true);

				foreach($newNode->childNodes as $child) {
					$parent = $element->parentNode;
					$parent->insertBefore($child, $element);
				}
			}
		}

		echo $document->saveXML();
		*/

		// \__________________________________________________/

		



		while(preg_match('_<(\w+):([\w-]+)([^>]*)(?:>((?:(?!<\w+:).)*?)</\1:\2>|/>)_si', $html, $matches, PREG_OFFSET_CAPTURE)) {
			list($match, $offset) = $matches[0];
			list($module)         = $matches[1];
			list($action)         = $matches[2];
			list($attributes)     = $matches[3];
			@list($content)       = $matches[4];

			// Parse the attributes
			if(preg_match_all('_(\w+)\s*=\s*(["\'])(.*?)\2_', $attributes, $matches)) 
				$attributes = array_combine($matches[1], $matches[3]);
			
			// Remove leading and trailing whitespace from content
			$content = trim($content, " \r\n\t");

			// If the first non-whitespace character of $content is "<", then 
			// $content is composed of tagged parameters, otherwise it's raw
			// data and should be passed into 0 => $content
			if(@$content[0] === "<") {
				// Create a new DomDocument
				$document = new DomDocument();
				// Fix checked/selected items
				$content = preg_replace('/
						(<\w.*)                             (?# Tagname followed by anything)
						(checked|selected)                  (?# The flags to convert to attributes)
						(\s*(?:>|\/>|[\w-]+\s*=\s*["\'].*)) (?# Make sure the flags are not values of attributes)
						/x', '$1$2="true"$3', $content);
				// Wrap $content so there is only one root node, then tell the 
				// DomDocument to parse it
				$document->loadXML("<root>$content</root>");
				// Get the root node that we used to wrap the content
				$rootNode = $document->getElementsByTagName("root")->item(0);

				$attributes = array_merge((array) $attributes, $this->_parseArray($rootNode));

				// Loop through all the root nodes and convert to parameters
				if(false)
				foreach($rootNode->childNodes as $node) {
					// See if this node is an array
					$array = $this->_parseArray($node);
					// If it was an array, then set the attribute
					if($array) $attributes[$node->nodeName] = $array;
					
					// If it wasn't an array, then add the contents of the node
					else {
						// Get the html of this node
						$innerHTML = $document->saveXML($node);
						// Strip the outer tags
						$innerHTML = preg_replace('/^\s*<(\w+)(?:".*?"|[^>]*?)*>(.*)<\/\1>/s', '$2', $innerHTML);
						// Add this attribute to the list
						$attributes[$node->nodeName] = $innerHTML;
					}
				}
			}

			// Prepare the URI based on wheter or not content was there (which would become URI[0])
			if($content) $URI = array_merge((array) $content, (array) $attributes);
			else $URI = $attributes;

			$replacement = Jackal::make("$module/$action", $URI);

			$html = substr_replace($html, $replacement, $offset, strlen($match));

			// Loop condom
			if(++$condom > 1000) {
				Jackal::error(500, "Jarkup condom busted");
				break;
			}
		}
		
		echo $html;
	}

	public function XML2Array($URI) {
		return call_user_func_array(array($this, "_parseArray"), $URI);
		return $this->_parseArray($URI[0]);
	}

	public function XML2Matrix($URI) {
		$rows = (array) @reset($this->_parseArray($URI[0], true));
		
		foreach($rows as $i=>$row) {
			if(is_string($row)) $row = array("contents" => $row);
			$contents = $this->_parseArray($row["contents"], true);
			unset($row["contents"]);

			foreach($contents as $j=>$jj)
			foreach($jj as $k=>$td) {
				if(is_string($td)) $td = array("contents" => $td);
				$contents[$j][$k] = $td;
			}

			$rows[$i] = array_merge($row, $contents);
		}

		return $rows;
	}

	public static function parse($xml, $depth=0) {
		preg_match_all('_<(/?[\w-]+\b)((?:\s*|[\w-]+\s*=\s*\'.*?\'|[\w-]+\s*=\s*".*?"|selected|checked)*)(\s*/?)>_', $xml, $matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
		$branch = array("tagName" => "root", "depth" => 0);
		$stack = array();

		//echo "<pre>".htmlentities(print_r($matches, 1))."</pre>";
		$oMatches = $matches;

		/*
		echo "<pre>".htmlentities(print_r($xml, 1))."</pre>";
		echo "<pre>".htmlentities(print_r($matches, 1))."</pre>";
		*/
		while($match = array_shift($matches)) {
			list(list($match, $offset), list($tagName), list($attributes), list($selfClosing)) = $match;
			preg_match_all('/(?:([\w-]+)\s*=\s*([\'"]?)(.*?)\2|(checked|selected))/', $attributes, $attributes);
			$attributes = @array_filter( @array_merge(
				(array) @array_combine(array_filter($attributes[4]), array_filter($attributes[4])),
				(array) @array_combine( $attributes[1], $attributes[3])
			));
			$attributes = array_filter($attributes);
			$match = compact("match", "offset", "tagName", "selfClosing", "attributes", PREG_SET_ORDER);
			
			//
			// End tag
			//
			if($match["tagName"][0] == "/") {
				//echo "($match[tagName] =  /$branch[tagName]) ".($match["tagName"] !=  "/$branch[tagName]")."<br>";
				if($match["tagName"] !=  "/$branch[tagName]") {
					//echo "<pre>".htmlentities(print_r(array($match, $branch, $oMatches), 1))."</pre>";
					exit();
				}
				
				// Process the contents
				$branch["contents"] = @substr(
					$xml, 
					$branch["offset"] + strlen($branch["match"]), 
					$match["offset"] - ($branch["offset"] + strlen($branch["match"]))
				);
				
				// Collapse children that are too deep
				if($depth) {
					if($branch["depth"] == $depth) {
						unset($branch["children"]);
					}
				}
				
				// Save the attributes
				// Set attributes directly
				$branch = array_merge(
					array_intersect_key($branch, array("children"=>1, "contents"=>1, "tagName"=>1)),
					(array) @$branch["attributes"]
				);
				
				// Add this guy to the children
				$stack[count($stack)-1]["children"][] = $branch;
				// Move to the parent branch
				$branch = array_pop($stack);
			}
			
			//
			// Open tag
			//
			else {
				$stack[] = $branch;
				$match["depth"] = ((int)@$branch["depth"]) + 1;
				$branch = $match;
			}

			//
			// Self closing tag
			//
			if(@$match["selfClosing"]) {
				//echo "($match[tagName] = $branch[tagName]) ".($match["tagName"] !=  "$branch[tagName]")."<br>";
				// Save the attributes
				// Set attributes directly
				$branch = array_merge(
					array_intersect_key($branch, array("children"=>1, "contents"=>1, "tagName"=>1)),
					(array) @$branch["attributes"]
				);
				// Add this guy to the children
				$stack[count($stack)-1]["children"][] = $branch;
				// Move to the parent branch
				$branch = array_pop($stack);
			}
		}

		//echo "<pre>".htmlentities(print_r($branch, 1))."</pre>";
		return (array) @$branch;
	}

	private function _parseArray($node, $forceArray = false) {
		// Convert string to node                           
		if(is_string($node)) $node = @str_get_dom($node);
		// Initialize the output array
		$array = array();

		foreach($node->children as $childNode) {
			// Localize nodeName for speed
			$nodeName = $childNode->tag;

			// Make sure text nodes are empty
			if($nodeName == "#text") {
				if(trim($childNode->textContent)) {
					// This text node isn't empty, so we can't make an array, so return the raw XML
					return $this->_innerXML($node);
				}
			}
			
			else {
				if($childNode->attributes) {
					$item = array();
					foreach($childNode->attributes as $name=>$attribute) $item[$name] = $attribute->value;
					// Store the contents in a sub-array with the $nodeName as the key of the subarray
					$item["contents"] = $childNode->innerHTML;
					$array[$nodeName][] = array_filter($item);
				}

				else {
					// Store the contents in a sub-array with the $nodeName as the key of the subarray
					$array[$nodeName][] = $this->_innerXML($childNode);
				}
			}
		}

		// Flatten sub-arrays with only one child
		if(!$forceArray)
		foreach($array as $key=>$value) {
			if(count($array[$key]) == 1) $array[$key] = $array[$key][0];
		}
		
		// At this point all the childNodes were ok, so parse the attributes
		if($node->attributes) 
		foreach($node->attributes as $name=>$value) {
			$array[$name] = $value;
		}

		return $array;
	}

	private function _parseArray_old($node, $forceArray = false) {
		// Convert string to node                           
		if(is_string($node)) $node = $this->_makeNode($node);
		// Initialize the output array
		$array = array();
		
		if($node->childNodes)
		foreach($node->childNodes as $childNode) {
			// Localize nodeName for speed
			$nodeName = $childNode->nodeName;

			// Make sure text nodes are empty
			if($nodeName == "#text") {
				if(trim($childNode->textContent)) {
					// This text node isn't empty, so we can't make an array, so return the raw XML
					return $this->_innerXML($node);
				}
			}
			
			else {
				if($childNode->attributes) 
				if($childNode->attributes->length) {
					$item = array();
					foreach($childNode->attributes as $name=>$attribute) $item[$name] = $attribute->value;
					// Store the contents in a sub-array with the $nodeName as the key of the subarray
					$item["contents"] = $this->_innerXML($childNode);
					$array[$nodeName][] = array_filter($item);
				}

				else {
					// Store the contents in a sub-array with the $nodeName as the key of the subarray
					$array[$nodeName][] = $this->_innerXML($childNode);
				}
			}
		}

		// Flatten sub-arrays with only one child
		if(!$forceArray)
		foreach($array as $key=>$value) {
			if(count($array[$key]) == 1) $array[$key] = $array[$key][0];
		}
		
		// At this point all the childNodes were ok, so parse the attributes
		if($node->attributes) 
		foreach($node->attributes as $name=>$value) {
			$array[$name] = $value;
		}

		return $array;
	}

	private function _parseXML($xml, $intoNode=NULL) {
		$document = new DomDocument();
		$document->recover = true;
		$errorReporting = error_reporting(0);
		$document->loadXML("<root>$xml</root>");
		error_reporting($errorReporting);

		if($intoNode) {
			$intoNode->appendChild(
				$intoNode->ownerDocument->importNode($document->childNodes->item(0), true)
				);
		}

		return $document;
	}

	private function _unwrap($xml) {
		return preg_replace('/^\s*<(\w+)(?:".*?"|[^>]*?)*>(.*)<\/\1>/s', '$2', $xml);
	}

	private function _innerXML($node) {
		// Get the html of this node
		// Strip the outer tags
		return preg_replace('/^\s*<(\w+)(?:".*?"|[^>]*?)*>(.*)<\/\1>/s', '$2', $node->ownerDocument->saveXML($node));
	}

	private function _makeNode($xml) {
		$document = new DomDocument();
		$document->loadXML("<root>$xml</root>");
		return $document
			->getElementsByTagName("root")
			->item(0);
	}

	private function _nodeToArray($node) {
		// Make sure node is a node
		if(is_string($node)) $node = _makeNode($node);
		// Initialize the output array
		$array = array();
	}

	private function process($xml, $forceArray = false) {
		$simpleParse = create_function2('function($a) {return new SimpleXMLElement("<root>".preg_replace(\'/<(\/?\w+):/\', \'<\1.\', $a)."</root>");}');
		$document = $simpleParse($xml);

		$nodes = $document->xpath("//*[contains(name(), '.')]");

		for($i=count($nodes)-1; $i>=0; --$i) {
			$node = $nodes[0];
			list($namespace, $component) = explode(".", $node->getName());
			
			$URI = array();
			// Suck attributes into $URI
			foreach($node->attributes() as $name=>$value) $URI[$name] = (string) $value;
			// Get children as attributes
			foreach($node->children() as $name=>$value) $URI[$name][] = $value->children()->asXML();

			// Flatten single children
			if(!$forceArray) 
			foreach($URI as $name=>$value) if(is_array($value)) if(count($value) == 1) $URI[$name] = $value[0];

			$newMarkup = (string) Jackal::make("$namespace:$component", $URI);
			$newNode = $simpleParse($newMarkup);

			$parent = @reset($node->xpath("parent::*"));

			foreach($node->xpath("following-sibling::*") as $next) unset($parent->{$next->getName()});

			break;
		}
		
		echo "DONE";
		echo "<pre>".htmlentities(print_r($document, 1))."</pre>";

		return $document->asXML();
	}

	private function processDOM($xml, $forceArray = false) {
		$document = $this->XML2DOM($xml);

		echo "<pre>".htmlentities(print_r($document->saveXML(), 1))."</pre>";
	}

	private function XML2DOM($xml) {
		$document = new DomDocument();
		$document->strictErrorChecking = false;
		$document->recover = true;
		$xml_ = preg_replace('_<(/?\w+):_', '<\1__', $xml);

		// Temporarily disable errors
		$level = error_reporting(0);
		@$document->loadXML("<root>$xml_</root>");
		// Set error reporting back to what it was
		error_reporting($level);

		return $document;
	}

	private static function nodeXML($node) {
		if($node instanceof DOMNodeList) {
			$result = "";
			foreach($node as $child) $result .= self::nodeXML($child);
			return $result;
		}

		return $node->ownerDocument->saveXML($node);
	}
} 

