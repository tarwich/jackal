<?php

/**
 * Clears the output screen
 * 
 * There is no more information about this command
 */


?><script type="text/javascript">
(function(NS, $) {
	//  __________________________________________
	// /---------- Initialize namespace ----------\
	(window[NS]) || (window[NS] = {});
	var $ns, ns = window[NS];
	// \__________________________________________/

	//  _[ Initialize ]___________________________________
	// |                                                  |
	// | One-time initialization of namespace             |
	// |__________________________________________________|
	ns.initialize = function() {
		// Find the namespace element
		$ns = $("." + NS);
		$ns.find(".output-box").empty();
	};

	$(ns.initialize);
})("Console-wrapper", jQuery);
</script>