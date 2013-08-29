<?php

/**
 * Creates and returns the class with the same logic that Jackal uses.
 * 
 * This method uses the same logic as Jackal::createClass, with the exception
 * that it looks for documentation and injects it into the appropriate place in 
 * the code. 
 * 
 * The class that it creates will directly conflict with pre-existing classes, 
 * which is a problem, so the class is created in a namespace called 'Nothing'
 * in order to prevent this problem.  The class is not instantiated, so the 
 * constructor method will not be called, unlike the getModuleDataFromFile 
 * counterpart.
 * 
 * This method looks in the Documentation subfolder for a .txt file with the
 * same name as the module and appends that as a DocComment to the top of the 
 * generated class. This is used for the file header doc comment.
 * 
 * Segments: path
 * 
 * @param string $path The path of the folder from which to derive the class
 * 
 * @return ReflectionClass 
 */

@( ($path = $URI[0]) || ($path = $URI["path"]) );

$name = basename($path);

// See if there is a class definition file in the folder
$files = glob($path."/$name.php");
if(count($files)) return $this->getModuleDataFromFile($files);

$files = glob($path."/*.php");
$methods = array();

foreach($files as $file) {
	// Internal use only
	if($file[0] == "~") continue;
	// Add slashes
	$file_ = addslashes($file);

	$methodName = str_replace(".php", "", basename($file));
	
	// If the file begins with _, but not __, then method access is private
	if(substr($methodName, 0, 2) == "__") {
		$access = "";
		$arguments = "";
		$code = "";
	} elseif($methodName[0] == "_") {
		$access = "private";
		$arguments = "\$URI=array()";
		$code = "\$URI = func_get_args();";
	} elseif($methodName[0] == "#") {
		$access = "protected";
		$arguments = "\$URI=array()";
		$code = "";
	} else {
		$access = "public";
		$arguments = "\$URI=array()";
		$code = "";
	}
	
	// By default, methods are not static
	$static = "";
	// If the file ends with an _, then method is static
	($methodName[strlen($methodName)-1] == "_") && ($static = "static");
	// Pull the doc comment out of the file
	preg_match('!(/\*\*.*?\*/)!s', file_get_contents($file), $matches);
	@list($nothing, $matches["comment"]) = $matches;
	$docComment = @$matches["comment"];
	
	// Remove non-alphanumeric characters and replace with underscores
	$methodName = preg_replace('-\W-', "_", $methodName);
	// Build the method
	$methods[] = <<<END
	
	$docComment
	$access $static function $methodName($arguments) {
		$code
		\$result = include("$file_");
		return \$result;
	}
END;
}

// For future versions
if(!@$base) $base = "JackalModule";
// Get the DocComment from the Documentation folder
$docComment = preg_replace('!/\*\*(.*?)\*/!', '$1', @file_get_contents("$path/Documentation/$name.txt"));
// Wrap docComment in DocComment block
$docComment = "/** $docComment */";

$methodData = implode("\n", $methods);
$classStructure = "$docComment \nclass Nothing_$name extends $base { $methodData } ";

eval($classStructure);

$result = new ReflectionClass("Nothing_$name");

return $result;
