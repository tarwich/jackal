<?php

Jackal::make("Layout:LayoutComponent");

/**
 * Wraps content with layout styles
 * 
 * segments: $html
 * 
 * @example Create two columns of content
 * <code type='php'>
 * Jackal::call("Layout/wrapper", array(
 * 	"html"	=> "Your wrapped content"
 * ));
 * </code>
 * 
 * @param String $html Contents to be placed within the wrapper
 * @param String $width CSS width of the wrapper, default type is percent. If pixels are desired, then use "px"
 * @param String $border CSS border styles for the wrapper
 * @param String $padding CSS padding styles for the wrapper
 * @param String $background CSS background styles for the wrapper
 * 
 * @return void
 */

class Layout__Wrapper extends Layout__LayoutComponent {
	
	// Properties
	protected $_html		= "";
	protected $_width		= 100;
	
	public function __construct($URI) {
		parent::__construct($URI);
	}

	public function __set($name, $value) {
		switch(strtolower($name)) {
			case 0				:
			case "content"		:
			case "html"			: $this->html = $value							; break ; // Ratio

			default: return parent::__set($name, $value); break; // Delegate to parent
		}
	}
	
	/**
	 * Build and render the component
	 * 
	 * @return string The rendered markup
	 */
	public function __toString() {
		// Start output buffering so that content can simply be echoed
		ob_start();
		
		// Get Jackal layout settings
		$settings = Jackal::setting("layout");
		
		$html		= $this->html;
		$border		= isset($this->border) ? $this->border : $settings["wrapper"]["border"] ;
		$padding	= isset($this->padding) ? $this->padding : $settings["wrapper"]["padding"] ;
		$margin		= isset($this->margin) ? $this->margin : $settings["wrapper"]["margin"] ;
		$background	= isset($this->background) ? $this->background : $settings["wrapper"]["background"] ;
		
		// Set width
		$width = $this->getSize($this->width, "%");
		
		// Create wrapper styles
		$styles = array();
		if ($border) 		$styles["border"] 		= $border;
		if ($padding) 		$styles["padding"] 		= $padding;
		if ($margin) 		$styles["margin"] 		= $margin;
		if ($background) 	$styles["background"] 	= $background;
		
		echo "
			<div class='layout-wrapper' style='width: $width;'>
				<div class='layout-wrapper-pad' style='",$this->makeStyles($styles),"'>
					$html
				</div>
			</div>";
	
		// End output buffering and return contents as string
		return ob_get_clean();
	}
}

