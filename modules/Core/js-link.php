<?php 

$delimiter = "|";

$resources = (array) Jackal::getResources("head");

$url = Jackal::siteURL("Core/js");

if (!count($resources["js"])) return;

$js = array();
foreach((array) @$resources["js"] as $file) {
	$js[] = str_replace("/", $delimiter, $file);
}

if (!count($js)) return;

$link = "
	<script type='text/javascript' src='$url/".join("/", $js)."'></script>";

return $link;

?>