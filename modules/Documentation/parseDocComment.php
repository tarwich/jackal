<?php

/**
 * Processes a DocComment and returns an associative array of the information 
 * found. 
 * 
 * This method is an always-positive method, meaning that it will always return
 * the array key even if the element doesn't exist. So, for example, if a 
 * parsed comment doesn't contain a &#64;return value, this method will still 
 * return returnType in the resultant array.
 * 
 * Segments: comment / documentationPath
 * 
 * @param string $comment The comment that should be parsed. This comment may 
 * 		(but does not have to) still contain the /** ... &#42;/ characters. 
 * @param string $documentationPath The path to the documentation folder to 
 * 		look for files referenced from tags
 *  
 * @return array
 */

if(is_string($URI)) $comment = $URI;
else @( ($comment = $URI["comment"]) || ($comment = $URI[0]) );
@( ($documentationPath = $URI["documentationPath"]) || ($documentationPath = $URI[1]) );

// Normalize line endings
$comment = preg_replace('/(\r\n|\r)/', "\n", $comment);
// Strip /* */ from comment along with * at line starts
$comment = preg_replace('!(?:
^\s*/\*\* (?#Opening of comment) |
\s*\*/ (?#Ending of comment) |
(\n)\s*\* (?#Star at beginning of line)
)!x', '$1', $comment);

// Parse inline tags {@foo }
$comment = preg_replace('/{@link\s+([\S]+)\s*(.*?)}/', '<a href="$1">$2</a>', $comment);

// Actually find the docs
preg_match_all('!(?:
(?P<summary>^.*?(?=\n\s*@|\n\s*\n|\s*$))\s*(?P<description>[^@].*?(?=\n\s*@|$|\n\s*Segments:))? (?# Summary AND description) |
[\n\s]*@param\s* (?P<parameters>.*?)(?=\n\s*@|$) (?# Param ) |
[\n\s]*@return\s* (?P<returnType>.+?\b)\s*\|?(?P<returnDescription>.*?)(?=\n\s*@|$) (?# Return ) |
[\n\s]*@var\s* (?P<type>.*?)(?=\n\s*@|$) (?# Var ) |
[\n\s]*@example\s* (?P<examples>.*?)(?=\n\s*@|$) (?# Example ) |
[\n\s]*[Ss]egments:(?<segments>.*?)(?=\n\s*@|$) (?# Segments ) |
[\n\s]*@nothing (?# Used to reduce typos ) 
)!xsi', $comment, $matches);

// Remove numbered matches
$matches = array_diff_key($matches, array_values($matches));

// Make description an array of lines
$matches["description"] = (array) preg_split("/\n\s*\n/", @$matches["description"][0]);

// Process example tags
foreach($matches["examples"] as $i=>$example) {
	// Sometimes there can be empties
	if(!trim($example)) continue; 
	// Scan the example
	preg_match('/
		(?P<file>[^\s]+\.[^\s+])?
		(?P<title>[^\r\n]+)?
		(?P<body>.*)
		/xs', $example, $segments);
	// Remove numbered matches
	$segments = array_diff_key($segments, array_values($segments));
	// Add the example file
	if($segments["file"]) $segments["body"] .= @file_get_contents("$documentationPath/examples/$segments[file]");
	// Put the example back into the matches
	$examples[] = $segments;
}

// Clean up results
foreach($matches as $i=>$match) {
	$matches[$i] = array_values( // <-- Redo the indexes
		array_filter( // <-- Remove empties 
			$match
		)
	);
}

// Some items should not be arrays
$matches["summary"] = implode("", (array)$matches["summary"]);
$matches["type"] = implode("", (array)$matches["type"]);
$matches["returnType"] = implode("", (array)$matches["returnType"]);

// Split the segments
$matches["segments"] = array_map("trim", array_filter(explode("/", @$matches["segments"][0])));

// Break up parameters
foreach($matches["parameters"] as $i=>$parameter) {
	// Scan the parameter
	preg_match('/
		(?P<type>[^\$]+)?
		(?P<name>\$[^\s]+)?
		(?P<description>.*)
		/xs', $parameter, $segments);
	// Remove numbered matches
	$segments = array_diff_key($segments, array_values($segments));
	// Remove left-hand | from description
	$segments["description"] = preg_replace('/^(\s*)\|(\s*)/m', '$1$2', $segments["description"]);
	// Put the parameter back into the matches
	$matches["parameters"][$i] = $segments;
}

// Put the examples back 
$matches["examples"] = (array) @$examples;

return $matches;
