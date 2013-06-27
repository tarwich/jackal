/*
 * Jackal.js
 * 
 * This is the core Jackal javascript file.  This file creates the Jackal 
 * Facade.
 * 
 */

(function(NS, $) {
	(window[NS]) || (window[NS]={});
	var $ns, ns = window[NS];
	
	// --------------------------------------------------
	// ajaxComplete
	// --------------------------------------------------
	ns.initialize = function() {
		// Get the flaggers from settings
		ns.flaggers = <?php Jackal::call("Core/getFlaggers/json"); ?>;
		// Get the flaggers from settings
		ns.suffix = <?php echo json_encode(Jackal::setting("suffix")); ?>;
		
		// Prepare a filter for removing flaggers
		ns.filter = new RegExp("^(/|"+ns.flaggers.join("|")+")+"); // Produces /^(/|ajax|whatever)+/
		// Get the base url for stipping later
		ns.baseURL = url("", false).replace(/\.\w+$/, '');
		// Hook into the ajaxComplete event
		$(window).ajaxComplete(ns.ajaxComplete);
	};
	
	// --------------------------------------------------
	// ajaxComplete
	// --------------------------------------------------
	ns.ajaxComplete = function(/*Event*/ e, result, request) {
		var parts;
		
		// Strip the base url out of the path 
		parts = request.url.replace(ns.baseURL, '')
			// remove get vars
			.replace(/\?.*/, '')
			// strip the extension
			.replace(/\.\w+$/, '')
			// remove all flaggers
			.replace(ns.filter, '')
			// lowercase the rest
			.toLowerCase()
			// and break out into pieces 
			.split("/", 2);
		
		// Fire the event that belongs to this particular event
		$(jackal).trigger({
			type	: parts[0] + "/" + parts[1], 
			message	: parts.concat("complete"),
			http	: result
		});
		
		// Fire the event that pertains to this object
		$(jackal).trigger({
			type	: parts[0] + "/*", 
			message	: parts.concat("complete"),
			http	: result
		});
	};
	
	$(ns.initialize);
})("jackal", jQuery);
