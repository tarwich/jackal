<?php

// Get the styles
$dir = dirname(__FILE__);
echo "<style type='text/css'>";
include("$dir/resources/styles.css");
echo "</style>";
Jackal::call("Template/addResource/head/js", "resources/jquery.js");
// Get console settings
$settings = Jackal::setting("console");
?>
<div id='Console' class='Console-wrapper'>
	<span class='hotkeys'>
	<?php 
	foreach($settings["hotkeys"] as $key=>$action) {
		$alt = $control = $shift = $code = "";
		
		@list($code, $prefix) = array_reverse(explode("-", $key));
		$alt     = (int) (strpos($prefix, "A") !== false);
		$control = (int) (strpos($prefix, "C") !== false);
		$shift   = (int) (strpos($prefix, "S") !== false);
		
		echo "<b key='".htmlentities($code, ENT_QUOTES)."' action='$action' alt='$alt' control='$control' shift='$shift'></b>";
	}
	?>
	</span>
	<div class='terminal-wrapper' style='display: none;'>
		<div class='terminal'>
			<table>
				<tr>
					<td class='output'>
						<div class='output-box'>
							Type 'help' for instructions.
						</div>
					</td>
				</tr>
				<tr>
					<td class='input'>
						<div class='input'>
							<input type='text' name='line' class='input-field' />
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<?php
	// The button was just so damned ugly and didn't play nice with the banner.
	// I much prefer to press shift + tilde =) 
	if ($settings["button"]) {
		echo "<button class='toggle-console'>c</button>";
	}
	?>
</div>
<script type="text/javascript">
(function(NS, $) {
	//  __________________________________________
	// /---------- Initialize namespace ----------\
	(window[NS]) || (window[NS] = {});
	var $ns, ns = window[NS];
	var $terminal, $terminalWrapper, $box;
	var resizeTimer;
	ns.hotkeys = {};
	// \__________________________________________/

	//  _[ Initialize ]___________________________________
	// |                                                  |
	// | One-time initialization of namespace             |
	// |__________________________________________________|
	ns.initialize = function() {
		// Find the namespace element
		$ns = $("." + NS);
		// Initialize the history
		(ns.history) || (ns.history = []);
		// Set the index to the end of the history
		ns.historyIndex = ns.history.length;
		// Get the terminal wrapper element
		$terminalWrapper = $ns.find(".terminal-wrapper");
		// Find the terminal box
		$box = $ns.find(".terminal");

		// Parse hotkeys
		$ns.find(".hotkeys b").each(function() {
			var $this = $(this);
			
			ns.hotkeys[[$this.attr("alt")|0, $this.attr("control")|0, $this.attr("shift")|0, $this.attr("key")].join("")] = $this.attr("action");
		});
		
		// Move the console to the bottom of the document
		$ns.appendTo("body");
		// Connect event listeners
		ns.rebind();
	};

	//  _[ Check Hotkey ]_________________________________
	// |                                                  |
	// | See if the key pressed was the hotkey to bring   |
	// | up the console                                   |
	// |__________________________________________________|
	ns.checkHotkey = function(event) {
		var action;
		var index = [event.altKey|0,event.ctrlKey|0,event.shiftKey|0,event.charCode].join(""); 
		
		switch(ns.hotkeys[index]) {
			case "show"       : ns.toggleConsole()    ; return false ; 
			case "fullscreen" : ns.toggleFullscreen() ; return false ; 
		}
	}
	
	//  _[ History Next ]_________________________________
	// |                                                  |
	// | Edits the next command in the history que, or    |
	// | nothing, if at the end of the que                |
	// |__________________________________________________|
	ns.historyNext = function() {
		var $input = $ns.find(".input-field");
		
		ns.historyIndex = Math.min(ns.history.length, ns.historyIndex + 1);
		
		if(ns.historyIndex == ns.history.length) {
			$input.val("");
		} else {
			$input.val(ns.history[ns.historyIndex]);
			$input.select();
		}
	}
	
	//  _[ History Previous ]_____________________________
	// |                                                  |
	// | Edits the previous command in the history que    |
	// |__________________________________________________|
	ns.historyPrevious = function() {
		var $input = $ns.find(".input-field");
		
		ns.historyIndex = Math.max(0, ns.historyIndex - 1);
		$input.val(ns.history[ns.historyIndex]);
		$input.select();
	}
	
	//  _[ Key Down ]_____________________________________
	// |                                                  |
	// | Listen to KeyDown events to tell when an action  |
	// | key is pressed.                                  |
	// |__________________________________________________|
	ns.keyDown = function(event) {
		switch(event.keyCode) {
			case 13: // ENTER
				ns.sendCommand();
				break;
			case 38:
				ns.historyPrevious();
				break;
			case 40:
				ns.historyNext();
				break;
		}
	};
	
	//  _[ Rebind ]_______________________________________
	// |                                                  |
	// | Reconnect event listeners to their elements      |
	// | (usually as a result of an ajax call)            |
	// |__________________________________________________|
	ns.rebind = function() {
		// Handle clicking the toggle console button
		$ns.find("button.toggle-console").unbind().click(ns.toggleConsole);
		// Handle clicking the send button
		$ns.find(".send-button").unbind().click(ns.sendCommand);
		// Monitor keys in the input box
		$ns.find(".input-field").unbind().keydown(ns.keyDown);
		// Monitor hotkey
		$(document).keypress(ns.checkHotkey);

		// Resize output window in fullscreen mode
		$terminal = $ns.find(".terminal");
		$(window).resize(ns.resizing);
		ns.resizing();
	};
	
	//  _[ Toggle Full Screen ]___________________________
	// |                                                  |
	// | Toggle fullscreen mode                           |
	// |__________________________________________________|
	ns.toggleFullscreen = function() {
		// Don't toggle fullscreen unless temrinal is showing
		if(!$ns.find(".terminal").is(":visible")) return;
		
		if($ns.hasClass("fullscreen")) {
			$ns.removeClass("fullscreen");
		} else {
			$ns.addClass("fullscreen");
		}
		
		ns.resizing();
	};

	//  _[ Resizing ]_____________________________________
	// |                                                  |
	// | Resize the output window in fullscreen mode      |
	// |__________________________________________________|
	ns.resizing = function() {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(ns.setHeight, 100);
	};

	//  _[ Set Height ]___________________________________
	// |                                                  |
	// | Set height of the output window                  |
	// |__________________________________________________|
	ns.setHeight = function() {
		if($ns.hasClass("fullscreen")) {
			var height = $ns.height() - 26; // Subtract the height of the input box
		} else {
			var height = 250;
		}
		$terminal.height(height);
	};
	
	//  _[ Send ]_________________________________________
	// |                                                  |
	// | Process the command in the input box             |
	// |__________________________________________________|
	ns.sendCommand = function() {
		var $input = $ns.find(".input-field").select();
		var line = $input.val();
		var $response = $("<div class='response' />").appendTo($ns.find(".output-box"));
		var command = line.match(/\w+/);
		
		if(!command) return;
		
		// Add this command to the history
		ns.history.push(line);
		// Move to the end of the history array
		ns.historyIndex = ns.history.length;
		
		$response.load(
			url("Console/commands/" + command, ["console", "partial"]),
			{line:line},
			function() {
				$box.prop({scrollTop: $box.prop("scrollHeight")});
			}
		);
	};
	
	//  _[ Toggle Console ]_______________________________
	// |                                                  |
	// | Show or hide the console                         |
	// |__________________________________________________|
	ns.toggleConsole = function() {
		// Use fade effect if the console is too large
		if($terminalWrapper.height() > 300) {
			$terminalWrapper[$terminal.is(":visible") ? "fadeOut" : "fadeIn"](100, function() {
				$ns.find(".input-field:visible").focus();
			});
		} 
		// Slide for the default animation
		else {
			$terminalWrapper.slideToggle(function() {
				$ns.find(".input-field:visible").focus();
			});
		}
		
		// Scroll to bottom
		$box.prop({scrollTop: $box.prop("scrollHeight")});
	};

	$(ns.initialize);
})("Console-wrapper", jQuery);

// Not sure of a better way to handle this... so the exit command can call a method 
// in this namespace.
var JackalConsole = window["Console-wrapper"];
</script>