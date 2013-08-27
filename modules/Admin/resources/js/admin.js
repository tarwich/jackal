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
		// Hijack clicking on links
		$("a[\\$]").unbind("click", ns.navigate).bind("click", ns.navigate);
		// Hijack submitting forms
		$("form[\\$]").unbind("submit", ns.submitForm).bind("submit", ns.submitForm);
        // Make sure we don't duplicate any tests
        $(ns.updateTests)
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
        $("[admin-test]:not(.ran)").addClass("ran").each(function() {
			// Cache wrapped element for speed
			var $this = $(this);
			
			// Run the test
			$this.text("...").load(url("Admin/tester"), {
				test: $this.attr("admin-test")
			});
        });
    }

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
        
	// --------------------------------------------------
	// updateTests
	// --------------------------------------------------
	ns.updateTests = function() {
        // Get all of the tests that show up in the sidebar
        $("ul.test-results li p").each(function(i, e) {
            // Cache the text
            $completedTest = $(e).text();
            // Find the tests that have already been run and mark them as such
            $("span.test-list p:not([testing])").each(function(i, f) {
                // If the test already exists in the sidebar, then
                if ($(f).text().indexOf($completedTest) >= 0) {
                    // Update the text to show that it has already been run
                    $(f).text($(f).text() + " [DONE]")
                    // Add the class to the p tag indicating that its' been run
                    $(f).attr("testing", true);
                }
            });
        })
	};

    $(ns.initialize);

    // Start processing the self-tests listed on the page
    $(ns.runTest);
})("Admin", jQuery);
