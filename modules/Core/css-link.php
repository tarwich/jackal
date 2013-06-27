<?php 

$delimiter = "|";

$resources = (array) Jackal::getResources("head");

$url = Jackal::siteURL("Core/css");

if (!@count($resources["css"])) return;

$css = array();
foreach((array) @$resources["css"] as $file) {
	$css[] = str_replace("/", $delimiter, $file);
}

if (!count($css)) return;

$link = "
	<link rel='stylesheet' type='text/css' href='$url/".join("/", $css)."' />";

return $link;

?>