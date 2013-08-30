<?php

/**
 * Create a grid and place content into it's cells.
 * 
 * @example Create a grid with two columns
 * <code type='php'>
 * Jackal::call("Layout/grid", array(
 * 	"cells"	=> array(
 * 		"first cell content",
 * 		"second cell content",
 * 		"third cell content",
 * 		"fourth cell content"
 * 	)
 * ));
 * </code>
 * 
 * @example Create a grid with three columns sorted from top to bottom
 * <code type='php'>
 * Jackal::call("Layout/grid", array(
 * 	"cells"			=> array(
 * 		"first cell content",
 * 		"second cell content",
 * 		"third cell content",
 * 		"fourth cell content"
 * 	),
 * 	"maxColumns"	=> 3,
 * 	"left"			=> false
 * ));
 * </code>
 * 
 * @param Array 	$cells 		The HTML contents for every cell
 * @param Int 		$maxColumns	Maximum number of columns, default is two columns
 * @param Bool 		$left 		True will build left to right, and false builds top to bottom. Default is true
 * @param Int 		$width 		Grid width in percent or pixels
 * @param Bool		$even		Set a symetrically even width percentage for the grid cells, default is true
 * @param Bool 		$noPad 		False will ensure no cell has padding, default is true
 * 
 * @return void
 */

// Get parameters and set defaults
@($cells = (array) $URI["cells"]) || ($cells = false);
@($maxColumns = (integer) $URI["maxColumns"]) || ($maxColumns = 2);
@($width = $URI["width"]) || ($width = false);
$left = isset($URI["left"]) ? (boolean) $URI["left"] : true ;
$noPad = isset($URI["noPad"]) ? (boolean) $URI["noPad"] : false ;
$even = isset($URI["even"]) ? (boolean) $URI["even"] : true ;
if (!$cells) return;

// Ensure the array is numerically keyed for row and column ordering
$cells = array_values($cells);

if ($maxColumns>count($cells)) $maxColumns = count($cells);

// Get the width of every cell
$cellWidth = $even ? round(100 / $maxColumns, 2)."%" : "auto" ;

$styles = array();

// Determine if it's a percent or pixel, default to percent
if ($width) {
	$styles["width"] = layout_getSize($width);
}

// Declare the rows array
$rows = array();
$totalRows = ceil(count($cells)/$maxColumns);

// Left to right
if ($left) {
	for($i=0; $i<$totalRows; $i++)
		for ($ii=1; $ii<=$maxColumns; $ii++) {
			$rows[$i][$ii-1] = current($cells);
			next($cells);
		}

// Top to bottom
} else {
	for($i=0; $i<$totalRows; $i++)
		for ($ii=1; $ii<=$maxColumns; $ii++)
			$rows[$i][$ii-1] = @$cells[(($totalRows*$ii)-($totalRows-$i))];
}


// Get no padding style
$padStyle = $noPad ? "no-padding" : "" ;

// Display each row
echo "
	<table class='layout-grid' style='",layout_makeStyles($styles),"'>";
foreach ($rows as $key_row=>$row) {
	$rowClass 	= "layout-grid-row-middle";
	$rowClass 	= !$key_row ? "layout-grid-row-first" : $rowClass ;
	$rowClass 	= count($rows)==($key_row+1) ? "layout-grid-row-last" : $rowClass ;
	$even		= ($key_row%2) ? "even" : "odd" ;
	echo "
		<tr class='layout-grid-row-$even'>";
	foreach ($row as $key_column=>$cell) {
		$cellClass 	= "layout-grid-cell-middle";
		$cellClass 	= !$key_column ? "layout-grid-cell-first" : $cellClass ;
		$cellClass 	= count($row)==($key_column+1) ? "layout-grid-cell-last" : $cellClass ;
		$odd		= ($key_column%2) ? "even" : "odd" ;
		echo "
			<td style='width: $cellWidth;' class='layout-grid-cell layout-cell-$key_column $padStyle $rowClass $cellClass layout-grid-cell-$odd'>
				$cell
			</td>";
	}
	echo "
		</tr>";
}
echo "
	</table>";
