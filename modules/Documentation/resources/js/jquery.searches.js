jQuery.prototype.searches = function(targetElements, callback, filterSource) {
	var timer, selector, filterFunction,
		$this = jQuery(this),
		// If filterSource was provided, we're going to 'find' the results, 
		// otherwise, we want the entire 'row'
		filterMethod = filterSource ? "find" : "first" 
		;
	
	// Get the full selector for the list
	selector = jQuery(targetElements).selector;
	// Default to toggle method
	if(!callback) callback = $.fn.toggle;
	
	filterFunction = function() {
		var $all, $show, $hide, text, regex, sourceType;
		
		// Get the text from the textbox
		(text = $this.val()) || (text = $this.text());
		// Get the elements we're going to filter
		$all = jQuery(selector);
		// Setup the regex to find at least one instance of every word in any order
		regex = new RegExp(  
			text.replace(/[^\w]*([\w-]+)[^\w]*/g, "(?=.*$1)"), "i"  
		);
		 
		// If text is empty we're showing everything
		if(!text) $show = $all; 
		else {
			$show = $(selector).filter(function() {
				return regex.test(
					$(this)[filterMethod](filterSource).text() 
				);
			});
		} 
		
		// We're going to hide the rest
		$hide = $all.not($show);
		// Hide the hides
		callback.apply($hide, [false]);
		// Show the shows
		callback.apply($show, [true]);
	}

	// Listen to keyups to trigger the filter
	$this.keyup(function() {
		clearTimeout(timer);
		// Add a lag to allow mass keystrokes to be ignored
		timer = setTimeout(filterFunction, 150);
	});

	// And we're done, but apply the filter once to
	// hide any elements already applicable
	return $this.keyup();
}
