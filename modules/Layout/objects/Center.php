<?php

Jackal::make("Layout:LayoutComponent");

/**
 * Wraps content with layout styles
 * 
 * segments: $html
 * 
 * @example Center content
 * <code type='php'>
 * echo Jackal::make("Layout:Center", array(
 * 	"html"	=> "Your wrapped content"
 * ));
 * </code>
 * 
 * @param String $html Contents to be placed within the wrapper
 * 
 * @return void
 */

class Layout__Center extends Layout__LayoutComponent {
	
	// Properties
	protected $_html		= "";
	protected $_padding		= "";
	
	public function __construct($URI) {
		parent::__construct($URI);
	}

	public function __set($name, $value) {
		switch(strtolower($name)) {
			case "contents"		:
			case "html"			: $this->html = $value							; break ; // Ratio
			case "padding"		: $this->padding = $value 						; break ;
			case "class"		: $this->addClass($value) 						; break ;
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
		$padding	= $this->padding;
		
		$this->class = "layout-center";
		
		$this->attr("class", implode(" ", array_keys((array) @$this->classes)));
		$attributes = Jackal::call("HTML/makeAttributes", $this->attributes, array("required", "classes", "styles", "html", "script", "div"));
		
		// Create wrapper styles
		$styles = array();
		if ($padding) $styles["padding"] = $this->getSize($padding, "px");
		
		echo "
			<div $attributes style='".$this->makeStyles($styles)."'>
				<span class='layout-center-block'>
					$html
				</span>
			</div>";
	
		// End output buffering and return contents as string
		return ob_get_clean();
	}
}

