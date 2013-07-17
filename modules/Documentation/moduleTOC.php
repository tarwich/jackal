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

// Did this so I didn't destroy this page just incase
return Jackal::call("Documentation/welcome");

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
		<div class='toc scroll'>
			<div class='list'>
				<table>
					<tr class='header'>
						<th>
							Module
						</th>
						<th>
							Description
						</th>
					</tr>";
$i = 0;
foreach($modules as $module=>$path) {
	if(is_dir($path)) $class = $this->getModuleDataFromFolder(array($path));
	else $class = $this->getModuleDataFromFile(array($path));
	
	$doc = $this->parseDocComment($class->getDocComment());
	$evenOdd = ($i++%2) ? "odd" : "even" ;
	echo "
					<tr class='$evenOdd'>
						<th>
							<a target='load' href='".Jackal::siteURL("Documentation/rightPane/$module")."'>$module</a>
						</th>
						<td>
							{$this->addLinks($doc["summary"])}
						</td>
					</tr>";
}

echo "
				</table>
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
		$("div.toc").pageHeight();
	};
	
	$(ns.initialize);
})("documentation-module-toc", jQuery);

</script>