<?php

/**
 * Returns the <a href='http://www.php.net/ReflectionClass' target='_new'>ReflectionClass</a> 
 * for the module or specified.
 * 
 * This method will take the name of a module or library and return the 
 * appropriate ReflectionClass for it. Currently this method is not very 
 * sophisticated and only wraps and returns the class.
 * 
 * @param string $URI[0] The name of the class to return
 * 
 * @return ReflectionClass
 */

//  __________________________________________________
// / Parse URI                                        \

$file = $URI[0];

// \__________________________________________________/


$path = pathinfo($file);
$className = $path["filename"];

if(!class_exists($className)) $object = Jackal::loadLibrary($className);

return new ReflectionClass($className);
















































return;


$stateLogic = '
	states:
		default:
			patterns:
				- %php%
				- %html%
				- @EOF
		html:
			patterns:
				- @NOTPHP %php%
				- @EOF
		php:
			patterns:
				- <?php %statementList%... ?>
				- <?php %statementList%... @EOF
		classDefinition:
			patterns: 
				- class @NAME %classAncestry% { %statementList% }
				- class @NAME { %statementList% }
		classAncestry:
			patterns:
				- extends @NAME
				- implements @NAME
				- extends @NAME %classAncestry%
				- implements @NAME %classAncestry%
		nameList:
			patterns:
				- @NAME
				- nameList @NAME
		if:
			patterns:
				- if ( %expression% ) %statement%
				- if ( %expression% ) %statement% %else%
		else:
			patterns:
				- else %statement%;
		statementList:
			patterns:
				- @NAME ( ) ;
				- %classDefinition%
	lexers:
		NAME: \b\w+\b 
		NOTPHP: .*?(?=<\?php|$)
		EOF: $
	whitespace: \b\s*
';


//  __________________________________________________
// / Get module code                                  \

$code = file_get_contents(addslashes($file));

// \__________________________________________________/


$data = $code;


Jackal::loadLibrary("Spyc");
$stateLogic = spyc_load($stateLogic);

$states = $stateLogic["states"];
$lexers = $stateLogic["lexers"];
$whitespace = $stateLogic["whitespace"];
$tokens = array();   

// Create tokens from state logic
foreach($states as $i=>$state) {
	foreach($state["patterns"] as $j=>$pattern) {
		$words = preg_split('/(?:(%\w+%)|(@\w+)|\s+|\b)/', $pattern, 0, PREG_SPLIT_DELIM_CAPTURE);
		// Remove empty words (like love, and please (j/k))
		$words = array_values(array_diff($words, array("")));
		assert('$words[0] != $state; // A pattern may not begin with a reference to its parent state');
		// Change the pattern to have better information
		$states[$i]["patterns"][$j] = array(
			"pattern" => $pattern,
			"segments" => $words
		);
	}
}


$candidates = $states["default"]["patterns"];

while(count($candidates)) {
	// Move all the candidates to potential candidates
	$potentials = $candidates;
	// Empty the candidate array
	$candidates = array();
	
	// Expand all the potential candidates
	while(count($potentials)) {
		// Go through each potential candidate
		$candidate = array_shift($potentials);
		// If this candidate has no more segments, then dump it
		if(!count($candidate["segments"])) continue;
		// Get the first segment
		$segment = array_shift($candidate["segments"]);
		
		// See if this is a pointer
		if(preg_match('/^%(\w+)%$/', $segment, $matches)) {
			// Get the next state
			$state = $states[$matches[1]];
			// Add all the segments in the new state to the front of the present state
			foreach($state["patterns"] as $pattern) {
				$segments = array_merge($pattern["segments"], $candidate["segments"]);
				// Add this candidate back to the potential candidates array
				$potentials[] = array("segments" => $segments, "history" => @$candidate["history"], "match" => @$candidate["match"]);
			}
		} 
		// The state wasn't a pointer
		else {
			// Add the segment back to the candidate's segments
			array_unshift($candidate["segments"], $segment);
			// Add this candidate to the candidates array
			$candidates[] = $candidate;
		}
	}
	
	// Remember the last set of candidates
	$lastCandidates = $candidates;
	// Clear the last whole match
	$wholeMatch = "";
	
	// Go through the candidates and eliminate non-matches
	foreach($candidates as $i=>$candidate) {
		// Get the history
		$history = @$candidate["history"];
		// Get the first segment
		$segment = array_shift($candidate["segments"]);
		
		if($segment[0] == "@") $lexer = $lexers[substr($segment, 1)];
		else $lexer = preg_quote($segment);
		
		if(preg_match("/^\s*($history)\s*($lexer)\b/s", $data, $matches)) {
			$wholeMatch = $matches[0];
			$candidate["match"] = $matches[0];
			// Remember the history of matches
			@$candidate["history"] .= "\s*$lexer";
			// Add the candidate back to the list
			$candidates[$i] = $candidate;
		} else {
			unset($candidates[$i]);
		}
	}
	
//	$lastMatch = "";
//	
//	foreach($candidates as $candidate) {
//		$match = $candidate["match"];
//		
//		if($lastMatch) if($match != $lastMatch) dir("\n\nAmbiguous match $match != $lastMatch \n\n$data\n\n");
//		
//		$lastMatch = $match;
//	}
//
//	$data = substr($data, strlen($wholeMatch));
//	echo '<pre>', htmlentities(print_r($candidates, 1)), '</pre>';
//	echo '<pre>', htmlentities(print_r(substr($data, 0, 15), 1)), '</pre>';
}

if($data) {
	echo '<pre>', htmlentities(print_r($lastCandidates, 1)), '</pre>';
	echo '<pre>', htmlentities(print_r("ERROR: unexpected $data", 1)), '</pre>';;
}

echo '<pre>', htmlentities(print_r($code, 1)), '</pre>';
return;















































$data = $code;

Jackal::loadLibrary("Spyc");
$stateLogic = spyc_load($stateLogic);

$states = $stateLogic["states"];
$lexers = $stateLogic["lexers"];
$whitespace = $stateLogic["whitespace"];

// Break up the state patterns into subpatterns
foreach($states as $i=>$state) {
	// Go through each pattern in this state
	foreach((array) @$state["patterns"] as $j=>$pattern) {
		// Replace lexers
		$pattern = preg_replace('/@(\w+)\b/e', '$lexers["$1"]', $pattern);
		// Replace whitespace
		$pattern = preg_replace('/\s+/', $whitespace, $pattern);
		// Break into subpatterns
		$subpatterns = preg_split('/(%\w+%(?:...)?)/', $pattern, 0, PREG_SPLIT_DELIM_CAPTURE);
		
		// Add metadata to all the subpatterns
		foreach($subpatterns as $k=>$text) {
			if($text) {
				$subpattern = array("state" => "", "repeat" => "", "text" => "");
				
				if(preg_match('/^%\w+%(?:\.\.\.)?$/', $text)) {
					$subpattern["state"] = rtrim($text, ".");
					if(substr($text, -3, 3) == "...") $subpattern["repeat"] = true;
				} else {
					$subpattern["text"] = $text;
				}
				
				// Put the pattern back
				$subpatterns[$k] = $subpattern;
			} else {
				unset($subpatterns[$k]);
			}
		} 
		
		// Store back into logic
		$states[$i]["patterns"][$j] = array("subpatterns" => $subpatterns);
	}
}

$stack = array(array(
	"state" => "default",
	"patterns" => $states["default"]["patterns"]
));

echo "<pre>";
while(count($stack)) {
	$current = array_pop($stack);
	$currentState = $current["state"];
	$patterns = (array) $current["patterns"];
	
	echo "State: $currentState\n";
	
	while(count($patterns)) {
		// Get the next pattern
		$pattern = array_shift($patterns);
		// Get the subpatterns
		$subpatterns = (array) @$pattern["subpatterns"];
		
		while(count($subpatterns)) {
			// Get the next subpattern
			$subpattern = array_shift($subpatterns);
			echo "Subpattern: $subpattern[text]$subpattern[state]\n";
			
			// See if this subpattern is actually a state reference
			if($subpattern["state"]) {
				// Put the pattern back in the patterns
				array_unshift($patterns, array("subpatterns" => $subpatterns));
				// Put the current state back on the stack
				array_push($stack, array(
					"state" => $currentState, 
					"patterns" => $patterns, 
					"subpatterns" => $subpatterns
				));
				// Get the new state
				$newState = trim($subpattern["state"], "%");
				echo "State change: $currentState -&gt; $newState\n";
				// Push the new state onto the stack
				array_push($stack, array(
					"state" => $newState,
					"patterns" => $states[$newState]["patterns"]
				));
				// Get the next state
				$currentState = "_NEXT_";
			} else {
				$text = $subpattern["text"];
				echo "Checking subpattern: $text\n";
				$count = preg_match('/^\s*$text/s', $data, $matches, PREG_OFFSET_CAPTURE);
				echo "<pre>",htmlentities(print_r(compact("matches"), 1)),"</pre>";
			}
			
			if($currentState == "_NEXT_") break;
		}
		
		if($currentState == "_NEXT_") break;
	}
}


return;



























//  __________________________________________________
// / Expand module code                               \

// Find php code
preg_match_all('/<\?php\s+(.*?)(?:\?>|$)/is', $code, $matches);
$code = implode("; ", $matches[1]);

// \__________________________________________________/


//  __________________________________________________
// / Parse code                                       \

$preg_phpDoc = '';
$preg_word = '\w+';
$preg_scope = "(?:public|private|protected|static)*";
$preg_variable = "\\\$$preg_word";
$preg_field = "$preg_scope \s+ $preg_variable \s*.*?;";

$preg_pattern = "class \s+ ($preg_word) (?:\s+ extends ($preg_word))? \s* { (.*?) } (?=\s*class|\s*\$)";

preg_match_all("/$preg_pattern/sx", $code, $matches);
@list($nothing, $matches["class"], $matches["base"], $matches["body"]) = $matches;

foreach($matches["class"] as $i=>$className) {
	preg_match_all("/($preg_field)/xs", $matches["body"][$i], $fieldsAndMethods);
	
	echo "<pre>",htmlentities(print_r($fieldsAndMethods, 1)),"</pre>";
	echo "<hr>";
	echo "<hr>";
	break;
}

// \__________________________________________________/

echo "<pre>",htmlentities(print_r(array_diff_key($matches, array_fill(0,15,0)), 1)),"</pre>";
echo "<hr>";
echo "<pre>",htmlentities(print_r($matches, 1)),"</pre>";
echo "<hr>";
echo "<pre>",htmlentities(print_r($code, 1)),"</pre>";
