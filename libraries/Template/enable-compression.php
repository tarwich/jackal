<?php

return;

/**
 * Re-enables output compression after it has been disabled
 * 
 * By default output compression is turned off. It should be turned on with 
 * setting 'template/gzip'. Calling this method will turn on output compression
 * as well, it's just not optimal to use this method. 
 * 
 * Config setting: 
 * <code type="yaml">
 * template:
 * 		gzip: -1 # int (0-9) level of compression or true for default (6)
 * </code>
 * 
 * @return void
 */
$gzip = Jackal::setting("gzip");
(is_int($gzip)) || ($gzip = 6);
@ini_set("zlib.output_compression", "On");	
@ini_set("zlib.output_compression_level", $gzip);	
@header('Content-Encoding: ' . zlib_get_coding_type());
