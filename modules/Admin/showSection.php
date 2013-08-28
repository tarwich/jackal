<?php

// The section we're supposed to look up
($targetSection = @$URI[0]) || ($targetSection = @$URI["section"]);

echo "
	<span class='Admin-section'>";

// If section is Null, then call overview
if(!$targetSection) Jackal::call("Admin/overview");

else {
	// Initialize a sections array to hold all the sections that we find
	$sections = $this->_getSections();
	// Setup the url to save the for to
	$action = Jackal::siteURL("Admin/save/$targetSection");
    // Run the test for this section and get the test's errors / warnings
    $results = $this->runTests(array($targetSection));

    echo "
		<form \$='.Admin-section' method='post' action='$action'>
		<h1>$targetSection</h1>";

	// Go through the subsections of the target section
	foreach((array) @$sections[$targetSection] as $subsectionName=>$subsection) {
	    echo "
			<h2>$subsectionName</h2>
            <span class='message-area'>
                <ul class='messages'>";

        // Go through all of the messages
        foreach($results as $level => $messages){
            // Go through all of the messages in the respective error levels
            foreach($messages as $message){
                // Lowercase the level so that we can use it as the class of the li
                $class = strtolower($level);
                // Display each message as a list item
                echo "<li class='$class'>$level: $message</li>";
            }
        }

        echo "
                </ul>
            </span>";
		
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
