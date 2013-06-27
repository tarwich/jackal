/*
 * Jackal.js
 * 
 * This is the core Jackal javascript file.  This file creates the Jackal 
 * Facade.
 * 
 */
if(typeof(jQuery) != "undefined") {

(function(NS, $) {
	(window[NS]) || (window[NS]=jackal={});
	var $ns, ns = window[NS];
	
	// --------------------------------------------------
	// initialize
	// --------------------------------------------------
	ns.initialize = function() {
		// Get the flaggers from settings
		ns.flaggers = <?php echo "['" . implode("','", array_unique(Jackal::setting("flaggers"))) . "']"; ?>;
		// Get the flaggers from settings
		ns.suffix = <?php echo json_encode(Jackal::setting("suffix")); ?>;
		
		// Prepare a filter for removing flaggers
		ns.filter = new RegExp("^(/|"+ns.flaggers.join("|")+")+"); // Produces /^(/|ajax|whatever)+/
		// Get the base url for stipping later
		ns.baseURL = url("", false).replace(/\.\w+$/, '');
		// Hook into the ajaxComplete event
		$(window).ajaxComplete(ns.ajaxComplete);
		// Hook into the ajaxSend event
		$(window).ajaxSend(ns.ajaxSend);
		// Hook into the ajaxError event
		$(window).ajaxError(ns.ajaxComplete);
	};
	
	// --------------------------------------------------
	// ajaxSend
	// --------------------------------------------------
	ns.ajaxSend = function(/*Event*/ e, result, request) {
		var parts, 
			status = e.type.replace(/^ajax/, "").toLowerCase();
		
		// Strip the base url out of the path 
		parts = ns.extractMessage(request.url)
			// and break out into pieces 
			.split("/", 2);
		
		// Fire the event that belongs to this particular event
		$(jackal).trigger({
			type	: parts[0] + "/" + parts[1], 
			message	: parts.concat(status),
			http	: result
		});
		
		// Fire the event that pertains to this object
		$(jackal).trigger({
			type	: parts[0] + "/*", 
			message	: parts.concat(status),
			http	: result
		});
	};

	// --------------------------------------------------
	// ajaxComplete
	// --------------------------------------------------
	ns.ajaxComplete = function(/*Event*/ e, result, request) {
		var parts, 
			status = e.type.replace(/^ajax/, "").toLowerCase();
		
		// Strip the base url out of the path 
		parts = ns.extractMessage(request.url)
			// and break out into pieces 
			.split("/", 2);
		
		// Fire the event that belongs to this particular event
		$(jackal).trigger({
			type	: parts[0] + "/" + parts[1], 
			message	: parts.concat(status),
			http	: result
		});
		
		// Fire the event that pertains to this object
		$(jackal).trigger({
			type	: parts[0] + "/*", 
			message	: parts.concat(status),
			http	: result
		});
	};
	
	// --------------------------------------------------
	// extractMessage
	// --------------------------------------------------
	ns.extractMessage = function(url) {
		var parts; 

		// Strip the base url out of the path 
		parts = url.replace(ns.baseURL, '')
		// remove get vars
		.replace(/\?.*/, '')
		// strip the extension
		.replace(/\.\w+$/, '')
		// remove all flaggers
		.replace(ns.filter, '')
		// Make sure we only have foo/bar
		.split("/", 2).join("/")
		;

		return parts;
	};
	
	// --------------------------------------------------
	// interpretMessage
	// --------------------------------------------------
	ns.interpretMessage = function(event, request, options) {
		var methods, method, otherNS;
		
		otherNS = event.data;
		event.status = event.type.replace(/^ajax/, '').toLowerCase();
		event.message = jackal.extractMessage(options.url);
		methods = (event.message + "/" + event.status)
			.replace(/(.*?)\/(.*)\/(.*)/, "$1/$2.$3,$1/*.$3,$1/$2.*")
			.split(",");  
		
		for(i in methods) {
			(method = otherNS[methods[i]])
			&& (method instanceof Function)
			&& (method.apply(this, [event, request, options]));
		}
	};
	
	$(ns.initialize);
})("jackal", jQuery);

}
