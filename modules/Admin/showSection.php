<?php

// The section we're supposed to look up
($targetSection = @$URI[0]) || ($targetSection = @$URI["section"]);
// Wrapper for the admin section
echo "
	<span class='Admin-section'>";
// If section is Null, then call overview
if(!$targetSection) Jackal::call("Admin/overview");

else {
	// Initialize a sections array to hold all the sections that we find
	$sections = $this->_getSections();
	// Setup the url to save the for to
	$action = Jackal::siteURL("Admin/save/" . urlencode($targetSection));
    // Create an interface for all the settings and messages
    echo "
		<form \$='.Admin-section' method='post' action='$action'>
		<h1>$targetSection</h1>";

	// Go through the subsections of the target section
	foreach((array) @$sections[$targetSection] as $subsectionName=>$subsection) {
        // Show the subsection's title and start the region for messages
	    echo "
			<h2>$subsectionName</h2>
            <span class='message-area'>
                <ul class='messages'>";

        // Run the test for this section and get the test's errors / warnings
        $results = $this->runTests(array($targetSection, $subsectionName));

        // Go through all of the messages
        foreach($results as $level => $messages) {
            // Go through all of the messages in the respective error levels
            foreach((array) $messages as $message) {
                // Lowercase the level so that we can use it as the class of the li
                $class = strtolower($level);
                // Display each message as a list item
                echo "<li class='$class'>$level: $message</li>";
            }
        }

        // End the message region
        echo "
                </ul>
            </span>";
		
		// Go through all the editors that are supposed to show up in this subsection
	    foreach((array) $subsection as $editor)
            // Run the callback to load the settings for this subsection
	        Jackal::call($editor["callback"]);
	}

    // Show the save button and close the form
    echo "
			<button type='submit'>Save</button>
		</form>";
}

// End the admin-section area
echo "
	</span>";
