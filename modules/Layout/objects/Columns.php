<?php

Jackal::make("Layout:LayoutComponent");

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
 * @param String $widthUnit Unit of measurement to use for column width
 * 
 * @return void
 */

class Layout__Columns extends Layout__LayoutComponent {
	protected $_align    	= false;
	protected $_colAlign 	= array();
	protected $_colRatio 	= array();
	protected $_colWrap  	= array();
	protected $_columns  	= array();
	protected $_styles   	= array();
	protected $_width    	= false;
	protected $_length   	= 0;
	protected $styles		= array();
	protected $widthUnit	= "%";
	
	public function __construct($URI) {
		parent::__construct($URI);
	}

	public function __set($name, $value) {
		switch(strtolower($name)) {
			case "colratio"	: $this->_setRatio($value)		; break ; // Ratio
			case "colalign"	: $this->_setAlign($value)		; break ; // Align
			case "column"   : $this->_addColumn($value)  	; break ; // Column
			case "columns"  : $this->_setColumns($value)	; break ; // Columns
			case "colwrap"  : $this->colWrap = $value		; break ; // Col Ratio
			case "width"    : $this->_setWidth($value)		; break ; // Width

			default: return parent::__set($name, $value); break; // Delegate to parent
		}
	}

	private function _addColumn($value) {
		if(is_array($value)) if($value === array_values($value)) $this->columns = $value;
		
		// This is a single column being added
		else $this->_columns[] = $value;
	}
	
	// This will only be passed in through PHP as the colRatio property
	private function _setRatio($value) {
		foreach($value as $i=>$item) {
			if(!@$this->_columns[$i]["width"]) $this->_columns[$i]["width"] = $item;
		}
	}
	
	// This will only be passed in through PHP as the colAlign property
	private function _setAlign($value) {
		foreach($value as $i=>$item) {
			if(!@$this->_columns[$i]["align"]) $this->_columns[$i]["align"] = $item;
		}
	}
	
	// Add columns from a <columns> tag with <column> inside
	private function _setColumns($value) {
		
		// If the value is a string, which means it was created using jarkup
		if(is_string($value)) {
			$value = $this->safeMatrix($value, 1);
			$value = $value["column"];
		}
		
		// Reset indexes
		$value = array_values($value);
		
		// Standardize column structure
		foreach($value as $i=>$item) {
			
			// Get defaults or values from colAlign and colRatio
			$width 	= isset($this->_columns[$i]["width"]) ? @$this->_columns[$i]["width"] : false ;
			$align 	= isset($this->_columns[$i]["align"]) ? @$this->_columns[$i]["align"] : "left" ; 
			$wrap 	= false;
			
			// If it is passed in as strings from PHP instead of Jarkup
			if(is_string($item)) {
				$contents 	= $item;
			
			// This will be called when passed via jarkup 
			} else {
				$contents	= @$item["contents"];
				$width		= isset($item["width"]) ? $item["width"] : $width ;
				$align		= isset($item["align"]) ? $item["align"] : $align ;
				$wrap		= isset($item["wrap"]) ? $item["wrap"] : false;
			}
			
			// Make sure all columns have the right stuff
			$value[$i] = array(
				"contents"	=> $contents,
				"width"		=> $width,
				"align"		=> $align,
				"wrap"		=> $wrap
			);
		}
		
		$this->_columns = $value;
		$this->_length = count($this->_columns);
	}

	private function _setWidth($value) {
		$this->styles["width"] = $this->_width = $this->getSize($value, '%');
	}
	
	/**
	 * Build and render the component
	 * 
	 * @return string The rendered markup
	 */
	public function __toString() {
		// Start output buffering so that content can simply be echoed
		ob_start();
		
		// Get parameters
		$columns 	= $this->columns;
		$widthUnit	= $this->widthUnit;
		
		// Display the columns
		echo "
			<table class='layout-columns' style='{$this->makeStyles($this->styles)}'>
				<tr>";
		$i = 0;

		foreach($columns as $column) {
			$ratio 		= @$column["width"] ? $this->getSize($column["width"], $widthUnit) : round(100/$this->length, 4).$widthUnit ;
			$align 		= @$column["align"] ? $column["align"] : "left" ;
			$wrapping 	= @$column["wrap"] ? $this->str2Bool($column["wrap"]) : false ;
			
			// Column classes
			$class = "layout-column-middle";
			if($i==0) $class = "layout-column-first";
			if($i==($this->length-1)) $class = "layout-column-last";
			$wrapping = $wrapping ? "" : "white-space: nowrap" ;
			
			echo "
					<td class='layout-column layout-column-$i $class' style='width: $ratio; text-align: $align; $wrapping;'>";
			// Only set the column width if it is using something other than a percentage
			if(!strstr($ratio, "%")) {
				echo "
						<div class='layout-column-ratio' style='width: $ratio;'>
							$column[contents]
						</div>";

			// If it is using a percentage
			} else {
				if(@$column["contents"]) {
					echo $column["contents"];
				}
			}
			
			echo "
					</td>";
			$i++;
		}

		echo "
				</tr>
			</table>";
	
		// End output buffering and return contents as string
		return ob_get_clean();
	}
}

