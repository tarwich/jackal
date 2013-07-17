<?php

// Change the template
Jackal::call("Template/change/Documentation/template");

// Get the list of modules
$modules = $this->_getModuleList();

// FIXME: Make groups ACTUALLY work

echo "
	<div class='documentation-smalltoc-section smalltoc'>
		<div class='title'>
			<div class='toc'>
				<h2>All Classes</h2>
				<span><input type='text' name='filter' /></span>
			</div>
		</div>
		<div class='title-spacer'></div>
		<div class='class-list scroll'>
			<div class='list'>
				<ul>";

foreach($modules as $module=>$path) {
	echo "
					<li>
						<a target='load' href='".Jackal::siteURL("Documentation/rightPane/$module")."'>$module</a>
					</li>";
}

echo "
				</ul>
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
		$("input[name=filter]").searches($ns.find("li"));
		$("div.class-list").pageHeight();
		$("div.class-list li").click(ns.setCurrent);
	};

	ns.setCurrent = function() {
		var $this = $(this);
		$("div.class-list li").removeClass("current");
		$this.addClass("current");
	};
	
	$(ns.initialize);
})("documentation-smalltoc-section", jQuery);

</script>