<?php

/**
 * Outputs the contents of the html head block
 * 
 * The items this outputs are all the css include links, the js include tags 
 * and the title as set by setTitle() (or in the configuration see setTitle()
 * or getTitle())
 * 
 * @example Using head to allow template to output the head block
 * 
 * This allows template to do 100% of the head
 * 
 * <code type="html">
 * 	<html>
 * 		<head>
 * 			<?php Jackal::call("Template/head"); ?>
 * 		</head>
 * 		<body>
 * 			<!-- Content Here -->
 * 		</body>
 * 	</html>
 * </code>
 * 
 * However, you can also do parts of the head yourself
 * 
 * <code type="html">
 * 	<html>
 * 		<head>
 * 			<?php Jackal::call("Template/head"); ?>
 * 			<title> Override the Template title </title>
 * 		</head>
 * 		<body>
 * 			<!-- Content Here -->
 * 		</body>
 * 	</html>
 * </code>
 * 
 * @return void
 */

$css = (array) @$this->exResources["head"]["css"];
$js = (array) @$this->exResources["head"]["js"];
$title = $this->getTitle();

// Output the favicon
@Jackal::call("Favicon/link");

if(Jackal::flag("styling")) {
	$existingJS = $existingCSS = array();
} elseif(Jackal::flag("partial")) {
	$existingCSS = explode(" ", @$_COOKIE["css"]);
	$existingJS = explode(" ", @$_COOKIE["js"]);
} else {
	$existingJS = $existingCSS = array();
}

// Just incase there is no title or the designer wants to insert it himself
echo '<title>', $title,'</title>';

// See if we're in debug mode
$debugging = Jackal::debugging();
$partial = Jackal::flag("partial");
// Store the headers in order to reset the content type later
$headers = headers_list();

foreach($css as $entry) {
	$token = md5($entry["file"]);
	if(in_array($token, $existingCSS)) continue;
	else $existingCSS[] = $token;
	$debug_ = $debugging ? "origin='$entry[origin]' file='$entry[file]' md5='$token'" : "";
	
	if($partial) {
		$data = Jackal::handleRequest($entry["file"], true);
		echo "\n<style type='text/css' location='head' $debug_>$data</style>";
	} else {
		$file = Jackal::siteURL($entry["file"]);
		echo "\n<link rel='stylesheet' type='text/css' href='$file' $debug_ />";
	}
}

foreach($js as $entry) {
	$token = md5($entry["file"]);
	if(in_array($token, $existingJS)) continue;
	else $existingJS[] = $token;
	$debug_ = $debugging ? "origin='$entry[origin]' file='$entry[file]'" : "";
	
	if($partial) {
		$data = Jackal::handleRequest($entry["file"], true);
		echo "\n<script type='text/javascript' location='head' $debug_>$data</script>";
	} else {
		$file = Jackal::siteURL($entry["file"]);
		echo "\n<script type='text/javascript' src='$file' $debug_></script>";
	}
}

// Get the content type header
$headers = preg_grep('/^Content-type:/i', $headers);
// Improvise
if(!isset($headers[0])) $headers[] = "Content-type: text/html";
// Send the content type header
foreach($headers as $header) @header($header);

if(headers_sent()) {
	if(false) {
	?>
	<script type='text/javascript'>
		document.cookie = 'css=<?php echo implode("+", array_unique(array_filter($existingCSS))); ?>';
		document.cookie = 'js=<?php echo implode("+", array_unique(array_filter($existingJS))); ?>';
	</script>
	<?php 
	}
} else {
	$path = parse_url(Jackal::siteURL("", false), PHP_URL_PATH);
	$path = implode("", explode(Jackal::setting("jackal/debug-path"), $path, 2));
	setcookie("css", implode(" ", array_unique(array_filter($existingCSS))), 0, $path);
	setcookie("js", implode(" ", array_unique(array_filter($existingJS))), 0, $path);
}

?><script type="text/javascript">
if(typeof(jQuery) != "undefined")
(function(NS, $) {
	//  __________________________________________
	// /---------- Initialize namespace ----------\
	(window[NS]) || (window[NS] = {});
	var $ns, ns = window[NS];
	// \__________________________________________/

	//  _[ Initialize ]___________________________________
	// |                                                  |
	// | One-time initialization of namespace             |
	// |__________________________________________________|
	ns.initialize = function() {
		// Move resources that partial spit out into the head so that they don't have to be sent again
		$("#content style[location=head], #content script[location=head]").appendTo("head:first");
	};

	$(ns.initialize);
})("head", jQuery);
</script>
