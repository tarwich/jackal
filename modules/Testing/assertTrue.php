<?php

//  __________________________________________________
// / Parse URI                                        \

$left = $URI[0];

// \__________________________________________________/

if($left) {
	echo "<div class='subtest-result success'>  </div>";
}

else {
	$this->subTestStatus = "fail";
	
	switch(gettype($left)) {
		case "array":
		case "object":
			$left = print_r($left, 1);
			echo "<div class='single'>".htmlentities("$left != true")."</div>";
			break;
		case "boolean":
			$left = $left ? "true" : "false";
			echo "<div class='single'>".htmlentities("$left != true")."</div>";
			break;
		default: 
			$left = print_r($left, 1);
			echo "<div class='single'>".htmlentities("$left != true")."</div>";
			break;
	}
}

