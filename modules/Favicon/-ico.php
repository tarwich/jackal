<?php

/**
 * Handles the request from the browser for favicon.ico
 * 
 * Due to the way that requests are handled by Jackal, when a request comes
 * in for favicon.ico it will be sent to this method. In order to customize
 * the favicon for your site, simply put a favicon.ico file in the <ROOT> 
 * folder.
 * 
 * @return void
 */

// Create a glob to search for our favicon
$glob = "<ROOT>/{,../}favicon.ico";
// Try to find the path to the file
$results = Jackal::files($glob);

// If we found the favicon
if(!empty($results)) {
    // Extract the filename from the returned array
    list($file) = $results;

    Jackal::call("Template/disable-compression");
    Jackal::call("Template/disable");
    header("Content-type: image/vnd.microsoft.icon");

    // Load the file
    readfile($file);
}

exit();
