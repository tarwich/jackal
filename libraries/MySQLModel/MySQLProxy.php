<?php

class MySQLProxy {
	/**
	 * @var resource pointer to the connection resource
	 */
	private static $_connection = NULL;

	/**
	 * @var array connection defaults
	 */
	private static $DEFAULTS = array(
		"dbtype" 	=> "mysql",
		"host" 		=> "localhost",
		"username" 	=> "root",
		"password" 	=> "",
		"database" 	=> "mysql"
	);

	private static $_error;
	private static $dbtype;
	private static $host;
	private static $username;
	private static $password;
	private static $database;

	/**
	 * Constructor -- because they're nice to have
	 */
	public function MySQLProxy() {
		self::__connect();
	}

	/**
	 * Connect to the database, or return true of already connected
	 *
	 * @return boolean True if successful or already connected, false otherwise
	 */
	public static function __connect() {
		if(self::$_connection) return true;

		// Load the settings from Jackal
		$dsn = Jackal::url2uri(Jackal::setting("database"));

		// Map each field to the setting or the default
		foreach(self::$DEFAULTS as $fieldName=>$defaultValue)
		if($fieldName)
		if(!(self::$$fieldName = @$dsn[$fieldName]))
		self::$$fieldName = $defaultValue;

		self::$_connection = mysql_connect(self::$host, self::$username, self::$password);
		mysql_select_db(self::$database, self::$_connection);
	}

	// ---------------------------------------------------------------------------
	// query
	// ---------------------------------------------------------------------------
	//
	// Execute a query
	//
	// ---------------------------------------------------------------------------
	public function getData($sql, $replacements=NULL /*, ...rest */) {
		// Allow the replacements to be passed in as single array as well
		if(is_array($replacements)) {
			$arguments = $replacements;
		} else {
			// Get the arguments from the variant arguments
			$arguments = func_get_args();
			// Remove the SQL from the argument list
			array_shift($arguments);
		}
		
		// Prepare arguments
		foreach($arguments as $key=>$value) {
			$arguments[$key] = mysql_real_escape_string($value);
		} 
		
		// Replace numbered arguments
		$sql = preg_replace('/(\?\d+)/e', '$arguments[$1-1]', $sql);
		// Replace unnumbered arguments
		$sql = preg_replace('/\?/e', 'array_shift($arguments)', $sql);
		// Run the query
		$this->_result = self::__query($sql);

		// If there was an error
		if(!$this->_result) {
			// Remember the error
			$this->error = self::$_error;
			// Throw the error
			trigger_error($this->error."\nWhile executing query: \n{$sql}", E_USER_ERROR);
			// Return false
			return false;
		}

		// Nothing bad happened
		return true;
	}

	protected static function __query($sql) {
		if(!self::$_connection) self::__connect();

		// Run the query
		$result = mysql_query($sql, self::$_connection);
		
//		echo $sql."<br>";
		
		// If there was an error
		if(!$result) {
			// Remember the error
			self::$_error = mysql_error(self::$_connection);
			// Throw the error
			trigger_error(self::$_error."\nWhile executing query: \n{$sql}", E_USER_ERROR);
			// Return false
			return false;
		}

		// Got a result, so return it
		return $result;
	}

	// ---------------------------------------------------------------------------
	// fetch_all
	// ---------------------------------------------------------------------------
	//
	// Get the data from a resource, or run a query and return the data
	//
	// @return array An array of items from the query (object-style)
	//
	// ---------------------------------------------------------------------------
	public function fetch_all($key=NULL) {
		// HAX!
		if(false);

		// Get all rows
		elseif($key === NULL) {
			if(is_resource($this->_result)) {
				return $this->_fetch_all($this->_result);
			} else {
				return false;
			}
		}

		// Get all rows from resource
		elseif(is_resource($key)) {
			return $this->_fetch_all($key);
		}

		// Execute query and return results
		elseif(is_string($key)) {
			// Run the query
			$this->query($key);
			// Return the results
			return $this->_fetch_all($this->_result);
		}

		trigger_error("Data type for \$key not recognized", E_USER_ERROR);
		return false;
	}

	private function _fetch_all($resource) {
		$rows = array();
		
		while($row = mysql_fetch_assoc($resource))
		$rows[] = $row;
		
		return $rows;
	}

}


?>