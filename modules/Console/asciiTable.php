<?php

$data = $URI[0];

$columnSizes = array();

foreach((array) @reset($data) as $j=>$column) {
	$columnSizes[$j] = strlen($j);
}

foreach($data as $i=>$row) {
	foreach((array) $row as $j=>$column) {
		$columnSizes[$j] = max(strlen($column), @$columnSizes[$j]);
	}
}

foreach((array) @reset($data) as $j=>$column) {
	$line[] = str_pad($j, $columnSizes[$j], " ", STR_PAD_RIGHT);
}

$result[] = implode(" | ", $line);

foreach($data as $row) {
	$line = array();
	
	foreach((array) $row as $j=>$column) {
		$line[] = str_pad($column, $columnSizes[$j], " ", STR_PAD_RIGHT);
	}
	
	$result[] = implode(" | ", $line);
}

return implode("\n\n", (array) @$result);
