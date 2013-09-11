<?php

//  __________________________________________________
// / Parse URI                                        \

$left = $URI[0];
$right = $URI[1];

// \__________________________________________________/

//  __________________________________________________
// / diffTrees                                        \
if(!function_exists("diffTrees")) {
	/**
	 * Recursively generate two <ul> lists of differences between two structures
	 * 
	 * This method returns the differences as an array with [0] being the first <ul> list of differences and [1] being
	 * the second.
	 * 
	 * @param array $left  The left hand symbol (lhs) to compare
	 * @param array $right The right hand symbol (rhs) to compare
	 * 
	 * @return array
	 */
	function diffTrees($leftArray, $rightArray) {
		// Ensure that left and right arrays are actual arrays
		$leftArray  = (array) $leftArray; $rightArray = (array) $rightArray;
		// Initialize result text
		$leftTree = $rightTree = "<ul>";
	
		// Go through the arrays twice (they get flipped at the end, so it will cause a reverse-comparison the second time)
		for($i=0; $i<2; ++$i) {
			// Compare the left-hand to the right-hand
			foreach	($leftArray as $key=>$leftValue) {
				// If it's flat (int, string, etc)
				if(is_scalar($leftValue)) $rightValue = @$rightArray[$key];
				// Recurse if non-scalar (object, array)
				else list($leftValue, $rightValue) = diffTrees($leftValue, @$rightArray[$key]);
				$matchClass = ($leftValue == $rightValue) ? "match" : "no-match";
				// Make a flag for unset right hand symbols
				$unset = array_key_exists($key, $rightArray) ? "" : "<i>(unset)</i>";
				// Set the item in the left and right trees
				$leftTree .= "<li class='$matchClass'>$key = $leftValue</li>";
				$rightTree .= "<li class='$matchClass'>$key = $unset$rightValue</li>";
				// Unset these so we don't re-iterate
				unset($leftArray[$key], $rightArray[$key]);
			}
			
			// Switch the right hand symbols and left hand symbols
			list($leftArray, $rightArray, $leftTree, $rightTree) = array($rightArray, $leftArray, $rightTree, $leftTree);
		}
		
		// End the trees
		$leftTree  .= "</ul>";
		$rightTree .= "</ul>";
		
		return array($leftTree, $rightTree);
	}
}
// \__________________________________________________/

if($left == $right) {
	echo "<div class='subtest-result success'>  </div>";
}

else {
	$this->subTestStatus = "fail";
	
	switch(gettype($left)) {
		case "array":
		case "object":
			// Make diff trees
			list($leftTree, $rightTree) = diffTrees($left, $right);
			
			echo "
				<table>
					<thead>
						<tr>
							<td>Expected result</td>
							<td>Actual result</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								".$leftTree."
							</td>
							<td>
								".$rightTree."
							</td>
						</tr>
					</tbody>
				</table>";
			break;
		default: 
			$left = print_r($left, 1);
			$right = print_r($right, 1);
			echo "
				<table>
					<thead>
						<tr>
							<td>Expected result</td>
							<td>Actual result</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>".htmlentities($left)."</td>
							<td>".htmlentities($right)."</td>
						</tr>
					</tbody>
				</table>";
			break;
	}
}
