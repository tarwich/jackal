<?php

// Get the sections through which we will iterate
$sections = $this->_getSections();

echo "
	<ul class='Admin-sidebar'>";
echo "
		<li><a href='".Jackal::siteURL("Admin/section")."/ .Admin-section' \$='.Admin-content'>Overview</a></li>";

// Show each major item as a link
foreach($sections as $sectionName=>$ignore) {
	// Store the url in a variable to make the output cleaner
	$url = Jackal::siteURL("Admin/section/$sectionName");
	// Output the section item
	echo "
		<li admin-section='$sectionName'><a href='$url/ .Admin-section' \$='.Admin-content'>$sectionName<i class='test-result'></i></a></li>";
}

echo "
	</ul>";
