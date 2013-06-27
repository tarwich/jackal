<?php

Jackal::putSettings("
	jackal:
		#  _[ Autoload Helpers ]_____________________________
		# |                                                  |
		# | A list of helpers to include by default          |
		# |__________________________________________________|
		autoload-helpers: [error, template]
		
		#  _[ Autoload Libraries ]___________________________
		# |                                                  |
		# | A list of libraries to load automatically at the |
		# | beginning of the script                          |
		# |__________________________________________________|
		autoload-libraries: [Jarkup]
		
		#  _[ Autoload Modules ]_____________________________
		# |                                                  |
		# | A list of modules to load automatically at the   |
		# | beginning of the script                          |
		# |__________________________________________________|
		autoload-modules: []
		
		#  _[ Class Path ]___________________________________
		# |                                                  |
		# | This is the path where jackal will look for      |
		# | modules.                                         |
		# |__________________________________________________|
		class-path: <ROOT>/{<LOCAL>,<JACKAL>}/{<OTHER>modules,libraries,libraries/extra-libraries}/{<MODULE>,<MODULE>.php}

		#  _[ Object Path ]__________________________________
		# |                                                  |
		# | This is the path where jackal will look for      |
		# | definitions of module objects.                   |
		# |__________________________________________________|
		object-path: <ROOT>/{<LOCAL>,<JACKAL>}/{<OTHER>modules,libraries,libraries/extra-libraries}/<MODULE>/objects/<OBJECT>{,.php}
		
		#  _[ Model Path ]___________________________________
		# |                                                  |
		# | This is the path where jackal will look for      |
		# | models.                                          |
		# |__________________________________________________|
		model-path: <ROOT>/{<JACKAL>,<LOCAL>}/{model,modules/*/model/}<MODEL>.php
	
		#  _[ Debug Path ]___________________________________
		# |                                                  |
		# | This is the part of the query string that must   |
		# | preceed anything else                            |
		# |__________________________________________________|
		debug-path: DEBUG
		
		#  _[ Default Action ]_______________________________
		# |                                                  |
		# | What action should be invoked when no specific   |
		# | action was specified.  Primarily used for the    |
		# | index, or the main page of the site              |
		# |__________________________________________________|
		default-action: index
		
		#  _[ Default Module ]_______________________________
		# |                                                  |
		# | What module should be invoked when no module was |
		# | specified.  Primarily used for the index, or the |
		# | main page of the site                            |
		# |__________________________________________________|
		default-module: Site
	
		#  _[ Default Template ]_____________________________
		# |                                                  |
		# | Invoke this view to wrap all output before it    |
		# | goes to the browser                              |
		# |__________________________________________________|
		default-template: 
		
		#  _[ Error Log ]____________________________________
		# |                                                  |
		# | This is the place where PHP will dump errors.    |
		# | Set this value to an empty string \"\" to disable  |
		# | error logging.                                   |
		# | Note: This should be a FOLDER, not a file        |
		# |__________________________________________________|
		error-log: <ROOT>/{<LOCAL>,<JACKAL>}/errors/
		
		#  _[ Flaggers ]_____________________________________
		# |                                                  |
		# | These items may preceed a message.  They will be |
		# | set in Jackal::flags and removed from the URI    |
		# | prior to parsing                                 |
		# |__________________________________________________|
		flaggers: [ajax]
		
		#  _[ Index URL ]____________________________________
		# |                                                  |
		# | This is used to rewrite the URL from             |
		# | http:#www.site.com/index.php?module/action to    |
		# | to                                               |
		# | http:#www.site.com/INDEXURL/module/action        |
		# |__________________________________________________|
		index-url: ?
	
		#  _[ Resource Path ]________________________________
		# |                                                  |
		# | This is the place where jackal should look for   |
		# | resources                                        |
		# |__________________________________________________|
		resource-path: <ROOT>/{<LOCAL>,<JACKAL>}/{modules/<MODULE>/,}resources/{,<TYPE>/}{<FILE>,+<FILE>}
		
		#  _[ Helper Path ]__________________________________
		# |                                                  |
		# | This is the place where jackal should look for   |
		# | helpers                                          |
		# |__________________________________________________|
		helper-path: <ROOT>/{<JACKAL>,<LOCAL>}{,/modules/*}/helpers/{,*/}{,<MY>_}<FILE>{.php,}
		
		#  _[ Aliases ]______________________________________
		# |                                                  |
		# | Jackal will replace all the aliases with their   |
		# | values when handling a request. These are not    |
		# | evaluated in call, but only handleRequest.       |
		# | Aliases may be regular expressions               |
		# |__________________________________________________|
		aliases:
			'/^resources/': JackalModule/resources
					
		#  _[ Trackback ]____________________________________
		# |                                                  |
		# | Set this to 'private' to omit your information   |
		# | from jackalphp.com, but still report usage or    |
		# | false to disable the module                      |
		# |__________________________________________________|
		trackback: true
			
	#  _[ Database Connection ]__________________________
	# |                                                  |
	# | @param host string                               |
	# | @param user string                               |
	# | @param password string                           |
	# | @param database string                           |
	# |__________________________________________________|
	database: 
			host: localhost
			username: 
			password: 
			database: 
			port: 3306
			socket: /var/mysql/mysql.sock	
");

date_default_timezone_set("America/Chicago");

# --------------------------------------------------
# Local Directory
#
# This is the path where Jackal will look for files
# that belong to this application.  To change THIS
# setting, alter the following variable in your
# index script (that calls startup.php)
# $GLOBALS["jackal-settings"]["local-dir"] = "foo/bar"
# --------------------------------------------------
Jackal::setting("local-dir", "private");

?>
