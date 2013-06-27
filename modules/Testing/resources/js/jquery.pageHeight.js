// Allow pageHeight commands to be sent to all pageHeight instances on the page
jQuery.pageHeight = function(command){
	$(".ui-page-height").pageHeight(command);
};

// The pageHeight class
jQuery.fn.pageHeight = function(parameters) {
	var $this = $(this), pos, resizeTimer, windowHeight, newHeight;
	
	// Little fix 
	if (!$this.length) return;
	
	// Add the page-height class to every element that uses this feature
	$this.addClass("ui-page-height");
	
	// Set defaults
	settings = jQuery.extend({
		onResize		: null,		// Function to execute when resize is triggered
		css				: {},		// CSS for the container
		detectScroll	: false,	// Whether or not to detect the window's scroll height
		speed			: 100, 		// Speed of the resize while resizing
		padding			: 0			// Additional padding if the programmer wants to space it out
									// For a component which may be fixed at the bottom of the window.
	}, parameters);
	
	// Store the settings in the object so that every instance of pageHeight can retain their own settings
	$this.settings = settings;
	
	// Resize instantly
	var resize = function() {
		set();
	}
	
	// Do this while the window is resizing
	var resizing = function() {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(set, $this.settings.speed);
	};
	
	// Add up the bottom border and padding for each of the object's parents
	function getPadding() {
		var pad = 0;
		$this.parents().each(function(){
			pad += parseInt($(this).css("padding-bottom"));
			pad += parseInt($(this).css("margin-bottom"));
			pad += parseInt($(this).css("border-bottom-width"));
		});
		pad += parseInt($this.css("padding-bottom"));
		pad += parseInt($this.css("margin-bottom"));
		pad += parseInt($this.css("border-bottom-width"));
		return pad;
	};
	
	// Do this when the window has been resized
	function set() {
		
		// Get the object's offset position in the page
		pos = $this.offset();
		
		// Get the window's height
		windowHeight = $(window).height();
		
		// Get padding of parents
		var padding = getPadding();
		
		// Calculate the new height
		newHeight = windowHeight - pos.top - padding - $this.settings.padding;
		
		// FIXME: add $this.padding
		
		// Set the height
		$this.css({
			height		: newHeight + "px",
			maxHeight	: newHeight + "px"
		});
		
		// Detect document height
		if ($this.settings.detectScroll) {
			newHeight = $(document).height() - pos.top - padding - $this.settings.padding;
			$this.css({
				height		: newHeight + "px",
				maxHeight	: newHeight + "px"
			});
		}
		
		// Execute function on resize
		// function(obj, height)
		if (typeof($this.settings.onResize)=='function') {
			$this.settings.onResize($this, newHeight);
		}

		// Now apply CSS if there is any
		if ($this.settings.css) {
			$this.css($this.settings.css);
		}
	};
	
	// If a command was sent into pageHeight
	if (typeof(parameters)=='string') {
		try { eval(parameters + "()"); } catch(e) {}
	} else {
		// Bind to the window resizing
		$(window).resize(resizing);
	}
	
	// Start at the right size
	set();
	
	return $this;
}