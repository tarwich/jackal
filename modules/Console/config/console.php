<?php

$settings = "
	console:
		# Turn the button off by removing the value
		button: 

		# Toggle console using the following javascript keyCode. If prefixed
		# with + then it requires SHIFT too 
		hotkeys:
			CS-96: show       # SHIFT+\"~\" toggles console
			S-126: show       # SHIFT+\"~\" toggles console
			C-96 : show       # CTRL+\"~\" toggles console
			C-102: fullscreen # CTRL+\"F\" toggles fullscreen
		
		# Some commands may make sense with other names, so this allows 
		# multiple names for the same command
		aliases:
			settings: setting
	
	jackal:
		flaggers: [console]";

Jackal::putSettings($settings);