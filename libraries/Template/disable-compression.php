<?php

/**
 * Disables output compression. Useful for generating images and such
 * 
 * Template ships with the ability to enable output compression for your 
 * template. This compresses the HTML or CSS or JS output before it is sent
 * to the browser in order to reduce the page size. However, this can break
 * things like image generators, so call this method to disable the output
 * compression.
 * 
 * @return void
 */
if(!headers_sent()) {
	ini_set("zlib.output_compression", 0);	
	header("Content-encoding:"); 
}
