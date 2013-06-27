<?php

Jackal::putSettings("
	# --------------------------------------------------
	# ob-start
	#
	# The message to process when first starting the 
	# output buffer
	# --------------------------------------------------
	ob-start			: Template/ob-start
	
	# --------------------------------------------------
	# ob-end-flush
	#
	# The message to process when flushing the output
	# buffer the final time
	# --------------------------------------------------
	ob-end-flush		: Template/ob-end-flush
	
	# --------------------------------------------------
	# template-message
	#
	# This is the message that the template will 
	# process.  Basically, you would put something like
	# Site/template and this would call the site 
	# on ob-end-clean
	# --------------------------------------------------
	template-message	: Template/default-template
	
	# --------------------------------------------------
	# nothing
	#
	# Keeping the arrays pretty is all
	# --------------------------------------------------
");

?>