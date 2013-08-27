<?php

// The section we're supposed to look up
($targetSection = @$URI[0]) || ($targetSection = @$URI["section"]);

echo "
	<span class='Admin-section'>";

// If section is Null, then call overview
if(!$targetSection) Jackal::call("Admin/overview");

else {
	// Initialize a sections array to hold all the sections that we find
	$sections = array();
	// Setup the url to save the for to
	$action = Jackal::siteURL("Admin/save/$targetSection");
	
    echo "
		<form \$='.Admin-section' method='post' action='$action'>";

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

	// Go through the subsections of the target section
	foreach((array) @$sections[$targetSection] as $subsectionName=>$subsection) {
	    echo "
			<h2>$subsectionName</h2>";
		
		// Go through all the editors that are supposed to show up in this subsection
	    foreach((array) $subsection as $editor) {
	        Jackal::call($editor["callback"]);
	    }
	}	
	
    echo "
			<button type='submit'>Save</button>
		</form>";
}

echo "
	</span>";
