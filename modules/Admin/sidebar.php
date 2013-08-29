<?php

/**
 * Shows the "sidebar" view
 * 
 * The sidebar is a list of links to the various admin sections. When clicked a link will take you to the section on 
 * which you clicked.
 * 
 * To get a page into the sidebar, put in the module's config the following
 * 
 * <code type='yaml'>
 * 	admin:
 * 		modules:
 * 			your-module:
 * 				- 
 * 					name    : Your Section / Your Subsection
 * 					callback: your-module/adminPageMethod
 * 				-   # You can insert subsections into other admin pages like this
 * 					name    : Other Section / Your Subsection
 * 					callback: your-module/adminPageMethod
 * </code>
 * 
 * @return void
 */

// Get the sections through which we will iterate
$sections = $this->_getSections();

echo "
	<ul class='Admin-sidebar'>";
echo "
		<li><a href='".Jackal::siteURL("Admin/showSection")."/ .Admin-section' \$='.Admin-content'>Overview</a></li>";

// Show each major item as a link
foreach($sections as $sectionName=>$ignore) {
	// Store the url in a variable to make the output cleaner
	$url = Jackal::siteURL("Admin/showSection/" . urlencode($sectionName));
	// Output the section item
	echo "
		<li admin-section='$sectionName'><a href='$url/ .Admin-section' \$='.Admin-content'>$sectionName<i class='test-result'></i></a></li>";
}

echo "
	</ul>";
