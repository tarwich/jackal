<?php

class Testing__TestList {
	public function __toString() {
		// Start output buffering so that content can simply be echoed
		ob_start();
		
		// Get the test path from settings
		$testPath = Jackal::setting("testing/test-path");
		// Setup a recursion pattern to find all files in all subdirectories
		for($i=0; $i<10; ++$i) @$recursion[] = str_repeat("/*", $i);
		$recursion = "{".implode(",", $recursion)."}";
		// Find all the test files
		$files = Jackal::files($testPath.$recursion."/*.php");
		
		foreach($files as $file) {
			$info = pathInfo($file);
			
			($branch = substr(strstr($info["dirname"], "/tests/"), 7)) 
			|| ($branch = basename(dirname($info["dirname"])));
			// Get the shortpath to the test
			preg_match('_(tests/[^/]+/[^/]+\.php|[^/]+/tests/[^/]+\.php)_', $file, $matches);
			
			$tests[] = array(
				"name"      => $info["filename"],
				"branch"    => $branch, // The branch is everything after /tests/
				"shortPath" => $matches[1],
				"path"      => $file,
			);
		}

		// Group the tests
		foreach($tests as $i=>$test) {
			unset($tests[$i]);
			$tests[$test["branch"]][] = $test;
		}
		
		echo "
			<div class='tests Testing-TestList'>
				<ul>";

		// Output the list of tests
		foreach($tests as $key=>$group) {
			echo "
					<li>
						<span>
							<a class='run'></a>
							<a class='status status-none'></a>
						</span>
						<a class='module'>$key</a>";
			
			foreach($group as $i=>$test) {
				echo "
						<ul>
							<li>
								<span>
									<a class='run' path='$test[shortPath]'></a>
									<a class='status status-none'></a>
								</span>
								<a class='test'>$test[name]</a>
							</li>
						</ul>";
			}

			echo "
					</li>";
		}
				
		echo "
				</ul>
			</div>
		";
		
		$this->_javascript();
	
		// End output buffering and return contents as string
		return ob_get_clean();
	}

	/**
	 * Outputs the javascript for this component
	 * 
	 * @return void
	 */
	private function _javascript() {
		// Don't output javascript more than once
		if(@$this->_javascripted) return;
		
		?>
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
				// Listen to ajaxComplete messages
				$(document).bind("ajaxComplete", ns, jackal.interpretMessage);
				// Connect event listeners
				ns.rebind();
			};
		
			//  _[ Rebind ]_______________________________________
			// |                                                  |
			// | Reconnect event listeners to their elements      |
			// | (usually as a result of an ajax call)            |
			// |__________________________________________________|
			ns.rebind = function() {
				$ns.click(ns.click);
			};

			//  _[ Click ]________________________________________
			// |                                                  |
			// | Handle all clicks in this $ns                    |
			// |__________________________________________________|
			ns.click = function(event) {
				var $this = $(event.target);
				
				// Fire runTest if it's a run button
				$this.closest(".run").each(ns.runTest);
			};

			//  _[ Run Test ]_____________________________________
			// |                                                  |
			// | Runs the clicked test                            |
			// |__________________________________________________|
			ns.runTest = function() {
				var $this = $(this);

				$this.closest("li").find("[path]").closest("li").find(".status")
					.addClass("status-waiting");

				event.stopPropagation();
			};

			ns["Testing/runTest.complete"] = function(event, response) {
				var $result = $("<p>"+response.responseText+"</p>");
				var success = $result.find(".subtest-result").is(".success");

				$ns.find(".status-running")
					.toggleClass("status-success", success)
					.toggleClass("status-failed", !success);
			};


			$(ns.initialize);
		})("Testing-TestList", jQuery);
		</script>
		<?php
	}
}

