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
	// Find the DOM node that wraps the NS
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
		// Hijack clicking on links
		$("a[\\$]").unbind("click", ns.navigate).bind("click", ns.navigate);
		// Hijack submitting forms
		$("form[\\$]").unbind("submit", ns.submitForm).bind("submit", ns.submitForm);
		// Run any tests available on the page
		$(ns.runTest)
	};
	
	// --------------------------------------------------
	// navigate
	// --------------------------------------------------
	ns.navigate = function(e) {
		// Get the hyperlink
		var $target = $(e.target).closest("a[\\$]");

		// Find the selector destination
		$( $target.attr("$") )
			// Set the content to show we're loading something
			.text("...")
			// Load the something
			.load( $target.attr("url") || $target.attr("href"))
		;
		
		// Now that the page has loaded, we need to run any outstanding tests
		// Don't allow the browser to handle this event
		return !$.Event(e).preventDefault();
	};
	
	// --------------------------------------------------
	// runTest
	// --------------------------------------------------
	ns.runTest = function(ignore, e) {
		// Start running self-tests if any are on the page
		$(".admin-test:not(.ran)").addClass("ran").each(function() {
			// Cache wrapped element for speed
			var $this = $(this);

			// Run the test
			$('[admin-section="'+$this.attr("admin-section")+'"] .test-result').each(function(i, node) {
                // Update the node's test to show that we're loading
				$(node)
                    .text("...")
                    .load(url("Admin/showOverviewAlerts"), {
                        test: $this.attr("admin-section")
                    })
            });
		});
	};
	
	// --------------------------------------------------
	// submitForm
	// --------------------------------------------------
	ns.submitForm = function(e) {
		// Get the form
		var $form = $(e.target);
		
		var post = {};
		
		$form.find(":input").each(function(i, node) {
			// Cache wrapped object
			var $node = $(node);
			// Add this element to the post object
			post[$(node).attr("name")] = $node.is(":checkbox") 
				? ($node.is(":checked") ? $node.val() : false)
				: $node.val();
		});
		
		$( $form.attr("$") ).text("...").load($form.attr("action"), post);
		
		return !$.Event(e).preventDefault();
	};
	
	$(ns.initialize);
})("Admin", jQuery);
