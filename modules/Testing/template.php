<?php 

// Load resources
js("jquery.js", true);
js("javascript.js");
css('styles.css');

// Get the output buffer
$buffer = ob_get_clean();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<?php Jackal::call("Template/head");?>
	<title>Jackal Testing Framework</title>
<body>
	<div id='wrapper'>
		<div id='header'>
			<table width='100%'>
				<tr>
					<td width='50%'>
						<div class='buttons'>
							<span class='run-all'><a class='run' title='Run all tests'></a></span>
							<span class='run-failed'><a title='Run failed tests'></a></span>
						</div>
					</td>
					<td>
						<div class='title'>
							Jackal Testing Framework
						</div>
					</td>
					<td width='50%'>
						<div class='timer'>
							Time
							<b>0</b><b>0</b><b>:</b><b>0</b><b>0</b><b>:</b><b>0</b><b>0</b>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<div id='body'>
			<?php echo $buffer; ?>
		</div>
	</div>
</body>
</html>
<script type="text/javascript">
/*
Just an example I tossed together so I could make sure the design stayed pretty when
the numbers changed
*/
(function(NS, $) {
	//  __________________________________________
	// /---------- Initialize namespace ----------\
	(window[NS]) || (window[NS] = {});
	var $ns, ns = window[NS], currentTime = 0, $seconds;
	// \__________________________________________/

	//  _[ Initialize ]___________________________________
	// |                                                  |
	// | One-time initialization of namespace             |
	// |__________________________________________________|
	ns.initialize = function() {
		//  __________________________________________________
		// / Map elements                                     \
		
		$ns = $("#"+NS);
		ns.$timer = $("#header .timer");
		ns.$title = $("#header .title"); // Title

		// \__________________________________________________/

		// Handle clicking run all button
		$ns.find(".run-all").click(ns.runAll);
		// Handle clicking run failed button
		$ns.find(".run-failed").click(ns.runFailed);
		// Set a timer to run queued tests every 200 ms
		setInterval(ns.runTests, 200);
		// Start the runner
		$ns.data("running", false);
		// Initialize finished to true for timer
		$ns.data("finished", true);
		// Listen to ajaxComplete messages
		$ns.bind("ajaxComplete", ns, jackal.interpretMessage);
	};

	//  _[ Run All ]______________________________________
	// |                                                  |
	// | Run all the tests                                |
	// |__________________________________________________|
	ns.runAll = function() {
		// Click all the run buttons
		$("li .run").click();
	};

	//  _[ Run Failed ]___________________________________
	// |                                                  |
	// | Run all tests that have a status of failed       |
	// |__________________________________________________|
	ns.runFailed = function() {
		// Click all the run buttons of li's with .status-failed
		$("li:has(.status-failed) .run").click();
	};

	//  _[ Run Tests ]____________________________________
	// |                                                  |
	// | Watches for tests that are waiting to run and    |
	// | runs them                                        |
	// |__________________________________________________|
	ns.runTests = function() {
		// Animate the title
		ns.$title.text(
			[
			"JACKAL TESTING FRAMEWORK", 
			"JACKAL TESTING FRAMEWORK", 
			"JACKAL TESTING FRAMEWORK", 
			"JACKAL TESTING FRAMEWORK", 

			"JACKAL T3STING FRAMEWORK",
			"JACKAL TESTING FRAM3WORK"
			][Math.floor(Math.random()*6)]
		);

		// If we're not already running tests
		if(!$ns.data("running")) {
			var $test = $(".status-waiting:first");

			// No more tests
			if($test.length == 0) {
				$ns.data("finished", true);
			}

			// Run the next test
			else {
				// Note that we're currently running a test
				$ns.data("running", true);
				// Update the status of this test
				$test.addClass("status-running").removeClass("status-waiting");
				
				// If we were previously finished
				if($ns.data("finished")) {
					// Note that we're no longer finished
					$ns.data("finished", false);
					// Get a new start time
					$ns.data("startTime", new Date());
				}
				
				// Send the test to the server
				$.post(
					url("Testing/runTest", "partial"),
					$($test.closest("li").find("[path]:first")[0].attributes)
				);
			}
		}

		else {
			//  __________________________________________________
			// / Update the elapsed time                          \
			
			var startTime = $ns.data("startTime");

			if(startTime) {
				var elapsed = new Date() - startTime;
                var hours   = Math.floor(elapsed / (60*60000)),
                    minutes = Math.floor(elapsed / 60000),
                    seconds = Math.floor(elapsed / 1000 % 60);

				var time = "0" + [0, 0, 2].join(":0").replace(/:0\d{2}/g, ":$1");
				ns.$timer.html("Time <b>" + time.split("").join("</b><b>") + "</b>");
			}

			// \__________________________________________________/
		}
	};

	//  _[ Test Complete ]________________________________
	// |                                                  |
	// | When a test finishes, this routine is called     |
	// |__________________________________________________|
	ns["Testing/runTest.complete"] = function(a, response, c) {
		var $test = $(".status-running");

		$test.removeClass("status-running");

		// Prepare to run the next test
		$ns.data("running", false);
	};

	$(ns.initialize);
})("wrapper", jQuery);

</script>
