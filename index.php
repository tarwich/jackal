<?php

set_time_limit(10);

ini_set("display_errors", 1);
error_reporting(E_ALL | E_NOTICE);

include("libraries/Jackal.php");

// Initialize Jackal
Jackal::load(@$argv);
// Tell Jackal to use Admin in the abscence of a specified module 
Jackal::putSettings("jackal/default-module", "Admin");
// Tell Jackal to try handling the request
Jackal::handleRequest();
// Call the shutdown function(s)
Jackal::shutdown();
