<?php
//FIXME: Make this work with images and such

Jackal::loadHelper("url");

?>

/**
 * URL Helper
 *
 * This file should be the same as the URL library for PHP, but for JavaScript
 *
 */

(typeof(window["jackal"])=="undefined")&&(window.jackal={});
window.baseURL="<?php echo(url("/")); ?>"
	// Get rid of (leading or) trailing slashes
	.replace(/(^\/+|\/+$)/g, "");
window.suffix="<?php echo(Jackal::setting("suffix")); ?>";

// --------------------------------------------------
// url
// --------------------------------------------------
function url(path, flags) {
	var parts = [];
	
	// Add the base path if it isn't already there
	if( !String(path).match(/\w+:\/\//) ) {
		// Append the baseURL to the parts
		parts.push(window.baseURL);
		// Assign ajax as a default flag
		(flags==true) && (flags = "ajax");
		// Allow explicit removal of flags 
		(flags==false) && (flags = []);
		// Append the flags to the parts
		parts = parts.concat(flags || "ajax");
	}
	
	// Make sure path isn't undefined
	if(path) {
		// Strip the suffix from the path if it's already there
		(window.suffix) && (path = path.split(window.suffix, 2).join(""));
		// Add suffix to path if path doesn't already have a suffix
		(/\.\w+$/.test(path)) || (path = (path+"?").split("?", 2).join(window.suffix+"?"));
		// Strip trailing '?'
		path = path.replace(/\?$/, '');
		// Add the path to the parts
		parts.push(path);
	}
	
	return parts.join("/");
}
