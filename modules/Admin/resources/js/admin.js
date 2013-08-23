(window["jQuery"]) &&
(function(NS, $) {
    // Get the previous NS from the browser or make a new one
    var ns = window[NS] || (window[NS] = {});
    // The DOM node that wraps the NS
    var $ns, $sidebar, $content;
    
    // --------------------------------------------------
    // initialize
    // --------------------------------------------------
    ns.initialize = function() {
	// Find the DOM node that wraps the NS
	$ns = $("body");
	// Listen to clicks in the sidebar
	$sidebar = $ns.find(".Admin-Sidebar").click(ns.sidebarClick);
	// Find the content section
	$content = $ns.find(".Admin-Content");
    };
    
    // --------------------------------------------------
    // sidebarClick
    // --------------------------------------------------
    ns.sidebarClick = function(e) {
	$content.load(url("Admin/section .Admin-section"), {
	    section: $(e.target).attr("section")
	});
    };
    
    $(ns.initialize);
})("Admin", jQuery);
