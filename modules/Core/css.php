<?php 

Jackal::setAjax();

header('Content-type: text/css');

Jackal::call("Core/output-resource", array("files"=>$URI["segments"]));

?>