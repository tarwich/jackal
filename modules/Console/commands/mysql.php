<?php

/**
 * Runs a MySQL command
 * 
 * This command passes everything directly to MySQL
 */

@list($discard, $sql) = preg_split("/\s+/", $URI["line"], 2);
$query = Jackal::loadLibrary("ActiveRecord");
/* @var $query ActiveRecord */
Jackal::loadLibrary("ActiveRecord")->debug(true);
$results = $query->run($sql);
Jackal::loadLibrary("ActiveRecord")->debug(false);

echo "<p>".htmlentities($this->asciiTable(array($results)))."</p>";
