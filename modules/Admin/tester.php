<?php

// Get the self-test that needs to be run from the URI
($selfTest = @$URI['self-test']) || ($selfTest = @$URI[0]);

if($selfTest) {
    // Replace any '.'s with '/'s to set the path to the test
    $selfTest = str_replace(".", "/", $selfTest);
    // Run the test and collect any output
    $output = Jackal::returnCall($selfTest);
    // Display the output
    echo "
        <p class='output'>Results
            <p>$output</p>
        </p>";
}
else
    echo "<p>Error:s No test provided</p>";