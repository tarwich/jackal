<?php

/**
 * Returns array of modifiers for the object.
 * 
 * Takes an object or a modifier <code>long</code> and returns an associative
 * array of modifiers where the keys are the modifier name and the values are 
 * either 1 or 0 stating if the flag is set or not.
 * 
 * @param mixed $URI[0] Either a ReflectionClass, a ReflectionProperty, a 
 * 		ReflectionMethod, or the long int returned from reflection. 
 * 
 * @return array
 */

if(is_object($URI)) $number = $URI->getModifiers();
elseif(is_int($URI)) $number = $URI;
elseif(is_object($URI[0])) $number = $URI[0]->getModifiers();
elseif(is_int($URI)) $number = $URI[0];

$flags = array(
	"static"    => $number & 1, 
	"abstract"  => $number & 2, 
	"final"     => $number & 4, 
//	$number & 8, 
//	
//	$number & 16, 
//	$number & 32, 
//	$number & 64, 
//	$number & 128, 
	
	"public"    => $number & 256, 
	"protected" => $number & 512, 
	"private"   => $number & 1024, 
	
//	$number & 2048, 
//	$number & 4096, 
//	$number & 8192, 
//	$number & 16384, 
);

$flags = $flags;

return $flags;
