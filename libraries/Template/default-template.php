<?php

/**
 * Output the default template for this module
 * 
 * Ths purpose of this method is so that this module can ship with a template
 * at the start for the interim between downloading the module and setting up
 * your own template as well as a guide for setting up your template.
 * 
 * @return void
 * 
 */

$content = ob_get_clean();

?>
<html>
	<head>
		<?php
		
		Jackal::call("Template/head");
		
		?>
	</head>
	<body>
		<?php 
			echo $content;
		?>
		<?php //echo $URI["debug-toolbar"]; ?>
	</body>
</html>