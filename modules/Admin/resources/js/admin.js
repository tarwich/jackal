(window["jQuery"]) &&
(function(NS, $) {
    // Get the previous NS from the browser or make a new one
    var ns = window[NS] || (window[NS] = {});
    // The DOM node that wraps the NS
    var $ns, $sidebar, $content;
    
    // --------------------------------------------------
    // initialize
    // --------------------------------------------------
    ns.initialize = function() {
	// Find the DOM node that wraps the	 NS
		$ns = $("body");
		// Find the content section
		$content = $ns.find(".Admin-content");
		// Add a trap for when the page is loaded
		$(document).ajaxComplete(ns.ajaxComplete).trigger("ajaxComplete");
    };

	// --------------------------------------------------
	// ajaxComplete
	// --------------------------------------------------
	ns.ajaxComplete = function() {
		console.log("A");
		// Hijack clicking on links
		$("a[\\$]").unbind("click", ns.navigate).bind("click", ns.navigate);
	};
	
	// --------------------------------------------------
	// navigate
	// --------------------------------------------------
	ns.navigate = function(e) {
		// Get the hyperlink
		var $target = $(e.target);
		
		// Find the selector destination
		$( $target.attr("$") )
			// Set the content to show we're loading something
			.text("...")
			// Load the something
			.load( $target.attr("url") || $target.attr("href") )
		;
		// Don't allow the browser to handle this event
		return !$.Event(e).preventDefault();
	};
        
    $(ns.initialize);
})("Admin", jQuery);
