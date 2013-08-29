<?php

/**
 * Download a config file
 * 
 * The purpose of this method is to be able to link to admin configs. This method will look in the session under 
 * admin/file for the requested content and send it as an attachment. 
 * 
 * Currently this method will only download the content as filename=admin_.yaml, because that's what it was designed
 * to do. However, in the future we will probably allow setting the filename.
 * 
 * @return void
 */

// Disable the template, because we're downloading a file
Jackal::call("Template/disable");

// Set the content type to make the browser download the file instead of displaying it
header("Content-type: application/octet-stream");
// Set the filename to admin_.yaml
header('Content-Disposition: attachment; filename="admin_.yaml"');
// TODO: Consider deleting the content when done
// Get the file content from the session
$file = Jackal::call("Session/read/admin/file");
// Send the content to the browser
echo $file;
