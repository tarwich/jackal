<?php

/**
 * Wraps content with layout styles
 * 
 * segments: $html
 * 
 * @example Create two columns of content
 * <code type='php'>
 * Jackal::call("Layout/wrapper", array(
 * 	"html"	=> "Your wrapped content"
 * ));
 * </code>
 * 
 * @param String $html Contents to be placed within the wrapper
 * @param String $width CSS width of the wrapper, default type is percent. If pixels are desired, then use "px"
 * @param String $border CSS border styles for the wrapper
 * @param String $padding CSS padding styles for the wrapper
 * @param String $background CSS background styles for the wrapper
 * 
 * @return void
 */

return print(Jackal::make("Layout:Wrapper", $URI));