jQuery.prototype.pageHeight = function(settings) {
	var $this = $(this), pos, resizeTimer, windowHeight, newHeight;
	
	// Little fix just 
	if (!$this.length) return;

	// Set defaults
	settings = jQuery.extend({
		onResize		: null,		// Function to execute when resize is triggered
		css				: {},		// CSS for the container
		detectScroll	: false,	// Whether or not to detect the window's scroll height
		speed			: 100, 		// Speed of the resize while resizing
		padding			: 0			// Additional padding if the programmer wants to space it out
									// For a component which may be fixed at the bottom of the window.
	}, settings);
	
	// Do this while the window is resizing
	var resizing = function() {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(set, settings.speed);
	};
	
	// Add up the bottom border and padding for each of the object's parents
	function getPadding() {
		var pad = 0;
		$this.parents().each(function(){
			pad += parseInt($(this).css("padding-bottom"));
			pad += parseInt($(this).css("margin-bottom"));
			pad += parseInt($(this).css("borderBottomWidth"));
		});
		pad += parseInt($this.css("padding-bottom"));
		pad += parseInt($this.css("margin-bottom"));
		pad += parseInt($this.css("borderBottomWidth"));
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
		newHeight = windowHeight - pos.top - padding - settings.padding;
		
		// FIXME: add $this.padding
		
		// Set the height
		$this.css({
			height		: newHeight + "px",
			maxHeight	: newHeight + "px"
		});
		
		// Detect document height
		if (settings.detectScroll) {
			newHeight = $(document).height() - pos.top - padding - settings.padding;
			$this.css({
				height		: newHeight + "px",
				maxHeight	: newHeight + "px"
			});
		}
		
		// Execute function on resize
		// function(obj, height)
		if (typeof(settings.onResize)=='function') {
			settings.onResize($this, newHeight);
		}

		// Now apply CSS if there is any
		if (settings.css) {
			$this.css(settings.css);
		}
	};
	
	// Bind to the window resizing
	$(window).resize(resizing);
	
	// Start at the right size
	set();
	
	return $this;
}