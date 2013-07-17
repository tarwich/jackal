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
	 * A map from file type to mime type
	 * 
	 * This is used by resources() to output the correct HTTP header for the 
	 * resource requested
	 * 
	 * @var array
	 */
	protected static $contentTypes = array(
		"css"	=> "text/css",
		"js"	=> "text/javascript",
		"gif"	=> "image/gif",
		"jpg"	=> "image/jpeg",
		"jpeg"	=> "image/jpeg",
		"png"	=> "image/png",
		"zip"	=> "application/zip",
		"ttf"	=> "application/x-font-ttf",
		"xml"	=> "text/xml",
		"swf"	=> "application/x-shockwave-flash",
		"svg"	=> "image/svg+xml", 					// Strange, FF likes this one
//		"svg"	=> "application/xhtml+xml",				// Chrome likes this one and not the other
		"mp3"	=> "audio/mpeg3"
	);
	
	/**
	 * Array of actions to show how to handle each file type.
	 * 
	 * This array contains the code to execute for each filetype. Some files 
	 * are included in order to enable PHP inside that file, while others are 
	 * sent directly to the browser in order to reduce overhead.
	 * 
	 * @var array
	 */
	protected static $actions = array(
		"css"	=> 'include($a)',
		"js"	=> 'include($a)',
		"gif"	=> 'readfile($a)',
		"jpg"	=> 'readfile($a)',
		"jpeg"	=> 'readfile($a)',
		"png"	=> 'readfile($a)',
		"zip"	=> 'readfile($a)',
		"ttf"	=> 'readfile($a)',
		"xml"	=> 'readfile($a)',
		"swf"	=> 'readfile($a)',
		"svg"	=> 'readfile($a)',
		"mp3"	=> 'readfile($a)',
	);
	
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
	 * Consider all the parameters in URI and return an array of parameters as
	 * requested by the caller.
	 * 
	 * If a parameter is not provided, then it will not appear in the result 
	 * array unless a default is provided for that parameter.
	 * 
	 * @param array $URI 		The URI to parse
	 * @param array $parameters Associative array where the each is the name of 
	 * 							the resulting variable, and each value is either
	 * 							a string with the name of the alias for that 
	 * 							variable, or an associative array with rules on
	 * 							how to parse that variable. The available keys
	 * 							for the rules array are as follows:  
	 * 
	 * @param $rules[aliases]  An array of names for this variable
	 * @param $rules[default]  The default value of this variable
	 * @param $rules[type]     The type that this variable should be
	 * @param $rules[required] True (or string to throw upon error) if this is 
	 * 						   a required parameter.  
	 * @return array The resulting array where keys are the names of the 
	 * 			     parameters.
	 */
	public function parseParameters($URI, $rules) {
//		echo "<pre>".htmlentities(print_r(func_get_args(), 1))."</pre>";
		// Initialize the return values
		$result = array();
		
		// Map the rule aliases
		foreach($rules as $ruleName=>$rule) {
			// Save the name as part of the rule
			$rule["name"] = $ruleName;
			// Create an array mapping the aliases to the rules
			$aliases = array_fill_keys((array) @$rule["aliases"], $rule);
			// Add the rule as an alias
			$aliases[$ruleName] = $rule;
			// Add the default value to the result
			if(isset($rule["default"])) $result[$ruleName] = $rule["default"];
		} 
		
		foreach($URI as $name=>$value) {
			// Get the rule for this parameter
			$rule = @$aliases[$name];
			
			// The rule will be empty if not found 
			if($rule) {
				// Typecast
				if(@$rule["type"]) settype($value, $rule["type"]);
			} 
		}
		
		return (array) $result;
	}
	
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
		// If the template module exists, then disable it
		//Jackal::call("Template/change/Template/AJAX");
		Jackal::call("Template/disable");

		// Get the segments
		$segments = array_intersect_key($URI, array_values($URI));
		// Get the (file) passed into the request
		$file = end($segments);
		// Get the type
		$type = @end(explode(".", $file));
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
		$action = create_function('$a,$URI', @self::$actions[$type].";");

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
}

?>
