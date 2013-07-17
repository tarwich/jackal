<?php

/**
 * (Not Implemented) In the future, this method will update the secondary 
 * navigation with the links for Documentation sections.
 * 
 * @return void
 */

Jackal::call("Navigation/menu", array(
	"secondary-navigation" => array(
		"Module Reference" => array("title" => "Module Reference", "url" => "Documentation/moduleTOC")
	)
));
