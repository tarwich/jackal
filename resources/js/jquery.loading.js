$.prototype.loading = function(script, parameters, callback, type) {
	var $this = $(this);
	if(typeof(parameters)=="undefined") parameters = {};
	
	if (!type) type = "loading";
	var image = "loading.gif";
	if (type=='saving') image = "saving.gif";
	
	var css = {
		background		: "#FFFFFF url("+url("UI/resources/images/" + image, false) + ") no-repeat center center",
		cursor			: "default",
		opacity			: 0.8
	};
	
	// Allow custom CSS
	if (typeof(parameters["css"])=="object") {
		for (key in parameters["css"]) {
			css[key] = parameters["css"][key];
		}
	}
	
	// Block the element while loading
	$this.block({
		message		: null,
		baseZ		: 9999,
		overlayCSS	: css
	});
	
	// Only if a script is passed
	if (typeof(script)=='string') {
		// Now actually load
		$.ajax({
			url: script,
			type: "POST",
			data: parameters,
			complete: function(request, b, c) {
				$this.html(request.responseText);
				$this.unblock();
				try { callback.apply(this, arguments); } catch(e) {}
			}
		});
		
//		$this.load(script, parameters, function(){
//			$this.unblock();
//			if (typeof(callback)=='function') {
//				callback();
//			}
//		});
	}
	
	return $(this);
}

// Alias for saving
$.prototype.saving = function(script, parameters, callback) {
	$(this).loading(script, parameters, callback, "saving");
}