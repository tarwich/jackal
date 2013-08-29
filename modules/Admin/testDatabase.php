<?php

// Create an array that we will populate with messages generated from the test(s)
$messages = array(
    "WARNING" => array(),
    "ERROR"   => array(),
    "OK"      => array(),
);

$messages["WARNING"][] = "No tests have been written to verify database connectivity";

return $messages;