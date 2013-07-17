<?php

/**
 * Add links to other parts of documentation and return the new text.
 * 
 * This method adds Module Reference links to any existing modules, and it 
 * attempts to find links to methods and properties and add those links as
 * well.
 * 
 * These are the things that will be changed into links:
 * 
 * <b>Modules / Libraries</b>: If a word has the same spelling and 
 * 		capitalization as an existing item in the system, then it will be 
 * 		converted into a link to that item. If the word is followed by a 
 * 		::method(), then a link will be added for a method with that name (where
 * 		'method' is the actual name of the target method).
 * 
 * <b>Local Methods</b>: If a word is followed by parentheses and a local method
 * 		exists with that name, then the word will be converted into a link.
 * 
 * @return string
 */

if(is_string($URI)) $text = $URI;
else $text = $URI[0];

@($classes = $this->_classes) || ($classes = $this->_classes = array_keys($this->_getModuleList()));
@($link = $this->_moduleReferenceLink) 
|| ($link = $this->_moduleReferenceLink = explode("___", Jackal::siteURL("Documentation/rightPane/___")));
@($localMethods = $this->localMethods);
@($localProperties = $this->localProperties);

if(!$localMethods) {
	if(@$this->documentationClass) {
		$methods = (array) $this->documentationClass->getMethods();
		
		foreach($methods as $i=>$method) $methods[$i] = $method->name;
		$localMethods = $this->localMethods = $methods;
	} else {
		$localMethods = array();
	}
}

if(!$localProperties) {
	if(@$this->documentationClass) {
		$properties = (array) $this->documentationClass->getProperties();
		
		foreach($properties as $i=>$property) $properties[$i] = $property->name;
		$localProperties = $this->localProperties = $properties;
	} else {
		$localProperties = array();
	}
}

// Find class methods
$text = preg_replace("\x07\\b(".implode("|", $classes).")::(\\w+)([()]*)\x07", "{METHOD:$1,$2}", $text);
// Find classes
$text = preg_replace("\x07(?<!{METHOD:)\\b(".implode("|", $classes).")\\b\x07", "{CLASS:$1}", $text);
// Link to class methods
$text = preg_replace("\x07{METHOD:(.*?),(.*?)}\x07", "<a href='$link[0]$1$link[1]#$2-method'>$1::$2()</a>", $text);
// Link to classes
$text = preg_replace("\x07{CLASS:(.*?)}\x07", "<a href='$link[0]$1$link[1]'>$1</a>", $text);
// Link to local methods
$text = preg_replace('/\b('.implode("|", $localMethods).')\(\)/', "<a href='#$1-method'>$1()</a>", $text);
// Link to local properties
$text = preg_replace('/\\$('.implode("|", $localProperties).')/', "<a href='#$1-property'>$$1</a>", $text);

return $text;
