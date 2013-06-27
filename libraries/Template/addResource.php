<?php

/**
* Add a resource to the list
*
* This is used for templates. However, this functionality does not belong
* in Jackal and will be moved to another module in the future.
*
* Segments: section / type / resource / top
* 
* @param string $URI[section] 	The name of the section that this resource 
* 								belongs in. Presently the only valid value 
* 								for this parameter is "HEAD"
* 
* @param string $URI[type] 		The type of resource that this is. For example, 
* 								"js" or "css". This
* 
* @param string $URI[resource] 	The name / path of the resource to add
* 
* @param boolean $URI[top] 		True if this resource should be added above all 
* 								other resources. If two calls to addResource are 
* 								made and $top is true in both instances, then 
* 								the second will be above the first.
* 
* @return void
*/

@( ($section = $URI["section"]) || ($section = $URI[0]) );
@( ($type = $URI["type"]) || ($type = $URI[1]) );
@( ($resource = $URI["resource"]) || ($resource = $URI[2]) );
@( ($top = $URI["top"]) || ($top = $URI[3]) );

// Find out who the caller is
($origin = @implode("/", Jackal::scope(-2))) || ($origin = "Jackal::_start()");

$file = Jackal::siteURL($resource);

// Add to top of resources
if($top) {
	@array_unshift($this->resources[$section][$type], $file);
	
	$this->exResources[$section][$type] = array_merge(
		array($file => array("origin" => $origin, "file" => $file, "top" => true)),
		(array) @$this->exResources[$section][$type]
	);
}

// Add to bottom of resources
else {
	$this->resources[$section][$type][] = $file;
	$this->exResources[$section][$type][$file] = array("origin" => $origin, "file" => $file, "top" => false);
}
