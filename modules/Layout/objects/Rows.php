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

Jackal::make("Layout:LayoutComponent");

class Layout__Rows extends Layout__LayoutComponent {
	
	protected $_rows		= array();
	protected $_rowAlign	= array();
	protected $_rowHeight	= array();
	protected $_width		= false;
	protected $_align		= "left";
	protected $styles		= array();
	
	public function __construct($URI) {
		parent::__construct($URI);
	}
	
	public function __set($name, $value) {
		switch(strtolower($name)) {
			case "rows"  		: $this->_addRows($value)  		; break ; // Rows
			case "rowalign" 	: $this->_setAlign($value)		; break ; // Col Align
			case "rowheight"	: $this->_setHeight($value)     ; break ; // Col Ratio
			case "width"    	: $this->_setWidth($value)		; break ; // Width
			case "align"    	: $this->align = $value			; break ; // Align

			default: return parent::__set($name, $value); break; // Delegate to parent
		}
	}
	
	// This will only be passed in through PHP as the rowAlign property
	private function _setAlign($value) {
		foreach($value as $i=>$item) {
			if(!@$this->_rows[$i]["align"]) $this->_rows[$i]["align"] = $item;
		}
	}
	
	// This will only be passed in through PHP as the rowHeight property
	private function _setHeight($value) {
		foreach($value as $i=>$item) {
			if(!@$this->_rows[$i]["height"]) $this->_rows[$i]["height"] = $item;
		}
	}
	
	private function _addRows($value) {
		
		// If the value is a string, which means it was created using jarkup
		if(is_string($value)) {
			$value = $this->safeMatrix($value, 1);
			$value = $value["row"];
		}
		
		// Standardize column structure
		foreach($value as $i=>$item) {
			
			// Get defaults or values from colAlign and colRatio
			$height = isset($this->_rows[$i]["height"]) ? @$this->_rows[$i]["height"] : false ;
			$align 	= isset($this->_rows[$i]["align"]) ? @$this->_rows[$i]["align"] : "left" ; 
			$wrap 	= false;
			
			// If it is passed in as strings from PHP instead of Jarkup
			if(is_string($item)) {
				$contents 	= $item;
			
			// This will be called when passed via jarkup 
			} else {
				$contents	= @$item["contents"];
				$height		= isset($item["height"]) ? $item["height"] : $height ;
				$align		= isset($item["align"]) ? $item["align"] : $align ;
				$wrap		= isset($item["wrap"]) ? $item["wrap"] : false;
			}
			
			// Make sure all columns have the right stuff
			$value[$i] = array(
				"contents"	=> $contents,
				"height"	=> $height,
				"align"		=> $align,
				"wrap"		=> $wrap
			);
		}
		
		$this->_rows = $value;
		$this->_length = count($this->_rows);
	}

	private function _setWidth($value) { $this->styles["width"] = $this->_width = $this->getSize($value); }
	
	public function __toString() {
		// Start output buffering so that content can simply be echoed
		ob_start();
		
		$rows = (array) $this->rows;
		
		// Start the rows
		echo "
			<div class='layout-rows' style='{$this->makeStyles($this->styles)}'>";
		
		$i = 0;
		foreach ($rows as $row) {
			$height		= @$row["height"] ? $this->getSize($row["height"], "px") : false ;
			$align 		= @$row["align"] ? $row["align"] : "left" ;
			$even 		= ($i%2) ? "even" : "odd" ;
			
			// Row classes
			$class = "layout-row-middle";
			if ($i==0) $class = "layout-row-first";
			if ($i==(count($rows)-1)) $class = "layout-row-last";
			
			echo "
				<div class='layout-row layout-row-$even layout-row-$i $class' style='".($height ? "height: $height;" : "")." text-align: $align;'>
					$row[contents]
				</div>";
			$i++;
		}
		
		echo "
			</div>";
		
		// End output buffering and return contents as string
		return ob_get_clean();
	}

}


