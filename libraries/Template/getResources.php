<?php

/**
 * Find all the resources [of type] in the section specified and return them
 * 
 * This method will look through all the resources collected through 
 * addResource() and return the list. The list will be deduped prior to 
 * returning it. 
 * 
 * The purpose of this system is to support templates by including all 
 * resources in the <head> section. As such, the only valid value for 
 * $section is currently HEAD.
 * 
 * Segments: section / type
 * 
 * @param string $URI[section] 	The section to look for resources in. Currently
 * 								the only valid value is HEAD. 
 * @param string $URI[type]		The type of resource to find such as css, or js. 
 * 								This should be lowercase.
 * 
 * @return array An array of the resources found
 */

@( ($section = $URI["section"]) || ($section = $URI[0]) );
@( ($type = $URI["type"]) || ($type = $URI[1]) );

// Dedupe
foreach((array) @$this->resources as $sectionName=>$typeArray) {
	foreach($typeArray as $typeName=>$itemArray) {
		$this->resources[$sectionName][$typeName] = array_unique($itemArray);
	}
}

if($type) {
	return @$this->resources[$section][$type];
} else {
	return @$this->resources[$section];
}
