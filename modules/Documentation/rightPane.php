<?php

@($moduleName = $URI["module"]) || @($moduleName = $URI[0]);

?>
<div id='RightPage'>
	<div id='ModuleTitle'>
		<?php $class = Jackal::call("Documentation/moduleTitle/$moduleName");?>
	</div>
	<div id='ModuleReference' class='scroll'>
		<?php Jackal::call("Documentation/moduleReference/$moduleName", array("class"=>$class));?>
	</div>
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
		$ns = $("#" + NS);

		// Connect event listeners
		ns.rebind();
	};
	
	//  _[ Rebind ]_______________________________________
	// |                                                  |
	// | Reconnect event listeners to their elements      |
	// | (usually as a result of an ajax call)            |
	// |__________________________________________________|
	ns.rebind = function() {
		$ns.find("div#ModuleReference").pageHeight();
	};
	
	$(ns.initialize);
})("RightPage", jQuery);

</script>