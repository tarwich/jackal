<?php

/**
 * The main index page for the admin
 * 
 * The purpose of this page is to show the admin.
 * 
 * @return void
 */

// Get the modules that admin will show
$modules = $this->getSections();
// Load jQuery
js("resources/jquery-1.10.2.min.js");
// Load the admin.js
js("admin.js");

echo "
	<Admin:Sidebar />
	<Admin:Content />";
