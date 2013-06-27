<?php

Jackal::putSettings("
	template:
		# --------------------------------------------------
		# ob-start
		#
		# The message to process when first starting the 
		# output buffer
		# --------------------------------------------------
		ob-start: Template/ob-start
		
		# --------------------------------------------------
		# ob-end-flush
		#
		# The message to process when flushing the output
		# buffer the final time
		# --------------------------------------------------
		ob-end-flush: Template/ob-end-flush
		
		# --------------------------------------------------
		# template-message
		#
		# This is the message that the template will 
		# process.  Basically, you would put something like
		# Site/template and this would call the site 
		# on ob-end-clean
		# --------------------------------------------------
		template-message: Template/default-template
		
		# --------------------------------------------------
		# gzip
		#
		# States that the server should automatically 
		# attempt to zip pages sent to the client
		# --------------------------------------------------
		gzip: -1
		
		
	jackal:
		# Set myself to load at startup
		autoload-libraries: [template]
		
		# Set of flaggers that this module supports 
		flaggers: [ajax, partial, styling]
");

