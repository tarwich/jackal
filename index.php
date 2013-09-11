<?php

// Load Jackal
include("load.php");
// Tell Jackal to use Admin in the abscence of a specified module 
Jackal::putSettings("jackal/default-module", "Admin");
// Tell Jackal to try handling the request
Jackal::handleRequest();
// Call the shutdown function(s)
Jackal::shutdown();
