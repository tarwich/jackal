<?php 

/**
 * Provides information on how to use the Console
 * 
 * To get a list of all commands, type: help
 * To get more information about a command, type: help &lt;command&gt;
 * 
 */
@( ($command = $URI[2]) );

// If a command wasn't specified, then list the commands
if(!$command) {
	echo "<p>Commands: </p>";
	$files = glob(dirname(__FILE__)."/*.php");
	$commands = array();
	
	foreach($files as $file) {
		$info = pathinfo($file);
		
		$name = $info["filename"];
		$contents = file_get_contents($file);
		preg_match('~/\*\*(.*?)(?:(?:[\s\*]*[\n]){2}|\*/)~s', $contents, $matches);
		@list($nothing, $matches["summary"]) = $matches;
		$summary = trim(preg_replace('~[\r\n]+\s*\*~', '', @$matches["summary"]));
		// Store this command in the hash
		$commands[$name] = $summary;
	}
	
	// Print the command table
	echo "<pre>".Jackal::returnCall("Console/asciiTree", $commands)."</pre>";
	// Print the table footer
	echo "<p>For more information on a command, type: help &lt;command&gt;</p>";
} else {
	$file = dirname(__FILE__) . "/$command.php";
	
	if(!file_exists($file)) {
		echo "<p>Command &lt;$command&gt; does not exist</p>";
		return;
	}
	
	echo "Command: $command";
	$contents = @file_get_contents($file);
	preg_match('~/\*\*(.*?)\*/~s', $contents, $matches);
	@list($nothing, $matches["help"]) = $matches;
	$help = trim(preg_replace('~[\r\n]+\s*\*~', "\n", @$matches["help"]));
	
	if(!$help) echo "<p>There is no help available for this command</p>";
	
	foreach(explode("\n", $help) as $line) {
		echo "<p>$line</p>";
	}
}
