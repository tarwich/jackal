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

