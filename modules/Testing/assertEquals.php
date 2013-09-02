<?php

//  __________________________________________________
// / Parse URI                                        \

$left = $URI[0];
$right = $URI[1];

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

function diffTrees($leftArray, $rightArray) {
	// Ensure that left and right arrays are actual arrays
	$leftArray  = (array) $leftArray; $rightArray = (array) $rightArray;
	// Initialize result text
	$leftTree = $rightTree = "<ul>";
	
	// Go through the arrays twice (they get flipped at the end, so it will cause a reverse-comparison the second time)
	for($i=0; $i<2; ++$i) {
		// Compare the left-hand to the right-hand
		foreach	($leftArray as $key=>$leftValue) {
			$rightValue = @$rightArray[$key];
			// Recurse if non-scalar (object, array)
			if(!is_scalar($leftValue)) list($leftValue, $rightValue) = diffTrees($leftValue, $rightValue);
			$leftTree .= "<li>$key = $leftValue</li>";
			$rightTree .= "<li>$key = $rightValue</li>";
			// Unset these so we don't re-iterate
			unset($leftArray[$key], $rightArray[$key]);
		}
		list($leftArray, $rightArray, $leftTree, $rightTree) = array($rightArray, $leftArray, $rightTree, $leftTree);
	}
	
	$leftTree  .= "</ul>";
	$rightTree .= "</ul>";
	
	return array($leftTree, $rightTree);
}
