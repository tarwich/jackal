<?php

/**
 * Alias for Documentation::moduleTOC
 * 
 * This method is just an alias for moduleTOC.
 * 
 * @return void
 */

// Output the navigation 
//$this->navigation();

// Show the module documentation by default
//$this->moduleTOC();

Jackal::call("Template/change/Template/ajax");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Jackal <?php echo Jackal::$VERSION;?> Framework Documentation</title>
	</head>
	<frameset cols="250,*" border='0' noresize="yes">
	       <frame bordercolor="#3e7cc3" src="<?php echo Jackal::siteURL("Documentation/smallTOC");?>" name="classes" scrolling="no" />
	       <frame bordercolor="#3e7cc3" src="<?php echo Jackal::siteURL("Documentation/moduleTOC");?>" name="load" scrolling="no" frameborder="0" />
	</frameset>
</html>
<?php 

Jackal::call("Template/disable");
exit;
?>