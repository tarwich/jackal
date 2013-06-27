<?php

/**
 * 
 * This method returns all the possible flags that can be set (found in the
 * configuration files under 'flaggers')
 * 
 */

$flaggers = Jackal::setting("flaggers");

if(Jackal::flag("ajax")) {
	echo json_encode($flaggers);
} elseif(in_array("json", $URI["segments"])) {
	echo json_encode($flaggers);
}

return $flaggers; 
