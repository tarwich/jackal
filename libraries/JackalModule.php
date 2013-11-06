<?php

/**
 * The core of all Jackal modules.
 * 
 * This is the core that all folder based modules extend. It provides some basic 
 * functionality, nothing fancy. If you would like to have this functionality
 * in a class-based or file-based module, you would extend JackalModule. 
 * 
 * @author SammyD
 *
 */
class JackalModule {
	/**
	 * Cached content-actions from settings
	 * 
	 * @var array
	 */
	static $actions = null;
	
	/** 
	 * Cached mime-types from settings
	 * 
	 * @var array
	 */
	static $contentTypes = null;
	
	/**
	 * The glob for the resources folder(s). 
	 * 
	 * This is passed to Jackal::files() in order to find the files requested.
	 * This defaults to the "resources" folder inside your module.
	 * 
	 * Config setting: resource-path
	 * 
	 * @var string
	 */
	public static $DEFAULT_RESOURCE_PATH = "<ROOT>/{<LOCAL>/,<JACKAL>/}{modules/<MODULE>/,}resources/{,<TYPE>}<FILE>";
	
	/**
	 * Return the resource specified in the request
	 * 
	 * This method looks in the resource path for the file in the HTTP request 
	 * or $URI[0] and outputs the contents to the browser. It uses $actions in
	 * order to determine the method used to output the file. 
	 * 
	 * This method allows your modules to have a resources folder that you serve
	 * files out of without allowing users to circumvent the system. This method 
	 * will look for the file in $DEFAULT_RESOURCE_PATH and output it to the 
	 * browser.
	 * 
	 * In order to use this method, you would link to YourModule/resources/foo.gif
	 * where foo.gif is the name of the file you're linking to.
	 * 
	 * By default, this module will also check folders with the extension of 
	 * the resource you're looking for. This means that resources/foo.js will 
	 * be searched for in resources/js/foo.js and resources/foo.js. This allows
	 * you to have a clean structure for all your files, but keep the url clean
	 * at the same time.
	 * 
	 * @param array $URI[0] The file to send
	 * 
	 * @return void
	 */
	public function resources($URI) {
		// Make URI into the old-style uri
		$URI = $this->toURI(func_get_args());
		// If the template module exists, then disable it
		//Jackal::call("Template/change/Template/AJAX");
		Jackal::call("Template/disable");

		// Get the segments
		$segments = array_intersect_key($URI, array_values($URI));
		// Get the (file) passed into the request
		$file = end($segments);
		// Get the type
		$type = @end(explode(".", $file));
		// Load the content types
		self::$contentTypes ?: (self::$contentTypes = Jackal::setting("jackal/mime-types"));
		// Resolve the content-type
		$contentType = @self::$contentTypes[$type];
		if(!$contentType) $contentType = "text/plain";
		
		// Enable compression
		if(!headers_sent()) 
		if(strtok($contentType, "/") == "text") Jackal::call("Template/enable-compression");
		else Jackal::call("Template/disable-compression");
		
		// Get the age based on HTTP cache requirements
		$age=60*60*24*365;
		
		if(!headers_sent()) {
			// Spit out the content-type early-on
			header("Content-type: ".$contentType);
			// Cache-control headers
			header("Cache-Control: max-age=$age");
		} 
		
		// Get the module NAME
		$moduleName = get_class($this);
		// Reassemble the file from the URI
		$file = @join("/", $segments);
		// LPK ~ Include the $URI in the resource
		self::$actions ?: self::$actions = Jackal::setting("jackal/content-actions");
		$action = create_function('$a,$URI=null', @self::$actions[$type].";");
		// Will be used for freshness
		date_default_timezone_set("GMT");
		
		// Get the theme
		$theme = Jackal::setting("theme");
		if($theme != "default") $theme = "{"."$theme,default}";
		
		if(Jackal::getErrorLevel() == 403) {
			header("Access denied", true, 403);
			
			// Handle 403 resources
			$replaceables = array(
				"MODULE" => $moduleName,
				"TYPE" => $type,
				"FILE" => "403/403.$type",
				"THEME" => Jackal::setting("theme"),
			);
		} else {
			// Setup things that can be replaced in the module path setting
			$replaceables = array(
				"MODULE" => $moduleName,
				"TYPE" => $type,
				"FILE" => $file,
				"THEME" => $theme,
			);
		}
		
		// Load the pattern used to find resource files
		$searchPattern = Jackal::setting("resource-path", self::$DEFAULT_RESOURCE_PATH);
		
		// Find the files we're looking for
		$files = Jackal::files($searchPattern, $replaceables);
		
		// Use the found files
		foreach($files as $file) {
			if(!headers_sent()) {
				// Freshness
				header("Expires: " . date("D, j M Y H:i:s T", time()+$age));	// Last-Modified: Mon, 29 Jun 1998 02:28:12 GMT
				header("Last-Modified: " . date("D, j M Y H:i:s T", filemtime($file)));	// Last-Modified: Mon, 29 Jun 1998 02:28:12 GMT
			}
			
			// LPK ~ include the URL in the resource
			$action($file, $URI);
			$include = true;
			
			// Include files that begin with +
			foreach(preg_grep('/^\+/', $files) as $file) $action($file);
			
			// ...and quit
			return;
		}
		
		//
		// Find a 404 file to send
		//
		
		// Setup things that can be replaced in the module path setting
		$replaceables = array(
			"MODULE" => $moduleName,
			"TYPE" => $type,
			"FILE" => "404/404.$type",
			"THEME" => Jackal::setting("theme"),
		);
		
		// Find the files we're looking for
		$files = Jackal::files($searchPattern, $replaceables);
		
		// Use the found files
		foreach($files as $file) {
			// Freshness
			$mTime = filemtime($file);
			header("Last-Modified: " . date("D, j M Y H:i:s T", $mTime));	// Last-Modified: Mon, 29 Jun 1998 02:28:12 GMT
			header("HTTP/1.0 404 File not found");
			$action($file);
			return;
		}
		
		// Do the 404
		header("HTTP/1.0 404 File not found");
		echo "404";
	}
	
	/**
	 * Convert argument functions to the old-style URI
	 * 
	 * This method will take an array and flatten all the values into one array
	 * 
	 * <code language='php'>
	 * 	// This call
	 * 	toURI(array("a", "b", array("c" => "d"), array("x", "y")))
	 * 	// Returns
	 * 	array(
	 * 		"0" => "a",
	 * 		"1" => "b",
	 * 		"c" => "d",
	 * 		"2" => "x",
	 * 		"3" => "y"
	 * 	);
	 * </code>
	 * 
	 * @param  array   $arguments The arguments to convert
	 * 
	 * @return array              The URI
	 */
	public static function toURI($arguments) {
		// Convert all arguments to arrays
		foreach($arguments as $k=>$v) $arguments[$k] = is_array($v) ? $v : array($v);
		// Flatten arguments into one array (only if >1 value)
		if($arguments) $arguments = call_user_func_array("array_merge", $arguments);
		
		return $arguments;
	}
}
