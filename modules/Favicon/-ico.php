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

$glob = "<ROOT>/{,../}favicon.ico";
list($file) = Jackal::files($glob);

Jackal::call("Template/disable-compression");
Jackal::call("Template/disable");
header("Content-type: image/vnd.microsoft.icon");

readfile($file);
exit();
