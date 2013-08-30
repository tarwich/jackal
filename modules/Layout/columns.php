<?php

/**
 * Produce nicely formatted columns in a simple layout
 * 
 * @example Create two columns of content
 * <code type='php'>
 * Jackal::call("Layout/columns", array(
 * 	"columns"	=> array(
 * 		"first column content",
 * 		"second column content"
 * 	)
 * ));
 * </code>
 * 
 * @param Array $columns HTML content for each column
 * @param Array $colRatio Ratios for each column, it will be evenly divided by default
 * @param Array $colAlign Text alignment for each column
 * @param Int $width Total width of the column's container. 100% is default. Pixel widths are also allowed
 * @param String $align Center, left, or right alignment of the columns table
 * 
 * @return void
 */

return print(Jackal::make("Layout:Columns", $URI));