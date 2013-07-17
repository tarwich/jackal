<?php
$content = ob_get_clean();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link rel='stylesheet' type='text/css' href='<?php echo Jackal::siteURL("Documentation/resources/styles.css"); ?>' />
		<script type='text/javascript' src='<?php echo Jackal::siteURL("Documentation/resources/javascript.js");?>'></script>
		<?php 
		Jackal::call("Template/head");
		?>
		<title>Jackal <?php echo Jackal::$VERSION;?> Framework Documentation</title>
	</head>
	<body>
		<?php echo $content;?>
	</body>
</html>