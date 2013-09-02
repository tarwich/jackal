<?php

/**
 * Return whatever is passed into $URI[0]
 * 
 * This method simply returns the first segment
 * 
 * @segments: value
 * 
 * @param mixed $value The value to return
 * 
 * @return mixed
 */

// Parse URI
// - value
($result = @$URI["value"]) || ($result = @$URI[0]) || ($result = @reset($URI));

// Return the first item in URI
return $result;
