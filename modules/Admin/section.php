<?php

// The section we're supposed to look up
($targetSection = @$URI[0]) || ($targetSection = @$URI["section"]);

// Find all the modules that show up in this section and show them by calling their callback

// Initialize a sections array to hold all the sections that we find
$sections = array();

// Go through all the modules and map them to sections
foreach((array) Jackal::setting("admin/modules") as $module) {
    // Go through all the sections related to this module
    foreach($module as $section) {
        // Break apart the name into section and subsection
        list($a, $b) = explode("/", (string) @$section["name"]);
    }
    
	// Add this section to the sections map
    $sections[trim($a)][trim($b)][] = $section;
}

//print_r(compact("sections", "targetSection"));

// Go through the subsections of the target section
foreach((array) @$sections[$targetSection] as $subsectionName=>$subsection) {
    echo "<b>$subsectionName</b>";
    echo "<p>";
    
	// Go through all the things that are supposed to show up in this subsection
    foreach((array) $subsection as $thingy) {
    }
    
    echo "</p>";
}

