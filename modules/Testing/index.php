<?php

Jackal::call("Template/change/Testing/template");
js("jackal.js");

?>
<div id='Tests' class='Testing-index'>
<Layout:Columns>
	<columns>
		<column width='400px'>
			<Testing:TestList />
		</column>
		<?php 
		// Each of the subtests were designed with the idea that they would load collapsed, and
		// by clicking on the subtest name the results would expand below showing the "expected" and
		// "actual" results side by side.
		?>
		<column width='100%'>
			<div class='test-results'>
			</div>
		</column>
	</columns>
</Layout:Columns>
</div>
<script type="text/javascript">
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
		// Connect event listeners
		ns.rebind();
		// List of tests and test results need to extend to the bottom of the page
		// and be scrollable
		$ns.find("div.tests, div.test-results").pageHeight({css:{"overflow-y":"auto"}});
		// Mark the $ns as having been initialized
		$ns.data("initialized");
		// Listen to test results
		$(document).bind("ajaxSend ajaxComplete", ns, jackal.interpretMessage);
	};

	//  _[ Rebind ]_______________________________________
	// |                                                  |
	// | Reconnect event listeners to their elements      |
	// | (usually as a result of an ajax call)            |
	// |__________________________________________________|
	ns.rebind = function() {
	};

	ns["Testing/runTest.send"] = function(event, response) {
		if($ns.data("finished")) {
			$ns.data("finished", false);
			$("div.test-results").text("");
		}
	};

	ns["Testing/runTest.complete"] = function(event, response) {
		$("div.test-results").append(response.responseText);
		// Make a note if we've finished running all the tests
		if($(".status-waiting").length == 0) $ns.data("finished", true);
	};

	$(ns.initialize);
})("Testing-index", jQuery);
</script>
