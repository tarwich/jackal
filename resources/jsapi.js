/*

This file is supposed to make all the ajax work nicely. I'll try to document what's supported


<form action='blah'>
	Will load the result of blah into  the <form> tag (as post)

<form action='blah' target='.foo'>
	Will load blah into $(".foo") (as post)

<whatever url='foo'>
	Will load foo when clicked

<whatever url='foo' target='bar'>
	Will load foo into $(bar) when clicked

<whatever triggers='Foo/bar' url='Bin/baz'>
	Will load Bin/baz into <whatever> when Foo/bar completes anywhere for any reason

 */

(function(NS, $) {
	// Ensure we get a jQuery.on (bridging for jQuery < 1.7)
	if(!("on" in jQuery.fn)) jQuery.fn.on = jQuery.fn.bind;
	var $ns;
	// The namespace 
	var ns = window[NS] || (window[NS] = {});
	
	// --------------------------------------------------
	// initialize
	// --------------------------------------------------
	ns.initialize = function() {
		// Currently we're not going to wrap Risk in a namespace
		$ns = $("body");
		// Listen to url clicks
		$(document).on("click", "[url]", ns.onUrlClick);
		// Listen to form submits
		$(document).on("submit", "form[action],form[url]", ns.onSubmit);
		// Listen to ajaxComplete event 
		$(document).off("ajaxComplete", ns.ajaxComplete).on("ajaxComplete", ns.ajaxComplete);
	};
	
	// --------------------------------------------------
	// ajaxComplete
	// --------------------------------------------------
	ns.ajaxComplete = function(event, request, settings) {
		// Process the url and pull out the part we're looking for
		var href = settings.url.split(url())[1].replace(/(^\/|\/$)/g, '');
		
		// Go through every element that has a trigger
		$("[triggers],[trigger]").each(function() {
			// Cache wrapped this for speed and clarity
			var $this = $(this);
			// The triggers could have multiple, comma separated, triggers
			var triggers = ($this.attr("trigger") || $this.attr("triggers")).split(",");
			// The data to post
			var data = {};
			
			// Go through all the triggers looking for a match
			for(var i=0; i<triggers.length; ++i) {
				// If the url begins with this trigger, then it's a match
				if(href.match(triggers[i])) {
					// Get the source
					var source = $this.attr("source");
					// Make sure source has a space in it
					if(!source.match(/ /)) source += " >";
					// Find the target
					$($this.attr("target") || $this)
						// Load the url into this node
						.load(source, ns.makeObject(this));
					break;
				}
			}
		});
	};
	
	// --------------------------------------------------
	// makeObject
	// --------------------------------------------------
	ns.makeObject = function(node) {
		var result = {};
		// Get the thing to iterate
		var attributes = node.attributes || node;
		
		// Go through all the items in the result
		for(var i=0; i<attributes.length; ++i) 
			// Set the 'name' from this item in the result hashmap
			result[attributes[i].name] = attributes[i].value;
		
		return result;
	};
	
	// --------------------------------------------------
	// onSubmit
	// --------------------------------------------------
	ns.onSubmit = function(event) {
		// Don't let the event do anything else
		event.preventDefault();
		// Cache wrapped form
		var $form = $(this);
		// The hashmap we're going to post
		var data = {};
		// Setup the hash with default values (false) 
		$form.find(":input[name]").each(function() { data[$(this).attr("name")] = false; });
		// Setup the hash with real values
		// $($form.serializeArray()).each(function() { data[this.name] = this.value; });
		$.extend(data, ns.makeObject($form.serializeArray()));
		// Find the target
		$($form.attr("target") || $form)
			// Send the form
			.load($form.attr("url") || $form.attr("action") + " >", data, function(html, status) {
				// Show error pages anyway
				if(status == "error") $(this).html(html);
			});
	};
	
	// --------------------------------------------------
	// onUrlClick
	// --------------------------------------------------
	ns.onUrlClick = function() {
		var $this = $(this);
		
		// Find the right target
		$($this.attr("target") || $this)
			// Load the url with the data we collected
			.load($this.attr("url") + " >", ns.makeObject(this));
	};
	
	$(ns.initialize);
})("Risk-module", jQuery);