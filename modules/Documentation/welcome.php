<?php

/**
 * Displays the table of contents (TOC) listing all the modules in the system.
 * 
 * This method looks though the system and displays a table of contents with 
 * one entry for each item in the system. Clicking on an item should take the
 * user to the reference page for that module. 
 * 
 * @return void
 */

// Change the template
Jackal::call("Template/change/Documentation/template");

Jackal::loadHelper("html");

// Get the modules
$modules = $this->_getModuleList();

css("documentation.css");

echo "
	<div class='modules documentation documentation-module-toc'>
		<div class='title'>
			<div>
				<h4>Jackal ".Jackal::$VERSION." Component Reference</h4>
				<h1>Welcome to <b>Jackal</b>Docs</h1>
			</div>
		</div>
		<div class='title-spacer'></div>
		<div class='welcome scroll'>
			<div>
				<h2>Welcome to <b>Jackal</b> online documentation</h2>
				<p>
					Every module and library in the framework and your application are listed on the left. Click on a module
					or library to view it's documentation. Jackal documentation is a module that can be downloaded from our
					website <a target='_blank' href='http://www.jackalphp.com'>www.jackalphp.com</a>. By dropping this module
					into your website or application, documentation for your copy of the framework and your application will
					immediately become available.
				</p>
			</div>
		</div>
	</div>";
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

		// Connect event listeners
		ns.rebind();
	};
	
	//  _[ Rebind ]_______________________________________
	// |                                                  |
	// | Reconnect event listeners to their elements      |
	// | (usually as a result of an ajax call)            |
	// |__________________________________________________|
	ns.rebind = function() {
		$("div.welcome").pageHeight();
	};
	
	$(ns.initialize);
})("documentation-module-toc", jQuery);

</script>