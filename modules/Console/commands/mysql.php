<?php

/**
 * Runs a MySQL command
 * 
 * This command passes everything directly to MySQL
 */

@list($discard, $sql) = preg_split("/\s+/", $URI["line"], 2);
$query = Jackal::loadLibrary("QueryBuilder");
/* @var $query QueryBuilder */
Jackal::loadLibrary("QueryBuilder")->debug(true);
$results = $query->runSQL($sql);
Jackal::loadLibrary("QueryBuilder")->debug(false);

echo "<p>".htmlentities($this->asciiTable(array($results)))."</p>";
