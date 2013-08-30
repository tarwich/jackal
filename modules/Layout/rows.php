<?php

/**
 * Produce nicely formatted rows in a simple layout
 * 
 * @example Create two rows of content
 * <code type='php'>
 * Jackal::call("Layout/rows", array(
 * 	"rows"	=> array(
 * 		"first row content",
 * 		"second row content"
 * 	)
 * ));
 * </code>
 * 
 * @param Array $rows HTML content for each row
 * @param Array $rowAlign Text alignment for each Row
 * @param Array $rowHeight Height for each row's height in pixels or percentages
 * @param Int $width Total width of the row's container. 100% is default. Pixel widths are also allowed
 * @param String $align Center, left, or right alignment of the rows table
 * 
 * @return void
 */

return print(Jackal::make("Layout:Rows", $URI));