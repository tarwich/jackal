<?php 

Jackal::setAjax();

header('Content-type: application/javascript');

Jackal::call("Core/output-resource", array("files"=>$URI["segments"]));
