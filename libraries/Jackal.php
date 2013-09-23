<?php

$GLOBALS["start"] = microtime(true);

class Jackal {
	/**
	 * Case sensitive list of flags from the URL
	 *
	 * @var array
	 */
	private static $_caseFlags = array();
	
	/**
	 * Internal cache of class instances so that subsequent calls to
	 * Jackal::call() or Jackal::loadLibrary() can return the previous instance.
	 *
	 * @var array
	 */
	private static $_classes = array();
	
	/**
	 * The current url that invoked the script
	 *
	 * When a a request is received, one of the first things that Jackal does
	 * is store the url in this variable. This allows modules to react to that
	 * url in their own way. The purpose for this is primarily for outputting
	 * navigation information.
	 *
	 * In order to access this variable, you should call currentURL()
	 *
	 * @var string
	 */
	private static $currentURL = "";
	
	/**
	 * The glob used to find classes
	 *
	 * This is the default path used to find classes, and is the recommended
	 * path for application layouts. However, this is only a backup, and is
	 * meant to be set in the configuration file(s). It is set in the Jackal
	 * configuration, and can be overridden in any configuration file.
	 *
	 * This can be overridden in the configuration as "class-path"
	 *
	 * @var string
	 */
	private static $DEFAULT_CLASS_PATH = "<ROOT>/{<LOCAL>,<JACKAL>}/{<OTHER>modules,libraries}/{<MODULE>,<MODULE>.php}";
	
	/**
	 * The default <MY_> prefix
	 *
	 * By default, when you want to override a configuration file or any other
	 * file, for that matter, you prefix the new file with "MY_". This is the
	 * variable that holds that value.
	 *
	 * This can be overridden in the configuration as "custom-prefix"
	 *
	 * @var string
	 */
	private static $DEFAULT_CUSTOM_PREFIX = "MY_";
	
	/**
	 * The default jackal directory
	 *
	 * The only purpose for this variable is (1) if the configuration file has
	 * not been loaded, or (2) if the variable is not set in the configuration
	 * file.
	 *
	 * This can be overridden in the configuration as jackal-dir
	 *
	 * @var string
	 */
	private static $DEFAULT_JACKAL_DIR = "jackal";

	/**
	 * The default <LOCAL> directory
	 *
	 * The only purpose for this variable is (1) if the configuration file has
	 * not been loaded, or (2) if the variable is not set in the configuration
	 * file.
	 *
	 * This can be overridden in the configuration as local-dir
	 *
	 * @var string
	 */
	private static $DEFAULT_LOCAL_DIR = "private";
	
	/**
	 * Set by Jackal::error() to store the error error level introduced by the
	 * previous call.
	 *
	 * @var integer
	 */
	private static $_error_level = 0;
	
	/**
	 * Case insensitive list of flags from the URL
	 *
	 * @var array
	 */
	private static $_flags = array();
	
	/**
	 * Private hash map of functions for getFunction()
	 *  
	 * @var array
	 */
	private static $_functions = array();
	
	/**
	 * Internal array used by Jackal::info() to store calculated information
	 * about the system, such as BASE_DIR.
	 *
	 * When info() is called, it uses this array to cache the results.
	 *
	 * @var array
	 */
	private static $info = NULL;
	
	/**
	 * Internal cache of model class instances so that subsequent calls to
	 * Jackal::model() use the same instance.
	 *
	 * @var array
	 */
	private static $_models = array();
	
	/**
	 * The version of this Jackal distribution
	 * 
	 * @var float 
	 */
	public static $VERSION = "1.3.0";
	
	/**
	 * Internal array of scope hierarchy
	 *
	 * Every time call() is executed, the call is added to the scope tree, so
	 * that other modules can tell who they were called from, or what the entry
	 * level scope was.
	 *
	 * You should not access this variable directly, but instead use
	 * Jackal::scope(). This variable is for internal purposes and may change
	 * without notice.
	 *
	 * @var array
	 */
	private static $_scope = array();
	
	/**
	 * Internal array used by Jackal to store the settings loaded by all the
	 * configuration files in the system.
	 *
	 * You should not access this structure directly. In order to get settings,
	 * you should call Jackal::setting(). This array <b>WILL</b> change in the
	 * future.
	 *
	 * @var array
	 */
	private static $_settings = NULL;

	/**
	 * Internal variable used by Jackal to prevent double-starting
	 *
	 * @var boolean
	 */
	private static $_started = false;

	/**
	 * Internal cached version of the base url.
	 *
	 * When siteURL() is first called, it checks the url for things like port
	 * number and https. Then subsequent calls reuse this information. The
	 * entire first part of the url is stored here and only the actual message,
	 * flags, and segments change
	 *
	 * @var string
	 */
	private static $_urlPrefix = "";
	
	
	/**
	 * Internal function used to prevent Jackal instantiation
	 *
	 * Jackal is not meant to be instantiated. It is not a singleton, it is a
	 * static class. This method is meant to aid in development by pointing out
	 * accidental instantiations
	 */
	public function __construct() {
		Jackal::error("500", "Cannot instantiate singleton class ".__CLASS__);
	}

	/**
	 * Invoke an object / method
	 *
	 * This is the core of Jackal. When an request is received, this method is
	 * called. When you want to invoke another object, you should use call() in
	 * order to allow Jackal to instantiate the object, and perform any other
	 * necessary actions such as triggers.
	 *
	 * $method should be written as Module/method and may contain additional
	 * segments in the form of Module/method/segment1/segment2. This is called 
	 * the message, because it reflects the message sent in MVC between 
	 * components.
	 *
	 * $data is an associative array that will be merged into $URI in the
	 * destination method. However, $data can simply be a regular array.
	 *
	 * If this method is called with a non-array value in $data, or with more
	 * than two arguments, then the arguments are used as segments.
	 *
	 * call() will return the result of the destination method, or true, of the
	 * destination method does not return anything.
	 *
	 * When calling methods in your own object, such as $this->something(), you
	 * should consider the implications of Jackal::call(). You can call your
	 * own object the same way you would externally and this will allow Jackal
	 * to handle things such as triggers. However, if the method is trivial,
	 * then you might not want the overhead of Jackal::call().
	 * 
	 * @example A simple call to execute method bar() of module Foo
	 * 
	 * <code type="php">
	 * Jackal::call("Foo/bar");
	 * </code>
	 * 
	 * This example is equivalent to writing:
	 * <code type="php">
	 * include("Foo.php")
	 * $foo = new Foo();
	 * $foo->bar();
	 * </code>
	 * 
	 * @example A call passing segments
	 * 
	 * <code type="php">
	 * Jackal::call("Foo/bar/baz/bin");
	 * </code>
	 * 
	 * This example is equivalent to writing:
	 * <code type="php">
	 * include("Foo.php");
	 * $foo = new Foo();
	 * $foo->bar("baz", "bin");
	 * </code>
	 * 
	 * @example A call passing an associative array and segments
	 * 
	 * <code type="php">
	 * Jackal::call("Foo/bar/baz/bin", array("test" => "asdf"));
	 * </code>
	 * 
	 * @example A call that passes segments in variant arguments
	 * <code type="php">
	 * Jackal::call("Foo/bar", "one", "two");
	 * // Or 
	 * Jackal::call("Foo/bar/one/two", "three", "four");
	 * </code>
	 * 
	 * @param string $method "Object/action" string
	 * @param array $data (optional) Associative array of parameters to pass
	 * 		into the destination method
	 * @return mixed The return value of the called script
	 */
	public static function call($method, $data=array()) {
		@list($module, $action, $segments) = explode("/", $method, 3);
		
		// $module doesn't exist, try using the default module
		if($module && (!@self::getClass($module))) {
			// Shift everybody back one and reassign
			list($module, $action, $segments) = array(
				self::$_settings['jackal']['default-module'], // Module
				$module,                                      // Action
				$action . '/' . $segments                     // Segments
				);
		}

		// Improvise with default action
		($module) || ($module = @self::$_settings["jackal"]["default-module"]);
		($action) || ($action = @self::$_settings["jackal"]["default-action"]);

		// Get the variant arguments
		$arguments = func_get_args();
		// First argument not real
		array_shift($arguments);
		// Initialize URI
		$URI = array();

		//
		// Assign segments
		//
		// Because we might need a slash in one of the segments
		foreach(explode("/", $segments) as $segment) {
			if($segment !== "") {
				$URI[] = urldecode($segment);
			}
		}

		//
		// Assign ordered arguments
		//
		// Add the variant arguments into the array
		if(!is_array(@$arguments[0])) $URI = array_merge($URI, $arguments);
		elseif(count($arguments) > 1) $URI = array_merge($URI, $arguments);
		// Added to provide compatibility for old $URI[segments]

		//
		// Incorporate named arguments
		//
		// See if there is a first argument
		if(is_array(@$arguments[0]))
		// See if the first argument is an associative array
		if(array_values(@$arguments[0]) !== @$arguments[0]) {
			// Add the key/value pairs to the URI
			$URI += @$arguments[0];
		} else {
			// Add the key/value pairs to the URI
			$URI += @$arguments;
		}
		//$URI["segments"] = (array) @array_intersect_key($URI, array_values($URI));

		// Remember the current scope
		self::$_scope[] = array($module, $action);
		
		// Gather pre-triggers
		$triggers = @array_merge( 
			(array) self::$_settings["jackal"]["triggers"]["pre"]["$module/$action"],
			(array) self::$_settings["jackal"]["triggers"]["pre"]["$module/*"],
			(array) self::$_settings["jackal"]["triggers"]["pre"]["*/$action"],
			(array) self::$_settings["jackal"]["triggers"]["pre"]["*/*"]
		);
		// Remove the current method from the trigger list
		$triggers = array_diff($triggers, array($method));
		
		// Run pre-triggers
		foreach($triggers as $trigger) {
			@list($triggerObject, $triggerMethod) = explode("/", $trigger);
			self::$_scope[] = array($triggerObject, $triggerMethod);
			extract((array) self::executeMethod(self::getClass($triggerObject), $triggerMethod, $URI)); 
			array_pop(self::$_scope);
		}

		// Find the object that this belongs to
		$object = Jackal::getClass($module);
		
		// Execute the method
		$result = Jackal::executeMethod($object, $action, array(  (array) $URI));

		// Gather post-triggers
		$triggers = @array_merge(
			(array) self::$_settings["jackal"]["triggers"]["post"]["$module/$action"],
			(array) self::$_settings["jackal"]["triggers"]["post"]["$module/*"],
			(array) self::$_settings["jackal"]["triggers"]["post"]["*/$action"],
			(array) self::$_settings["jackal"]["triggers"]["post"]["*/*"]
		);
		// Remove the current method from the trighger list
		$triggers = array_diff($triggers, array($method));
		// Run post-triggers
		foreach($triggers as $trigger) extract((array) self::call($trigger, $URI));

		// Discard the current scope
		array_pop(self::$_scope);

		return $result;
	}
	
	/**
	 * Make a class out of a directory
	 *
	 * This is an internal function used by Jackal to build a class from a
	 * directory. However, this function is made public in order to allow
	 * developers to extend Jackal functionality. For example, if you want to
	 * create an object out of a subfolder inside your module.
	 *
	 * Each file in the folder $path will be added as a method of the class.
	 * The logic for adding these files is as follows:
	 *
	 * <b>public</b> By default every file will be added as a public instance
	 * method. The name of the file will be serialize such that s/\W+/_/g
	 *
	 * <b>private (_)</b> Any file that begins with an underscore will be added
	 * as a private instance method. The method will have code at the beginning
	 * that converts the arguments to a URI, because you can't use
	 * Jackal::call() to call a private method. The exception is a file that
	 * begins with two underscores (__).
	 *
	 * <b>ignored (~)</b> Any file that begins with a ~ will be ignored
	 *
	 * <b>magic (__)</b> Magic methods are added as-is.
	 *
	 * <b>__extends</b> In order to cause your class to extend another class
	 * you would create a special file in the folder. The file will be empty,
	 * and will be named "__extends OtherClass" (without the quotes). This file 
	 * should not have an extension (such as .php).
	 *
	 * @param string $name Name of the class to create
	 * @param string $path Directory to make the class out of
	 * @param string $base Base class to extend or blank for no base
	 */
	public static function createClass($parameters, $path="", $base="") {
		extract(Jackal::url2uri($parameters, "name"));

		if(!assert('$name')) return false;

		$files = glob($path."/*.php");
		$methods = array();

		foreach($files as $file) {
			// Internal use only
			if($file[0] == "~") continue;
			// Add slashes
			$file_ = addslashes($file);

			$methodName = str_replace(".php", "", basename($file));
				
			// If the file begins with _, but not __, then method access is private
			if(substr($methodName, 0, 2) == "__") {
				$access = "";
				$arguments = "";
				$code = "";
			} elseif($methodName[0] == "_") {
				$access = "private";
				$arguments = "\$URI=array()";
				$code = "\$URI = func_get_args();";
			} elseif($methodName[0] == "#") {
				$access = "protected";
				$arguments = "\$URI=array()";
				$code = "";
			} else {
				$access = "public";
				$arguments = "\$URI=array()";
				$code = "if(func_num_args() > 1) \$URI = func_get_args();";
			}
				
			// By default, methods are not static
			$static = "";
			// If the file ends with an _, then method is static
			($methodName[strlen($methodName)-1] == "_") && ($static = "static");
				
			// Remove non-alphanumeric characters and replace with underscores
			$methodName = preg_replace('-\W-', "_", $methodName);
			// Build the method
			$methods[] = <<<END
			$access $static function $methodName($arguments) {
				$code
				\$result = include("$file_");
				return \$result;
			}
END;
		}
		
		// Allow inheritance to be gleaned from the file system
		($file = @array_pop(self::files("$path/__extends *"))) && ($base = @end(explode(" ", basename($file), 2)));

		// Make sure that a base was specified
		($base) || ($base = "stdclass");
		// Load the class if necessary
		if(!class_exists($base)) Jackal::loadLibrary($base);

		$methodData = implode("\n", $methods);
		$classStructure = " class $name extends $base { $methodData } ";

		assert('!class_exists($name)');

		eval($classStructure);

		//			$object = new $name();
		//			self::$_classes[$name] =& $object;
		//			return $object;
	}

	/**
	 * Get the URL that is currently being visited
	 *
	 * @example A standard call to currentURL
	 * 
	 * <code type="php">
	 * echo Jackal::currentURL(); // http://www.example.com/Test.php
	 * </code>
	 * 
	 * @return string the URL that is currently being visited
	 */
	public static function currentURL() {
		return self::$currentURL;
	}

	/**
	 * Returns true if Jackal is in debug mode
	 * 
	 * At the start of every script Jackal checks the url to see if the DEBUG 
	 * flag is set. If so, it turns on {@link http://www.php.net/display_errors display_errors} 
	 * and {@link http://www.php.net/assert assertions}.
	 * 
	 * Config setting:
	 * <code type="yaml">
	 * jackal:
	 * 		debug-path: DEBUG
	 * </code>
	 * 
	 * @return boolean true if Jackal is in debug mode
	 */
	public static function debugging() {
		return $GLOBALS["jackal-debug"];
	}

	/**
	 * See if debugging should be turned on and do so
	 *
	 * This is an internal method used to see if debugging should be turned on
	 * and turn it on. This looks up the setting for the debugging flag and if
	 * the flag is present in the url, debugging is turned on.
	 *
	 * Config setting: 
	 * <code type="yaml">
	 * jackal:
	 * 		debug-path: DEBUG
	 * </code>
	 *
	 * @return void
	 */
	private static function _debuggingCheck() {
		if(self::flag("DEBUG")) {
			$debug = $GLOBALS["jackal-debug"] = true;
			
			ini_set("display_errors", 1);
			ini_set("display_startup_errors", 1);
			error_reporting(E_ALL | E_STRICT);
		} else {
			$debug = $GLOBALS["jackal-debug"] = false;
			
			ini_set("display_errors", 0);
			error_reporting(E_ALL);
		}

		assert_options(ASSERT_ACTIVE,      $debug);
		assert_options(ASSERT_WARNING,    $debug);
		assert_options(ASSERT_BAIL,       $debug);
		assert_options(ASSERT_QUIET_EVAL, !$debug);
	}

	/**
	 * Generate an error
	 *
	 * Generates a php error, and sends the correct HTTP header with the
	 * response code. If $die is true, then the application will quit after
	 * sending the error. An optional $userMessage can be provided in order to
	 * send a message in the body of the html page as a status.
	 * 
	 * @example Throw an error for a file not found problem
	 * 
	 * <code type="php">
	 * Jackal::error(500, "Cannot find the file specified: $file");
	 * </code>
	 * 
	 * @example Throw an error for a not implemented problem along with a user message, and stop script execution
	 * 
	 * <code type="php">
	 * Jackal::error(
	 * 		// HTTP 501 = Not Implemented 
	 * 		501,	
	 * 		// The error for the error log or debug mode
	 * 		"Not implemented: $method", 
	 * 		// The non-debug mode error to show in the browser (to the user)
	 * 		"There was an error processing your request", 
	 * 		// Bail
	 * 		true 
	 * );
	 * </code>
	 *
	 * @param int $code The HTTP response code to send
	 * @param string $message The message of the error
	 * @param string $userMessage The message to send to the user
	 * @param boolean $die True if the application should bail
	 * 
	 * @return the new error level
	 */
	public static function error($code, $message, $userMessage="", $die=false) {
		//(self::debugging()) ||
		headers_sent() ||
		header("HTTP/1.1 $code $message");

		// Store the error level for other error handlers
		Jackal::set_error_level($code = (int) $code);
		// Throw an error so that other error handlers can trap it
		trigger_error($message, E_USER_WARNING);

		// Show the userMessage
		if($userMessage) {
			?>
<div class="error">
<div class="title">Error</div>
<div class="information"><?php echo $userMessage; ?></div>
</div>
			<?php
		}
		
		// Die
		if($die) die();
		// Return the new error level
		return $code;
	}

	/**
	 * Execute a method of an object
	 *
	 * Internal function used by Jackal::call() in order to actually execute
	 * the method. This function is made public in order to allow other modules
	 * to utilize this functionality if needed. However, it is recommended to
	 * use Jackal::call() if at all possible.
	 * 
	 * @example A sample usage of this method 
	 * 
	 * <code type="php">
	 * Jackal::executeMethod($objectInstance, "method", array("one", "two"));
	 * </code>
	 *
	 * @param object $object The object who's method to execute
	 * @param string $methodName The method to execute
	 * @param array $parameters The parameters to pass to the method
	 * 
	 * @return void
	 */
	public static function executeMethod($object, $methodName, $parameters) {
		$methodName = str_replace(".php", "", basename($methodName));
		$methodName = preg_replace('-\W-', "_", $methodName);

		if(method_exists($object, $methodName)) {
			return call_user_func_array(array($object, $methodName), $parameters);
		} else {
			if(error_reporting())
			Jackal::error(404, get_class($object) . " does not know how to $methodName", "The page you requested could not be found.", true);
		}
	}
	
	/**
	 * Expand a path, replacing &lt;FOO&gt; items with their values
	 * 
	 * This method looks for things like &lt;JACKAL&gt; and replaces them with their actual values.
	 * 
	 * @example Options:
	 * <code language='yaml'>
	 * ROOT  : The path to the site root
	 * JACKAL: The path to the /jackal/ folder
	 * LOCAL : The path to the /private/ folder
	 * </code>
	 * 
	 * @param string $path The path to expand
	 * 
	 * @return string
	 */
	public static function expandPath($searchPath, $additionalReplaceables=array()) {
		// Setup replaceables
		$replaceables = $additionalReplaceables + array(
			"JACKAL" => Jackal::setting("jackal-dir", self::$DEFAULT_JACKAL_DIR),
			"LOCAL"  => Jackal::setting("local-dir", self::$DEFAULT_LOCAL_DIR),
			"ROOT"	 => Jackal::info("BASE_DIR"),
			"MY" 	 => Jackal::setting("custom-prefix", self::$DEFAULT_CUSTOM_PREFIX),
		0);

		// Prevent the folder/ error
		$searchPath = rtrim($searchPath, "/\\");
		// Build the glob
		return @preg_replace('/<([\w\-]+)>/e', '$replaceables["$1"]', $searchPath);		
	}

	/**
	 * Find the files in the path specified
	 *
	 * Find the file that corresponds to $searchPath. This method will find the
	 * local file and the Jackal file and return then in an array in the order
	 * that they should be implemented.
	 *
	 * This method executes a glob with optional substitutions. The default
	 * substitutions are as follows:
	 *
	 * <b>&lt;JACKAL&gt;</b>: Becomes the path from the "jackal-dir" setting()
	 *
	 * <b>&lt;LOCAL&gt;</b>: Becomes the path from the "local-dir" setting()
	 *
	 * <b>&lt;ROOT&gt;</b>: Becomes the root of the site, from BASE_DIR info()
	 *
	 * <b>&lt;MY&gt;</b>: Becomes the prefix from "custom-prefix" setting()
	 *
	 * @example Get all the files in the Private folder
	 * 
	 * <code type="php">
	 * $files = Jackal::files("<ROOT>/<PRIVATE>/"."*");
	 * </code>
	 * 
	 * @example Get all the files in the attachments folder inside the Foo module
	 * 
	 * <code type="php">
	 * $files = Jackal::files(
	 * 		Jackal::setting("class-path")."/attachments/"."*", 
	 * 		array("MODULE" => "Foo")
	 * );
	 * </code>
	 *
	 * @param string $searchPath The path to search
	 * @param array $additionalReplaceables Any replaceables other than the
	 * 		defaults.
	 * @param boolean $d If true, this method will echo the $glob before
	 * 		executing it. This is useful for debugging applications.
	 * 
	 * @return array Array of files found
	 */
	public static function files($searchPath, $additionalReplaceables=array(), $d=false) {
		// Expand the path items like <ROOT> and <JACKAL>
		$glob = self::expandPath($searchPath, $additionalReplaceables);
		// Debugging
		($d) && (print($glob));

		// Perform the find
		$files = (array) array_keys(array_flip(glob($glob, GLOB_BRACE)));

		return $files;
	}

	/**
	 * Returns true if there is a flag set in the request with $name
	 *
	 * All the settable flags are defined in the configuration. Jackal will 
	 * parse the flags in the request and remove them afterwords, leaving the 
	 * request with just the actual object/method message.
	 * 
	 * If you pass true to $isset, then it will mark the flag as being set (or
	 * not set) and will return the new value. 
	 *
	 * Config setting: 
	 * <code type="yaml">
	 * jackal:
	 * 		flaggers: [ajax, partial]
	 * </code>
	 * 
	 * @example See if the ajax flag is set
	 * <code type="php">
	 * echo Jackal::flag("ajax"); // true | false
	 * </code>
	 * 
	 * @param string $name the name of the flag to search for
	 * @param boolean $isset true if the flag should be set
	 *
	 * @return boolean
	 */
	public static function flag($name=NULL, $isset=NULL) {
		// Set the flag if the isset parameter is true
		if($isset !== NULL) self::$_flags[$name] = $isset;
		// If $name is null (not provieded), then return all the flags
		if($name === NULL) return self::$_flags;
		// Return the value of the flag
		return @self::$_flags[strtoupper($name)] ? true : false;
	}

	/**
	 * Finds all the flags in the URI, removes them, and sets them in
	 * Jackal::$flags
	 * 
	 * This function is an internal function used by Jackal when it first 
	 * receives a request. 
	 *
	 * @return void
	 */
	private static function flagCheck() {
		$flaggers = (array) Jackal::setting("flaggers");
		$flaggers[] = Jackal::setting("debug-path");
		// Get the flaggers and querystring parts of the URL
		preg_match(',((?:'.implode("|", $flaggers).'|/)*)(.*$),', $_SERVER["QUERY_STRING"], $components);
		// Get the flags and url from components
		@list(,$flags, $url) = $components;
		// Push the URL back to the environment
		$_SERVER["QUERY_STRING"] = $url;
		// Store the flags
		self::$_caseFlags = array_filter(explode("/", $flags));
		self::$_caseFlags = (array) @array_combine((array) @self::$_caseFlags, (array) @self::$_caseFlags);
		// Add CLI flag
		if(php_sapi_name() == "cli") self::$_caseFlags[] = "CLI";
		// Add CLI DEBUG flag
		if(@$_ENV["DEBUG"]) self::$_caseFlags[] = "DEBUG";
		// Remove empty flags
		self::$_caseFlags = array_filter(self::$_caseFlags);
		self::$_flags = array_map('strtoupper', self::$_caseFlags);
		self::$_flags = (array) @array_combine((array) @self::$_flags, (array) @self::$_flags);
		// Remove empty flags
		self::$_caseFlags = array_filter(self::$_caseFlags);
	}

	/**
	 * Find the class, load its file, and return a new instance
	 * 
	 * This is an internal function that finds the class file for a file-based 
	 * class such as a library and loads it. If the class is a folder based 
	 * class such as a module, then the this method calls createClass(). 
	 * 
	 * Once the class exists, it is instantiated and the instance is returned.
	 * If an instance already exists, then the previous instance is returned.
	 * 
	 * You should not call this function direction, but instead call loadLibrary()
	 *
	 * @param string $className Name of the class to load
	 * @param string $otherDir Used to allow callers to specify additional paths
	 * 		to look for modules. This is here for future use
	 *
	 * @return object A reference to the class instance
	 */
	public static function &getClass($className, $otherDir="") {
		// Make sure the class name begins with a capital letter
		$className = ucfirst($className);
		
		// Jump out if we've cached the class
		if(isset(self::$_classes[$className])) return self::$_classes[$className];

		// Make sure the module class is loaded
		if($className != "JackalModule") Jackal::loadLibrary("JackalModule");

		// Hack for classes that already exist some way or another
		if(!class_exists($className)) {
			// Make a list of the things that might be replaceable in the path
			$replaceables = array(
				"OTHER" 	=> $otherDirs = join(",", (array) $otherDir),
				"MODULE" 	=> $className,
			0);

			// Find the file(s)
			$files = Jackal::files(Jackal::setting("class-path", self::$DEFAULT_CLASS_PATH), $replaceables);

			// No files found
			if(!count($files)) {
				// If we're trying to be quiet, then show the minimum information
				if(@self::$_settings["super-quiet-mode"]) {
					if(error_reporting()) Jackal::error(501, "$className does not exist", NULL, true);
					self::$_classes[$className] = new stdClass();
				}

				// Otherwise, show helpful information about the problem
				else {
					// Tell the user what happen
					if(error_reporting()) Jackal::error(501, "$className does not exist", "Jackal was unable to find class $className");
					self::$_classes[$className] = false;
				}
				
				return self::$_classes[$className];
			}
			
			// Is the actual class file
			elseif(is_file($files[0])) {
				include($files[0]);
			}
			
			// Is the directory for the class file
			else {
				// And has the class definition file in it
				if(file_exists($file="$files[0]/~class.php")) {
					include($file);
				} elseif(file_exists($file="$files[0]/$className.php")) {
					include($file);
				} else {
					Jackal::createClass($className, $files[0], "JackalModule");
				}
			}
		}
		
		// Debug - make sure the class exists
		assert('class_exists("'.$className.'")');
		
		// Get information about the class
		$reflection = new ReflectionClass($className);
		// Don't instantiate abstract classes
		if($reflection->isAbstract()) $object = true;
		// Class found... Instantiate, store, and return
		else $object = new $className();
		// Cache the result
		self::$_classes[$className] = $object;
		
		// Call the autorun function if available
		if(method_exists(self::$_classes[$className], "autorun"))
		call_user_func(array(self::$_classes[$className], "autorun"));
		
		return self::$_classes[$className];
	}
	
	/**
	 * This method is deprecated 
	 *  
	 * Find the directory for a module and return the absolute path
	 * 
	 * Provided the name of a module, this method returns the path to that 
	 * module in the filesystem. The purpose of this method is to find a module 
	 * folder in order to look for files in it. This is meant to allow modules 
	 * to access each others' files without knowing about the structure of the 
	 * file system.
	 * 
	 * Note that it is best to try to avoid accessing files directly if there
	 * is another solution. It is also important to try to access files from 
	 * other modules as little as possible.
	 * 
	 * @param string $className The name of the library or module to find the
	 * 		path of. 
	 * 
	 * @return string The path to the desired library or module
	 * 
	 * @deprecated This method is deprecated. Instead use files();
	 */
	public static function getModuleDir($className) {
		// Make a list of the things that might be replaceable in the path
		$replaceables = array(
			"OTHER" 	=> "",
			"MODULE" 	=> $className,
		0);

		// Find the file(s)
		$files = Jackal::files(Jackal::setting("class-path", self::$DEFAULT_CLASS_PATH), $replaceables);

		// TODO: Add support for module not found
		return @$files[0];
	}

	/**
	 * Returns the current Jackal error level
	 * 
	 * Jackal::error() sets the current error code. This method retrieves that 
	 * error code. The purpose of this method is to allow other modules to 
	 * determine if an error has occurred between the beginning of the request 
	 * and the time of calling this method.
	 * 
	 * In the future, there will be a method to get a list of the errors that
	 * have occurred in order to display them in a list.
	 * 
	 * @example Displays the current error level
	 * <code type="php">
	 * Jackal::error(404, "I couldn't find that file");
	 * echo Jackal::getErrorLevel(); // 404
	 * </code>
	 * 
	 * @return integer The most recent error code
	 */
	public static function getErrorLevel() {
		return self::$_error_level;
	}
	
	/**
	 * Returns a pointer to a global function
	 * 
	 * This method looks up a function and returns a pointer to it for late
	 * bound execution. If the function does not exist, then it creates a null
	 * function and returns the pointer to that function. This allows modules
	 * to call methods of helpers that may or may not be loaded.
	 * 
	 * If the second $helper parameter is provided, then Jackal will attempt to
	 * load said helper if the function does not exist.
	 * 
	 * @example How to use this function
	 * 
	 * <code type='php'>
	 * // Suppose there is a helper foo that defines function bar()
	 * 
	 * // Get the function
	 * $bar = Jackal::getFunction("bar", "foo");
	 * // Call the function
	 * echo $bar();
	 * </code>
	 * 
	 * If $function cannot be found, then a void function will be created, 
	 * which does nothing, but returns the input. This is useful for safely 
	 * implementing helpers that perform actions on the input. 
	 * 
	 * @example Example of non-existent function
	 * <code type='php'>
	 * // Suppose there is an optional (but not present) helper called translate
	 * 
	 * $t = Jackal::getFunction("translate", "international");
	 * echo $t("Some example text"); // Outputs "Some example text"
	 * </code> 
	 * 
	 * @param string $function Name of the function to point to
	 * @param string $helper Name of the helper to load
	 * 
	 * @return string Pointer to the function
	 */
	public static function getFunction($function, $helper="") {
		// See if the function is cached
		if($f = @self::$_functions[$function]) return $f;
		
		// If the function doesn't exist, then load the helper 
		if(!function_exists($function)) if($helper) @self::loadHelper($helper);
		// If the function exists now, then create and return the local pointer
		if(function_exists($function)) return self::$_functions[$function] = $function;
		// Return the void function
		if($f = @self::$_functions["void"]) return $f;
		// The void function doesn't exist, so create it
		return self::$_functions["void"] = create_function('$a=""', 'return $a;');
	}

	/**
	 * Load and return the class associated with a model
	 * 
	 * This method will attempt to load the class associated with a model and 
	 * return it. If a previous instance is found, then that instance will be 
	 * returned instead.
	 * 
	 * The model requested should be in the correct case, and should not contain
	 * the suffix Model. So if the class requested is SomethingModel, $model 
	 * would be Something
	 * 
	 * <i>Note: This method is not recommended. Instead, use model()</i>
	 * 
	 * @example Loads the FooModel class and executes the find method
	 * <code type="php">
	 * // Get the instance
	 * $fooModel = Jackal::getModelClass("Foo");
	 * // Execute the search (usually this queries the database)
	 * $results = $fooModel->find("foo");
	 * </code>
	 * 
	 * @param string $model The name of the model to load
	 * 
	 * @return object An instance of the model
	 */
	public static function &getModelClass($model) {
		$model_ = strtolower($model);

		// Return the model if it already exists
		if(@self::$_models[$model_]) return self::$_models[$model_];

		// Make sure the model class is loaded
		if($model != "JackalModel") Jackal::loadLibrary("JackalModel");

		// Map out the places to look
		$placesToLook = Jackal::setting("model-path", "<ROOT>/{<JACKAL>,<LOCAL>}/{model,modules/*/model/}{<MODEL>}.php");
		// Add the lowercase version of the model
		$placesToLook = str_replace("<MODEL>", "{<MODEL>,<LMODEL>}", $placesToLook);
		// Execute the find (glob)
		@list($file) = Jackal::files($placesToLook, array("MODEL" => $model, "LMODEL" => $model_));

		if($file) {
			// Memorize what the class should be named
			$class = $model."Model";
			// Include the model
			Jackal::_include_once($file);
			// Instantiate the model
			$object = new $class();
			// Store the model
			self::$_models[$model_] = $object;
			// Return the model
			return self::$_models[$model_];
		} else {
			$object = null;
			return $object;
		}
	}

	/**
	 * Parse a query string and do what it says to do
	 * 
	 * This is an internal method used by Jackal to parse the web request. This
	 * method parses the request url, and pulls out flaggers, debugging, and 
	 * any other keys. It also sets $currentURL. Then it hands the request off
	 * to call().  
	 * 
	 * This method is made public so that in the rare case that another request 
	 * needs processed. However, this would cause the system to be unstable. 
	 * 
	 * @example Treat a request as if it came from the browser
	 * <code type="php">
	 * Jackal::handleRequest("http://www.example.com/Foo/bar.php");
	 * </code>
	 * 
	 * @param string $queryString 	 The request string. This will default to $_SERVER["QUERY_STRING"] if not provided.
	 * @param boolean $returnContent If this variable is true, then the content
	 * 								 will be returned instead of output to the browser. Defaults to 
	 * 								 false.
	 * 
	 * @return mixed The result of call()
	 */
	public static function handleRequest($queryString=null, $returnContent=false) {
        // Default queryString to the one in the headers
        if($queryString == null) $queryString = $_SERVER["QUERY_STRING"];
		// Get the base URL of the currently running application
		$baseURL = Jackal::siteURL("");
		// Remove the URL portion of the request
		$queryString = implode("", explode($baseURL, $queryString));
		// Start an additional output buffer for returning the content
		if($returnContent) ob_start();
		// Remove leading slash if it's there
		$queryString = ltrim($queryString, "/?");

		//  __________________________________________________
		// / Replace aliases                                  \
		
		// Get the aliases setting and make sure it's an array
		$aliases = (array) Jackal::setting("aliases");
		
		// Scan for regular expressions
		foreach($aliases as $name=>$value) {
			if($name[0] == "/") $queryString = preg_replace($name, $value, $queryString);
			else $queryString = str_replace($name, $value, $queryString);
		}
		
		// \__________________________________________________/
		
		// These are the types of queryStrings that have to be handled:
		// site.com/module/action
		// site.com/module/action/foo/bar
		// site.com/module/action?foo=bar
		// site.com/?module/action
		// site.com/?module/action/foo/bar
		// site.com/?module/action&foo=bar
		preg_match('#^'
		// Matching for module
		.'(\w+)/?'
		// Optional matching for action
		.'([^?&/]*)/?'
		// Matching for remaining segments
		.'([^?&]*)[?&]?'
		// Matching for urlencoded parameters
		.'(.*)'
		.'#', $queryString, $matches);
		// Assign the matches to local variables
		@list(,$module, $action, $segments, $parameters) = $matches;
		// Urldecode the parameters
		parse_str($parameters, $parameters);
		// Add any POST vars to parameters
		$parameters = array_merge($parameters, $_POST);
		
		/*
		 * 
		 * LPK - Feb 21, 2013
		 * Check for a jackal template change. I did this because modern browsers see
		 * URL's with flags like "ajax" as a different domain and will not return
		 * the response text, and generate an error.
		 *  
		 */
		if(isset($parameters["jackal_template_change"])) {
			Jackal::call("Template/change/Template/$parameters[jackal_template_change]");
		}

		// Set this item as the base of scope
//		self::$_scope = array( array($module, $action) );
		// Set the current URL
		self::$currentURL = "$module/$action";

		// Format the message (the main purpose of this line is to keep the
		// Jackal::call clean)
		$message = rtrim("$module/$action/$segments", "/");

		$result = Jackal::call($message, $parameters);
		
		if($returnContent) {
			return ob_get_clean();
		} else {
			return $result;
		}
	}

	/**
	 * Include a file without accidentally passing data from current scope 
	 * 
	 * This is just a wrapper for include. The purpose of this function is to 
	 * ensure that the included file has a clean scope. 
	 * 
	 * @param string $file The path of the file to include. This is passed
	 * 		directly to the php <a href='http://www.php.net/include'>include</a>
	 * 		statement
	 * @param array $data An associative array of data to extract into the local
	 * 		scope 
	 * 
	 * @return void
	 */
	public static function _include(/* $file, $data */) {
		// Will import $data into current scope
		if(func_num_args() == 2) extract(func_get_arg(1));
		// Include $file
		include(func_get_arg(0));
	}

	/**
	 * Include a file without accidentally passing data from current scope
	 * 
	 * This is the same as _include(), but only includes the file one time
	 * 
	 * @param string $file The path of the file to include. This is passed
	 * 		directly to the php <a href='http://www.php.net/include_once'>include_once</a>
	 * 		statement
	 * @param array $data An associative array of data to extract into the local
	 * 		scope 
	 * 
	 * @return void
	 */
	private static function _include_once(/* $file, $data */) {
		// Will import $data into current scope
		if(func_num_args() == 2) extract(func_get_arg(1));
		// Include $file
		include_once(func_get_arg(0));
	}
	
    /**
     * Initialize Jackal and load necessary configs and libraries 
     * 
     * This method does not process the url. That is what handleRequest() or start() are for.
     * 
     * return void
     */
    public static function load($argv=null) {
        // TODO: Remove this if we're not using error-based-routing
		header("HTTP/1.0 200 OK");
		// Import settings
		Jackal::$_settings = (array) @$GLOBALS["jackal-settings"];
		// Load the base config
		Jackal::_loadConfigs();
        // Set the timezone
        @date_default_timezone_set(self::$_settings["jackal"]["timezone"]);
        
		// Remove magic quotes
		if (get_magic_quotes_gpc()) {
			$gpc = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
			array_walk_recursive($gpc, create_function('&$value, $key', '$value = stripslashes($value);'));
		}
		
		// TODO: This is required to get GAE working. We can probably remove later
		// Walk through the stack trace frame-by-frame, searching for the document root
		if(isset($_SERVER['APPENGINE_RUNTIME']))
		foreach(debug_backtrace() as $frame) {
			if(@$frame["class"] != "Jackal") {
				// Initialize path to the file for this item
				$path = $frame['file'];
				
				// Go through the path by directory from left to right
				while($path = strstr(substr($path, 1), '/')) {
					// Find the longest common prefix
					for($i=0, $iMax=strlen($path); $i<$iMax; ++$i) 
						if(@$_SERVER["REQUEST_URI"][$i] != $path[$i]) break;
					// Found a common prefix
					if($i != 1) {
						$_SERVER["SCRIPT_NAME"] = $path;
						$_SERVER["DOCUMENT_ROOT"] = dirname($path);
						break 2;
					}
				}
			}
		}
		
		// Find the longest common prefix
		for($i=0, $iMax=strlen($_SERVER["SCRIPT_NAME"]); $i<$iMax; ++$i) 
			if(@$_SERVER["REQUEST_URI"][$i] != $_SERVER["SCRIPT_NAME"][$i]) break;
		// Parse the QUERY_STRING
		$_SERVER["QUERY_STRING"] = substr($_SERVER["REQUEST_URI"], $i);
		// Rip INDEX out of the query string
		$_SERVER["QUERY_STRING"] = str_replace(Jackal::setting("index-url"), "", $_SERVER["QUERY_STRING"]);
		// Rip SUFFIX out of the query string
		$_SERVER["QUERY_STRING"] = str_replace(Jackal::setting("suffix"), "", $_SERVER["QUERY_STRING"]);
		
		// Pull flags out of URI
		Jackal::flagCheck();
		// Turn debugging on
		self::_debuggingCheck();
		// Turn on error logging
		Jackal::_startLogging();
		
		//  _________________________________________________
		// / Calculate the base url                          \
		
		// Add the protocol (http / https)
		$parts[] = strtolower(strtok($_SERVER["SERVER_PROTOCOL"], "/")).":/";
		// Add the hostname
		$parts[] = $_SERVER["HTTP_HOST"];
		// Add the dirname() path
		($directory = trim($_SERVER["PHP_SELF"], "/")) && ($parts[] = $directory);
		// Add the index url
		($index = Jackal::setting("index-url")) && ($parts[] = Jackal::setting("index-url"));
		// Cache the URL so far
		self::$_urlPrefix = implode("/", $parts);
		
		// \__________________________________________________/
			
		// Load default helpers
		$helpers = (array) Jackal::setting("autoload-helpers");
		foreach($helpers as $helper) if($helper) Jackal::loadHelper($helper);

		// Load default libraries
		$libraries = Jackal::setting("autoload-libraries", array());
		foreach($libraries as $library) if($library) Jackal::loadLibrary($library);
		
		// Load default modules
		$modules = (array) Jackal::setting("autoload-modules");
		foreach($modules as $module) if($module) Jackal::loadLibrary($module);

		// Load debug helpers
		if(Jackal::debugging()) {
            $helpers = (array) Jackal::setting("debug-helpers", array());
			foreach($helpers as $helper) if($helper) Jackal::loadHelper($helper);
		}
    }
    
	/**
	 * Load all the config files for the application
	 * 
	 * Internal function used when Jackal first receives a request. This method
	 * loads all the config files in the system.
	 * 
	 * Config files are ".php" files inside "config" folders 
	 * 
	 * @return void
	 */
	private static function _loadConfigs() {
		// Gather all the config files
		$files = Jackal::files("<ROOT>/{<JACKAL>/,<LOCAL>/}{,modules/*/,libraries/*/}config/{jackal,*}.{yaml,yml,php}", array());
		
		// Order the array with jackal first, then everything, followed by 'MY_*' files
		$files = array_merge(
			preg_grep('~jackal[^/]+~', $files),
			preg_grep('~(MY_|_\.)~', $files, PREG_GREP_INVERT),
			preg_grep('~MY_~', $files),
			preg_grep('~_\.~', $files)
		);
		
		// Load each config  
		foreach($files as $i=>$file) {
			// Load php files one way and yaml files another
			switch(strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
				case "php" : Jackal::_include($file)                       ; break;
				case "yml" :
				case "yaml": Jackal::putSettings(file_get_contents($file)) ; break;
			}
		}
		
		// // Supercede index_url if mod_rewrite configured
		if(trim(@self::$_settings["jackal"]["index-url"]) == "?")
			// If mod_rewrite working
			if(@$_SERVER["REDIRECT_STATUS"]) self::$_settings["jackal"]["index-url"] = "";
		
		// Flaggers duplicates are bugging me
		self::$_settings["jackal"]["flaggers"] = array_unique((array) self::$_settings["jackal"]["flaggers"]);
	}

	/**
	 * Load the helper specified by $name
	 * 
	 * Basically just includes the specified file, allowing for its resources
	 * to be utilized by other code. Typically, a helper is a file that
	 * provides global functions to speed up development or help perform
	 * repetitive routine tasks such as validating a phone number or email.
	 * 
	 * @example Searches the helpers and loads the array.php helper file
	 * <code type="php">
	 * Jackal::loadHelper("array");
	 * </code>
	 * 
	 * @param string $name The name of the helper to load
	 * 
	 * @return void
	 */
	public static function loadHelper($name) {
		// Make sure I only get called once
		if(defined("INCLUDE_$name")) return true; define("INCLUDE_$name", 1);

		// Find the files required
		$files = Jackal::files(Jackal::setting("helper-path"), array("FILE" => $name));

		if(count($files)) {
			foreach($files as $file) Jackal::_include_once($file);
			return true;
		} else {
			Jackal::error(501, "Helper $name not found.");
			return false;
		}
	}
	
	/**
	 * Instantiate and return the library or module
	 * 
	 * This method will instantiate and return the class associated with $class.
	 * If an instance already exists, then the previous instance is returned.
	 * 
	 * This method passes the call to getClass(), and therefore, will follow 
	 * that same logic.
	 * 
	 * It is possible -- but not re-commented --  to load a folder based module
	 * and execute a method directly on the destination object, because this 
	 * method simply returns an instance of the class.
	 * 
	 * @example Loads an instance of the Template library
	 * <code type="php">
	 * // Load the library
	 * Jackal::loadLibrary("Template");
	 * 
	 * // This is also possible, but not recommended
	 * $template = Jackal::loadLibrary("Template");
	 * $template->doSomething();
	 * // Instead use this, which will load the library and execute the method
	 * Jackal::call("Template/doSomething");
	 * </code>
	 * 
	 * @param string $className The name of the library or module to load
	 * 
	 * @return object The instance of the object
	 */
	public static function loadLibrary($className) {
		$class = Jackal::getClass($className);

		return $class;
	}
	
	/**
	 * Returns a new instance to an object of the specified path
	 * 
	 * URI should be in Module/Object format.
	 * 
	 * You can call this method with just Module, but that will not create a
	 * new instance of the module class, because Module classes are supposed
	 * to be singletons. Instead, that will just return the single instance
	 * of that module (and create it if there is none).
	 * 
	 * Jackal does not ensure that $URI or any other parameters will be passed
	 * to the constructor.
	 * 
	 * @Example Create a button and output to the browser
	 * <code type='php'>
	 * 	// Create a button
	 * 	$button = Jackal::make("UI/button");
	 * 	$button->label = "Foo";
	 * 	// Implicitly calls $button->__toString();
	 * 	echo $button;
	 * </code>
	 * 
	 * @Example Create a button with parameters inline and output
	 * <code type='php'>
	 * 	echo Jackal::make("UI/button", array("label" => "Foo"));
	 * </code>
	 * 
	 * @Example Create a button with parameters in string
	 * <code type='php'>
	 * 	echo Jackal::make("UI/button&label=Foo");
	 * </code>
	 * 
	 * @param string $URI The path to the object to make
	 * 
	 * @return object An instance of the object
	 */
	public static function make($URI) {
		assert('is_string($URI)');
		
		// Match for Module/Object?And=Arguments or Module/Object/Segments
		if(preg_match('_^
			([\w-]+)               (?# The module)
			(?:[/:]([\w-]+))?      (?# The name of the object to instantiate)
			(?:((?:/[^/\?]+)+))?   (?# Any arguments to be passed in by number)
			(?:\?(.*))?            (?# Any arguments to be passed in by name)
			_x', $URI, $matches)) {
			
			$matches["module"]     = @$matches[1];
			$matches["object"]     = @$matches[2];
			$matches["segments"]   = @$matches[3];
			$matches["parameters"] = @$matches[4];
			
			// Make sure a module was passed
			if(!@$matches["module"]) {
				Jackal::error(500, "Must specify module");
				return false;
			}

			// Sanitize the module name
			$matches["module"] = preg_replace('/[^\w]/', "_", $matches["module"]);
			// If no object was passed, then just return the module
			if(!@$matches["object"]) return self::loadLibrary($matches["module"]);
			// Sanitize the object name
			$matches["object"] = preg_replace('/[^\w]/', "_", $matches["object"]);

			$segments = explode("/", trim(@$matches["segments"], "/"));
			$parameters = ($parameters = @$matches["parameters"]) ? Jackal::call("Strings/parseStr/$parameters") : array();
			$parameters = ((array) @func_get_arg(1)) + $parameters;
			
			// Generate the name of the class
			$className = "$matches[module]__$matches[object]";
			
			// If the class hasn't been included yet, then find it
			if(!class_exists($className)) {
				// Look for the object file or folder based on the path in settings
				$files = Jackal::files(Jackal::setting("object-path"), array(
					"MODULE" => $matches["module"],
					"OBJECT" => "*",
					));


				$files = array_values(preg_grep(',/'.preg_replace('/[_-]/', '[-_]', $matches["object"]).'.php$,i', $files));
	
				// If we found files, then 
				if(count($files)) {
					$file = $files[0];
					
					// Is this a folder-based class?
					if(is_dir($file)) Jackal::createClass($className, $file);
					// This is a file based class, so include the file
					else include($file);
					
					assert("class_exists('$className')");
				}

				// If we didn't find files
				else {
					Jackal::error(500, "Object $matches[module]__$matches[object] does not exist");
					return false;
				}
			}
			
			$result = new $className(array_merge(array_filter($segments), array_filter($parameters)));
			self::$_classes[$className] = $result;
			
			return $result;
		}
	}

	/**
	 * Get the model and try to execute the method
	 * 
	 * This method is the companion to call(), but for models. This method 
	 * ensures that the model requested is loaded (by calling getModelClass()) 
	 * and executes the method (by calling executeMethod()).
	 * 
	 * $model is a message style string to send to the model class. So if the 
	 * model is SomethingModel, and you wanted to <u>find</u> something, then
	 * you would pass "Something/find".
	 * 
	 * $data is anything that is to be handled by the destination model. Jackal
	 * does little modification to this array as that is the responsibility of 
	 * the model.
	 * 
	 * This method returns the result of the model call
	 * 
	 * All logic to be handled is done by the destination model class. Jackal
	 * provides little actual work in this method as that is the responsibility 
	 * of the model.
	 * 
	 * @example Find all the entries in the bar table of the Foo model
	 * <code type="php">
	 * $bars = Jackal::model("Foo/find/bar");
	 * </code>
	 * 
	 * @example Find all the bars in the Foo model that are named 'Test'
	 * <code type="php">
	 * $bars = Jackal::model("Foo/find/bar", array("name" => "Test"));
	 * </code>
	 * 
	 * @param string $message The message to send
	 * @param mixed $data The data to send
	 * 
	 * @return mixed The result of the model call
	 */
	public static function model($message, $data=array()) {
		@list($model, $method, $segments) = explode("/", $message, 3);

		$object = Jackal::getModelClass($model);

		// If the model couldn't be found, then tell all about it.
		if(!$object) {
			Jackal::error(501, "Cannot find model $model", "The application is written badly and died because of it.", true);
			return false;
		}

		if($segments) {
			$segments = explode("/", $segments);
			$data = array_merge((array) $segments, (array) $data);
		}

		else {
			// If segments didn't exist, then set the table to an empty string to cause JackalModel to find it
			// Allow for additional ordered parameters
			$data = array_merge((array) $data, array_slice(func_get_args(), 1));
		}
		
		
		if($method) {
			return Jackal::executeMethod($object, $method, array($data));
		} else {
			return $object;
		}
	}
	
	/**
	* Store a setting to be accessed later with setting()
	* 
	* If a namespace is not provided, then Jackal will attempt to guess it. 
	* However, this requires extra processor time and is therefore not 
	* recommended.
	* 
	* @example Put YAML settings for module Foo
	* 
	* If there is only one parameter and it is a string, then it is parsed as
	* {@link http://www.yaml.org/ YAML}. Each module should list their settings
	* in a section, and can set settings in other modules by listing that 
	* section. 
	* 
	* <code type="php">
	* Jackal::putSettings("
	* foo:
	* 	something: Example value 1
	* 	something-else: [array value 1, array value 2]
	* ");
	* </code>
	* 
	* @example Store one setting for module Foo
	* 
	* If there are two parameters, then the first is used as the key and the
	* second is used as the value. The key should use slashes to denote the
	* section. 
	* 
	* <code type="php">
	* // One level deep
	* Jackal::putSettings("foo/something", "example value");
	* // Three levels deep
	* Jackal::putSettings("foo/one/two/three/foo", "bar");
	* </code>
	* 
	* Form 3: putSettings(array("key" => "value"))<br>
	* This method is only provided for backwards compatibility. If you aren't
	* currently using this form, then don't. This method is planned for 
	* deprecation.
	* 
	* @param string $settings YAML string, setting path, or array of settings 
	* @param mixed $value Value for setting or unset
	* 
	* @return void
	*/
	public static function putSettings(/*$settings=array(), $value=NULL*/) {
		$args = func_get_args();
		// Initialize the settings variable to prevent logic errors leaving it 
		// uninitialized
		$settings = array();
		
		switch(array(count($args), gettype(@$args[0]))) {
			//
			// YAML settings
			//
			case array(1, "string"):
				// Load the Spyc library to parse YAML
				self::loadLibrary("Spyc");
				// Parse the settings as YAML
				$settings = spyc_load($args[0]);
				break;
			
			//
			// One explicit setting (ie putSettings("Module/foo", "bar"))
			//
			case array(2, "string"):
				// Get the path and value from arguments (for code legibility)
				list($path, $value) = $args;
				// Path should be namespaced just like a message Module/foo
				$components = explode("/", $path);
				
				// The namespace wasn't provided, so we have to find it
				if(count($components) < 2) {
					// Get the key we're looking for out of the components array (for code legibility)
					$key = $components[0];
					// Get the module we're looking for
					@(list($module) = self::scope()) || ($module = "jackal");
					// Convert to lowercase
					$module = strtolower($module);
					
					// See if this is a jackal setting
					if(isset(self::$_settings[$module][$key])) {
						// Add the module to the front of the components resulting in module/whatever 
						array_unshift($components, $module);
					} elseif(isset(self::$_settings["jackal"][$key])) {
						// Add jackal to the front of the components resulting in jackal/whatever
						array_unshift($components, "jackal");
					} else {
						// Add the module to the front of the components resulting in module/whatever 
						array_unshift($components, $module);
					}
				}
				
				// Faster to do it this way if there's only 2 components
				if(count($components) == 2) {
					self::$_settings[$components[0]][$components[1]] = $value;
				} else {
					// Set deep setting
					$chain = '["'  . implode('"]["', $components) . '"]';
					eval("self::\$_settings$chain = \$value;");
				}
				
				break;
			
			//
			// Array of settings
			//
			case array(1, "array"):
				return self::$_settings = self::merge_arrays(self::$_settings, $args[0]);
		}
		
		// Merge new settings into existing settings
		if(is_array($settings))
			self::putSettings($settings);
			
		return;
	}
	
	public static function merge_arrays() {
		// Copy the arguments into a new array so that we can easier operate on them
		$arguments = func_get_args();
		// Prepare the result array in order to prevent any errors
		$result = reset($arguments);
		
		// Go through all the arguments
		while($array = next($arguments)) {
			// Go through all the items in this array
			for(reset($array); list($name, $value) = each($array);) {
				if(is_numeric($name)) {
					// Numeric indices are appended with new indexes  
					$result[] = $value;
				} elseif(is_array($value)) {
					// Arrays are sub-merged
					$result[$name] = self::merge_arrays((array) @$result[$name], $value);
				} else {
					// Simple values override
					$result[$name] = $value;
				}
			}
		}
		
		// Return the result array
		return $result;
	}
	
	/**
	 * Returns a string with information about a Jackal variable
	 * 
	 * This method should be used for debugging purposes when you need to see 
	 * the value of a variable that belongs to Jackal
	 * 
	 * Sometimes when you're programming it's useful to see information that is
	 * private to Jackal. This is difficult because Jackal is not instantiable,
	 * which makes late reflection ({@link http://www.php.net/print_r print_r}) 
	 * nearly impossible. This method aims to solve that. 
	 * 
	 * @example Get the value of the settings variable
	 * <code type="php">
	 * // Get the value of the variable from Jackal
	 * $settings = Jackal::query("_settings");
	 * // Output the value into the browser
	 * echo "<pre>$settings</pre>";
	 * </code>
	 * 
	 * @param string $variable The name of the variable to return information about
	 * 
	 * @return string
	 */
	public static function query($variable) {
		return print_r(self::$$variable, 1);
	}
	
	/**
	 * Performs a Jackal::call, but uses output buffering to return the result as text 
	 * 
	 * @param mixed $args... See Jackal::call
	 * 
	 * @return string
	 *
	 */
	public static function returnCall() {
		// Find out what arguments we were called with
		$arguments = func_get_args();
		// Start output buffering so that nothing goes to the browser
		ob_start();
		// Execute the call we're supposed to
		call_user_func_array("Jackal::call", $arguments);
		// Get the output buffer
		$buffer = ob_get_contents();
		// Erase the output buffer
		ob_end_clean();
		// Return the output buffer as a string
		return $buffer;		
	}

	/**
	 * Returns the current calling scope of Jackal
	 * 
	 * All call() statements keep track of the hierarchy tree in order for other
	 * modules to be able to track the call chain. This is useful if a module
	 * needs to know who called it, or what the first call in the chain was.
	 * There is little practical use for accessing antecedents between those 
	 * two values, but it is possible.
	 * 
	 * This method will return the call at position $position. If $position is
	 * omitted, then the previous call is returned. If position is 0, the first 
	 * call is returned. If position is positive, then the nth call is  
	 * returned. If position is negative, then the nth previous call is 
	 * returned. 
	 * 
	 * Pretty much the only interesting values for this method are 0 and -1. 
	 * Except in the case of a trigger, when -2 is useful.
	 * 
	 * @example Get the name of the currently executing module
	 * <code type="php">
	 * list($module, $method) = Jackal::scope();
	 * echo "I am running inside of $module";
	 * </code>
	 * 
	 * @example Get the name of the calling module
	 * <code type="php">
	 * list($module, $method) = Jackal::scope(-1);
	 * echo "I was called by the $method method of $module";
	 * </code>
	 * 
	 * @example Get the name of the root module (the entry-level call)
	 * <code type="php">
	 * list($module, $method) = Jackal::scope(0);
	 * echo "$module is the module that started it all";
	 * </code>
	 * 
	 * In order to get more information about this variable, try using query()
	 * to get the value of $_scope
	 * <code type="php">
	 * echo Jackal::query("_scope");
	 * </code>
	 * 
	 * @param integer $position The position desired
	 *
	 * @return array The current module/action pair for the present scope
	 */
	public static function scope($position=-1) {
		$count = count(self::$_scope);
		
		if($position >= $count) return array();
		if($position < 0-$count) return array();
		
		$i = ($count+$position) % $count;
		return self::$_scope[$i];
	}

	/**
	 * Store the current error level for future error handlers
	 * 
	 * This method is used to set the error level without calling error(). This
	 * is useful to set the error, but not generate an error message or write
	 * to the error log.
	 * 
	 * @example Set the error level without sending a message
	 * <code type="php">
	 * Jackal::set_error_level(404);
	 * </code>
	 * 
	 * @param integer $level The new error code to set
	 * 
	 * @return void
	 */
	public static function set_error_level($level) {
		headers_sent() ||
		header("HTTP/1.1 $level");
		
		self::$_error_level = $level;
	}

	/**
	 * Retrieve a settting from the configuration
	 * 
	 * This method returns value of the setting named $name. If $default is 
	 * provided, and the setting with $name is not found, then it is returned 
	 * instead.
	 * 
	 * @example Some basic setting() examples
	 * 
	 * Get the bar setting of the Foo module
	 * <code type="php">
	 * $bar = Jackal::setting("Foo/bar");
	 * </code>
	 * 
	 * Get the bar setting of the Foo module and default to "baz"
	 * <code type="php">
	 * $bar = Jackal::setting("Foo/bar", "baz");
	 * </code>
	 * 
	 * @param string $name The name of the setting to retrieve
	 * @param mixed $default The default value to return if the setting is not 
	 * 		found
	 * 
	 * @return mixed The value of the setting requested, or $default
	 */
	public static function setting($name=NULL, $default=false) {
		// Break apart name into module/key components
		$components = explode("/", $name);
        
		// If there aren't enough components, then guess at the namespace
		if(count($components) < 2) {
			// Allow retreival of root nodes
			if(isset(self::$_settings[$name])) return self::$_settings[$name];
			// Get the calling module or default to jackal
			@(list($module) = self::scope()) || ($module = "jackal");
			// Lowercase the module name
			$module = strtolower($module);
			
			// Guess at the namespace
			if(isset(self::$_settings[$module][$name])) {
				// Add the module to the front of the components for module/foo
				array_unshift($components, $module);
			} elseif(isset(self::$_settings["jackal"][$name])) {
				// Add jackal to the front of the components for jackal/foo
				array_unshift($components, "jackal");
			} else {
				// Add the module to the front of the components for module/foo
				array_unshift($components, $module);
			}
		}
		
		// Initialize the node to the settings root
		$node = self::$_settings;
		// Walk down the list until we get to the destination element
		foreach($components as $key) $node = @$node[$key];
		// Set the node to default if not found
		($node) || ($node = $default);
		
		// Done
		return $node;
		
		// Get the section and key (for code legibility)
		list($section, $key) = $components;
		
		if(@self::$_settings[$section][$key]) return self::$_settings[$section][$key];
		//		return $default;
		return self::$_settings[$section][$key] = $default;
	}
    
	/**
     * Run shutdown code for Jackal and all loaded libraries / modules
     * 
     * This method basically just calls __shutdown() for all Jackal-loaded libraries. If in the future
     * there is more shutdown code needed for Jackal, then this method will run that too.
     */
    public static function shutdown() {
        // Call the shutdown functions
		foreach(self::$_classes as $class) 
			if(method_exists($class, "__shutdown")) 
				self::call(get_class($class)."/__shutdown");
    }

	/**
	 * Return the url in a form that Jackal prefers
	 * 
	 * Returns the absolute path to a url in the system. The purpose of this 
	 * method is to respect all the settings of how URLs are handled by the 
	 * system. This method will preserve any <a href='http://httpd.apache.org/docs/2.0/mod/mod_rewrite.html'>mod_rewrite</a>
	 * settings as well as the port and https components of the path along with
	 * any previously set flags.
	 * 
	 * When calling this method, simply pass $path as a message and it will be
	 * wrapped appropriately. 
	 * 
	 * In order to remove any flags, simply pass false as $flags
	 * 
	 * @example Some basic siteURL calls
	 * 
	 * Get the url path to the bar method of the Foo module
	 * <code type="php">
	 * $url = Jackal::siteURL("Foo/bar");
	 * </code>
	 * 
	 * Get the url path to the bar.gif resource of the Foo module
	 * <code type="php">
	 * $url = Jackal::siteURL("Foo/resources/bar.gif");
	 * </code>
	 * 
	 * Get the url without any flags. This is useful if the script was invoked
	 * with the 'ajax' flag set, and you do not want it set in the url.
	 * <code type="php">
	 * $url = Jackal::siteURL("Foo/bar", false);
	 * </code>
	 * 
	 * @param string $path The message to wrap
	 * @param array $flags An array of flags to set
	 * 
	 * @return string The new URL
	 */
	public static function siteURL($path, $flags=array()) {
		$parts = array();
		
		// If the path DOESN'T have host information (http://)
		if(!preg_match(',\w+://,', $path)) {
			$parts = array(self::$_urlPrefix);
			
			if($flags !== false) {
				// Add the other flags
				$parts = array_merge($parts, array_filter(self::$_caseFlags));
			} else {
				// Add the DEBUG flag
				(Jackal::debugging()) && ($parts[] = Jackal::setting("debug-path"));
			}
		}

		// Finally, add the path and suffix
		if($path) {
			// If the path already has a suffix, or ends in a slash (/), or has a '?'
			if(preg_match('/(\.\w+$|\\/$|\?)/', $path)) {
				$parts[] = $path;
			} else {
				$parts[] = $path.Jackal::setting("suffix");
			}
		}
		
		// FIXME: Find a better way to do this
		// Remove trailing slash
		if(end($parts) == "/") {
			array_pop($parts);
			$parts[] = "";
		}
		
		return implode("/", $parts);
	}

	/**
	 * Main Jackal entry point (should only be called once)
	 * 
	 * Your entry point script should call start() in order to allow Jackal to 
	 * handle the request. In the future, there will be an argument to suppress
	 * parsing the URL. This is so that Jackal features can be integrated into
	 * an existing site without converting the site to a Jackal structure.
	 * 
	 * @return void
	 */
	public static function start($argv) {
		if(self::$_started) return Jackal::error(500, "Jackal already started.");
		self::$_started = true;
		self::_start($argv);
	}

	/**
	 * This is the private counterpart to start()
	 * 
	 * Called by start() in order to do the actual start
	 * 
	 * @return void
	 */
	private static function _start($argv) {
        // Load Jackal
        self::load($argv);
		// Handle the request
		self::handleRequest($_SERVER["QUERY_STRING"]);
        // Handle shutdown
        self::shutdown();
	}

	/**
	* Turns on error logging to file according to the configuration settings
	* 
	* This method is called by _start() in order to activate error logging. If 
	* error logging is disabled in settings, then it does nothing. If the error
	* log does not exist, then it is created. If no error log can be created, 
	* then this function will spit out an error. Currently this might have the
	* nasty side effect of a {@link http://en.wikipedia.org/wiki/White_screen_of_death WSOD}
	* 
	* Config setting: error-log &mdash; Path to error log, or blank to disable
	* 		error logging completely
	* 
	* @return void
	*/
	private static function _startLogging() {
		if($logPath = Jackal::setting("error-log", "")) {
			// Resolve the path of the log folder
			@list($logFile) = Jackal::files($logPath);

			// If the log file isn't found, then try to find a way to make it
			if(!$logFile) {
				// If we're here, then $logPath wasn't found, so look for its parent (grab the first item)
				$parentFolder = @reset(Jackal::files(dirname($logPath)));
                
				// If we were successful in finding the parent folder
				if($parentFolder) {
					// Try to make the error folder inside the parent folder
                    @mkdir("$parentFolder/" . basename($logPath));
					// Resolve the path of the log folder (again)
					@list($logFile) = Jackal::files($logPath);
				}
			}

			// If $logFile currently points to a folder, then point it to a file in said folder
			if(is_dir($logFile)) {
				// Point to a file
				$logFile = "$logFile/error.log";
				// Update the setting (in memory)
				Jackal::putSettings("error-log", $logFile);
			}

			// Make sure the log file exists
			if(!file_exists($logFile)) @touch($logFile);
			// Make sure the logFile is writable
			if(!is_writable($logFile)) {
				// Perms: (UGO = User, Group, Other)
				//                RWX    RWX    RWX
				//                421    421    421
				//Example: 761 =  XXX    XX_    __X
				if(!@chmod($logFile, 0766)) {
					@chmod(dirname($logFile), 0766);
					@chmod($logFile, 0766);
				}
			}

			// Set the settings regardless of the result of the previous checks
			ini_set("log_errors", 1);
			ini_set("error_log", $logFile);

			// Make sure we're going to be able to write to the error log
			if(!is_writable($logFile)) {
                // TODO: Make Jackal error log problems show up in admin
                return;
				$whoami = get_current_user();
                
				// See if the location exists
				if(file_exists($logFile)) {
					$message = "Could not write to $logFile.  The file exists, but is not writable.  Either make the folder writable by $whoami, or make the 'error-log' setting an empty string.";
				} else {
					// Log folder found, but not writable
					if(file_exists(dirname($logFile))) {
						$message = "Could not write to ".dirname($logFile).". The folder exists, but I can't make a ".basename($logFile)." file in it. Either make it yourself, or change the 'error-log' setting to an empty string.";
					}

					else {
						// Log folder not found
						$message = "Could not write to ".htmlentities($logPath).".  The folder does not exist.  Either create the folder, or make the 'error-log' setting an empty string.";
					}
				}

				// Log the error
				error_log($message);

				// Report the error
				if(Jackal::setting("super-quiet-mode")) {
					// Super quiet mode: Show the least information possible
					Jackal::error(501, $message, "This site is currently not accepting requests.", $die=true);
				} else {
					// Be as helpful as possible
					Jackal::error(501, $message, $message, $die=false);
				}
			}
		}
	}

	/**
	 * Convert from url string to URI array
	 * 
	 * Convert from string to uri, then if only one item exists, and default is
	 * set, then it will be assigned to default
	 * 
	 * @param string $url The array to parse. If this is an array, then it will 
	 * 		be returned as if it had successfully parse a string
	 * @param mixed $default The value to put into the default key of the 
	 * 		resulting URI array
	 * 
	 * @return array The resulting array
	 */
	public static function url2uri($url, $default="") {
		if(is_string($url)) parse_str($url, $uri);
		else $uri = $url;

		assert('is_array($uri)');

		if($default)
		if(count($uri)==1)
		list($uri[$default]) = each($uri);

		return $uri;
	}

	/**
	 * Returns calculated information such as the root of the site.
	 *
	 * This method exists, because certain calculations don't make sense to 
	 * make every single request, so this method makes them one time and caches 
	 * the result.
	 *
	 * @param string $name The piece of information to return
	 * 
	 * @return mixed The result of the calculation (the calculated field)
	 */
	public static function info($name) {
		if(isset(Jackal::$info[$name])) return Jackal::$info[$name];
		$function = "_info_$name";
		return self::$function();
	}

	/**
	 * (Called by info()) Returns the path to the parent of the jackal folder
	 * 
	 * @return string
	 */
	public static function _info_BASE_DIR() {
		return realpath(dirname(dirname(dirname(__FILE__))));
	}

	/**
	 * (Called by info()) Returns the path to the jackal configuration file
	 * 
	 * @return string
	 */
	public static function _info_SETTINGS_FILE() {
		return Jackal::info("BASE_DIR")."/jackal/settings.php";
	}
}

