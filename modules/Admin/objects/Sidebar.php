<?php

class Admin__Sidebar {
    function __toString() {
        // Prepare the result to return
        $html = "";
		// Prepare the sections array
        $sections = array();
        
		// Go through all the modules in the config
        foreach((array) Jackal::setting("admin/modules") as $module) {
            // Go through all the sections in this module
            foreach((array) @$module as $section) {
                // Break apart the name of this section by '/'
                list($a, $b) = explode('/', (string) @$section["name"]);
                // Add this section to the results
                $sections[$a][$b][] = $section;
            }
        }

        $html .= "<ul class='Admin-Sidebar'>";
        
        foreach($sections as $sectionName=>$section) {
            $html .= "<li><b>$sectionName</b></li>";
        }
        
        $html .= "</ul>";
        
        return $html;
    }
}
