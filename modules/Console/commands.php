<?php

//  __________________________________________________
// / Parse URI                                        \

@( ($command = $URI["command"]) || ($command = $URI[0]) );

// \__________________________________________________/


$files = scandir(dirname(__FILE__)."/commands");

// Resolve aliases
$command = Jackal::setting("console/aliases/$command", $command);
$files = preg_grep("/$command/", $files);
$segments = preg_split('/\s+/', @$URI["line"]);
$URI = array_merge($URI, $segments);

foreach($files as $file) {
	echo "
		<hr size='1' style='color: #33EE33;'/>
		",join($segments, " "),"
		<hr size='1' style='color: #33EE33;'/>";
	include("commands/$file");
	return;
}

echo "<p class='error'>Command $command not found</p>";
