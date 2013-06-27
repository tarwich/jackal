(function(NS, $) {
	(typeof(window[NS])=="undefined") && (window[NS]={});
	var $ns, ns = window[NS];

	// --------------------------------------------------
	// initialize
	// --------------------------------------------------
	ns.initialize = function() {
		$ns = $(".jSection-debug");
		$ns.find(".debug-wrapper").hide();
		$ns.find(".debug-wrench a").click(ns.toggle);
		$ns.find(".debug-tab").not(":first").hide();
		$ns.find(".tab").click(ns.changeTab);
	};

	// --------------------------------------------------
	// toggle
	// --------------------------------------------------
	ns.toggle = function() {
		$ns.find(".debug-wrapper").animate({height:"toggle"}, "fast");
		
		// Don't let the browser handle the click
		return false;
	};
	
	// --------------------------------------------------
	// changeTab
	// --------------------------------------------------
	ns.changeTab = function() {
		// Assign short-hand for $(this)
		var $this = $(this);

		// Remvoe the 'current' class from the highlighted tab
		$this.closest("tr").find(".tab.current").removeClass("current");
		// Add the 'current' class to the new tab
		$this.addClass("current");

		// Hide the current debug section
		$ns.find(".debug-tab").hide();
		$ns.find(".debug-tab."+$this.attr("tab")).show();
	}

	$(ns.initialize);
	
})("jSection-debug", jQuery);
