<?php

/**
 * MySQL database abstraction layer that makes writing queries as easy as pie
 * 
 * @author Luke Keith
 *
 */

class QueryBuilder {
	
	/**
	 * Contains the database connection string
	 */
	public $connection 			= array();				// Changing it here will not alter the connection
	
	/**yea I th
	 * Default query properties which are copied into the query upon creation.
	 */
	public $defaults 			= array(
		"limit"	=> 100,									// Default limit to use on all results
		"field" => "*",									// Default selector if no fields are provided for select statement
	);
	
	/**
	 * Functions that are different between MySQL4 and MySQL5 
	 */
	public static $functions = array(
		"buildInsertUpdateSQL"	=> "buildInsertUpdateSQL4"
	);
	
	/**
	 * When being used outside of Jackal, this contains an instance of the class
	 */
	protected static $instance = null;
	
	/**
	 * Contains current resultset
	 */
	public $results = false;
	
	/**
	 * Contains pre-processed queries
	 */
	public $query = null;
	
	/**
	 * Contains post-processed queries, which is every query ever executed.
	 */
	public $queries = null;
	
	/**
	 * The ID of the current query being processed. When a query is finished processing this number is
	 * incremented by one.
	 */
	public $query_id = 0;
	
	/**
	 * Instance of MySQLi
	 */
	public $db = null;
	
	/**
	 * Directory where the class lives
	 */
	public $dir = null;
	
	/**
	 * Debug mode true/false. Debug information is sent to the browser when this is on.
	 */
	public static $debugging = false;
	
	/**
	 * Write mode true/false. If this is set to false, insert, update, and delete will not actually
	 * query the database.
	 */
	public static $writing = true;
	
	// Maximum number of queries to save
	public static $saveMax = 50;
	
	// Logging queries
	public static $logging 		= false;
	public static $logPointer 	= ""; // The log file connection
	
	/**
	 * Every error returned by MySQL
	 */
	public $errors 		= array();
	public $error 		= ""; // The last error to be generated
	public static $maxErrors 	= 100; // Maximum number of errors to store
	
	/**
	 * Constructor
	 */
	public function __construct() {
		
		// Set class directory
		$this->dir = dirname(__FILE__)."/";
		
		// Set instance
		QueryBuilder::$instance = $this;
		
		// Connect
		$this->connect();
		
		return $this;
	}
	
	/**
	 * Returns an instance of QueryBuilder.
	 * 
	 * @return QueryBuilder
	 */
	public static function instance() {
		if (!self::$instance) self::$instance = new QueryBuilder_base();
		return self::$instance;
	}
	
	/**
	 * Turn on or off the ability to write to the database. This includes
	 * update, insert, and delete queries.
	 * 
	 * @example How to use write()
	 * <code type="php">
	 * // Toggle writing
	 * $db->write();
	 * 
	 * // Allow writing
	 * $db->write(true);
	 * 
	 * // Disallow writing
	 * $db->write(false);
	 * </code>
	 * 
	 * @param 	Bool $writing
	 * 
	 * @return	Bool
	 */
	public function write($writing=null) {
		$old = self::$writing;
		if ($writing==null) {
			self::$writing = !self::$writing;
		} else {
			self::$writing = $writing ? true : false ;
		}
		return $old;
	}
	
	public function log($logging=null) {
		$old = self::$logging;
		if ($logging==null) {
			self::$logging = !self::$logging;
		} else {
			self::$logging = $logging ? true : false ;
		}
		return $old;
	}
	
	/**
	 * Toggle debug mode on/off
	 * 
	 * @param 	Boolean	$debugging
	 * @return 	void
	 */
	public function debug($debugging=null) {
		@list($debugging) = func_get_args();
		if (!isset($debugging)) {
			self::$debugging = !self::$debugging;
		} else {
			self::$debugging = $debugging ? true : false ;
		}
	}
	
	/**
	 * Log debugging information for a SQL statement. Displays debugging information if
	 * debugging is turned on by QueryBuilder::debug()
	 * 
	 * @param 	String	SQL statement
	 * @return	void
	 */
	public function debugInfo($sql) {
//		if(!$this->error) return;
		$info = "<h4>Query: $this->query_id</h4>$sql<hr/>\r\n";
		if (mysqli_errno($this->db)) {
			$info .= "<b style='color: red;'>MYSQL ERROR ".mysqli_errno($this->db).":</b> ".mysqli_error($this->db)."<hr/>\r\n\r\n";
			error_log($info);
		}
		if (self::$debugging)
			echo $info;

		// Log error
		if (mysqli_error($this->db)) {
			$this->errors[] = array("sql"=>$sql, "error"=>"Query $this->query_id: ".mysqli_error($this->db));
		}
	}
	
	/**
	 * Run SQL query without saving or affecting the current pre-processed query
	 */
	public function runSQL($sql) {
		
		// Execute the query
		$result = mysqli_query($this->db, $sql);
		$this->error = mysqli_error($this->db);
		
		// Log error
		if ($this->error) {
			$this->errors[] = array(
				"sql"	=> $sql,
				"error"	=> $this->error
			);
		}
		
		// Return results if necessary
		$results = array();
		if( @(mysqli_affected_rows($this->db)>0) || @(mysqli_num_rows($result)>0))
			$results = $this->get_results($result);
		return $results;
	}
	
	/**
	 * Execute a query
	 * 
	 * @param $sql 		SQL statement
	 * @param $query_id	Query ID to execute, if null then the current query will be used
	 * 
	 * @return QueryBuilder
	 */
    public function run($sql, $query_id=null) {
    	
		$query = $this->query[$this->query_id];
		
		// Execute the query
		$result = mysqli_query($this->db, $sql);
		$this->error = mysqli_error($this->db);
		
		// Log error
		if ($this->error) {
			$this->errors[] = array(
				"sql"	=> $sql,
				"error"	=> $this->error
			);
		}
		
		// Log the query
		$this->logQuery($sql);
		
		// Return results if necessary
		$this->results = array();
		if (!@$query["update"]) {
			if (@mysqli_affected_rows($this->db)>0 || @mysqli_num_rows($result)>0)
				$this->results = $this->get_results($result);
		}
		return $this;
    }
    
	/**
	 * Alias for run()
	 * 
	 * return QueryBuilder
	 */
	public function r($query) {
		return $this->run($query);
	}
	
	/**
	 * Get the resultset from a query
	 */
	public function get_results($result) {
		if(is_bool($result)) return array();
		$rows = array();
		while($row = mysqli_fetch_assoc($result)) {
			$rows[] = $row;
		}
		mysqli_free_result($result);
		return $rows;
	}
	
	/**
	 * Connect to database. This will accept an array with name/value pairs or a string
	 * formatted like "host=your_host&username=your_username&password=your_pwd&database=your_db"
	 * 
	 * @param	Array/String	$connection	Array or string containing database connection information
	 * @param	String	$connection["host"]		Database host
	 * @param	String	$connection["username"]	Database username
	 * @param	String	$connection["password"]	Database password
	 * @param	String	$connection["database]	Database name
	 * 
	 * @return QueryBuilder
	 * 
	 */
	public function connect($connection=array()) {
		// Prepare directory for logging
		if(self::$logging) {
			$folder = dirname(__FILE__)."/db_log/".date("Ym");
			$file	= "$folder/".date("Y-m-d").".txt";
			if(!@is_dir($folder)) mkdir($folder, 0777, true);
			if(is_writeable($folder)) {
				self::$logPointer = fopen($file, "a");
			}
		}
		
		// Convert connection to array if string is passed
		if (is_string($connection))
			$connection = $this->makeArray($connection);
		
		// Use settings as default if they exist
		$connection = Jackal::merge_arrays(Jackal::setting("database"), $connection);
		$this->connection = $connection;
		
		// Connect allowing one or more port attempts
		if(is_array($connection["port"])) {
			foreach($connection["port"] as $port) {
				// Don't bother trying to make a database connection if the port is not open
				if($sock = @fsockopen($connection["host"], $port, $errno, $errstr, 30)) {
					if($this->db = mysqli_connect($connection["host"], $connection["username"], $connection["password"], $connection["database"], $port)) {
						break;
					}

					// Close the socket
					fclose($sock);
				}
			}
		} else {
			$this->db = mysqli_connect($connection["host"], $connection["username"], $connection["password"], $connection["database"], $connection["port"], @$connection["socket"]);
		}
		
		// Trigger an error if unable to connect
		if (!$this->db) {
			trigger_error("Cannot connect to MySQL!", mysqli_connect_error());
		}
		
		// Determine MySQL version
		$version = mysqli_get_server_version($this->db) / 10000;
		
		// Public QueryBuilder class
		if ($version>=5) {
			// Set to MySQL5 functions
			self::$functions["buildInsertUpdateSQL"] = "buildInsertUpdateSQL5";
		} else {
			// Set to MySQL4 functions
			self::$functions["buildInsertUpdateSQL"] = "buildInsertUpdateSQL4";
		}
		
		// Set default options
		mysqli_query($this->db, "SET sql_mode='STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';");
		
		return $this;
    }
    
    /**
     * Get a specific part of any pre-processed query.
     * 
     * @param $parameter
     * @param $offset
     * 
     * @return 	String	The parameter
     */
    public function get($parameter, $offset=0) { $this->getParameter($parameter, $offset); }
    public function getParam($parameter, $offset=0) { $this->getParameter($parameter, $offset); }
    public function getParameter($parameter, $offset=0) {
    	return $this->query[$this->query_id-$offset][$parameter];
    }
    
	/**
	 * Returns the first row of a resultset
	 * 
	 * @param Integer	Row number to return, default is 0
	 */
    public function row($row=0) {
    	@($row = $this->results[$row]) || ($row = false);
    	return $row;
    }
    
    /**
     * Get field from any row of data in a resultset
     * 
     * @example Get a field from the first row of a resultset immediately after a query was run.
     * <code type="php">
     * // Run a query to get users
     * $db->table("users")->select();
     * 
     * // Get the first users email address
	 * $db->getField("email");
	 * </code>
     * 
     * @param String	Name of the column
     * @param Integer	Row number
     * 
     * @return String	Column data
     */
	public function getField($field, $row=0) {
		$row = $this->row($row);
    	return @$row[$field];
    }
	
    /**
     * Set parameter value
     * 
     * @example Set the default limit to 100 records per resultset
     * <code type="php">
	 * $db->setDefault("limit", 100);
	 * </code>
     * 
     * @param String	Name of the paramter
     * @param String	New parameter value
     * 
     * @return QueryBuilder
     */
	public function setDefault($param, $value) {
		$this->defaults[$param] = $value;
		return $this;
	}
	
	/**
	 * Set the table for a query. This should always be the first method in the chain.
	 * 
	 * @example How to set the table, no pun intended
	 * <code type="php">
	 * $db->table("your_table");
	 * </code>
	 * 
	 * @param	String	$table	Name of the table
	 * 
	 * @return	QueryBuilder
	 */
	public function table($table="") {
		$this->query[$this->query_id] = array();
		$this->query[$this->query_id]["table"] = @$table ;
		$this->query[$this->query_id]["tables"][$table] = "PRIMARY";
		return $this;
	}
	
	/**
	 * Alias for table()
	 * 
	 * @return 	QueryBuilder
	 */
	public function setTable($table="") {
		return $this->table($table);
	}
	
	/**
	 * Set query ID for current query
	 * 
	 * @param	Integer	$query_id	Query ID
	 * 
	 * @return Void
	 */
	public function setQuery($query_id) {
		$this->query_id = $query_id;
	}
	
	/**
	 * Add fields to the query. This function will accept fields in a query string format
	 * like "name=value" or in an array of [name=>value] pairs. All fields will be added
	 * to the current query.
	 * 
	 * @param String/Array	$fields	Array or string of field data
	 * @param Array	$params	Additional parameters
	 * 
	 * @return QueryBuilder
	 */
	public function addFields($fields='', $params=array()) {
		if (is_string($fields))
			$fields = $this->makeArray($fields);
		if (count($fields)) {
			foreach ($fields as $field=>$value)
				$this->addField($field, $value, $params);
		}
		return $this;
	}
	
	/**
	 * Add one or more fields to a query. This function has two primary purposes which are
	 * outlined below.
	 * 
	 * @example  Add fields into a query using one of the following methods
	 * <code type='php'>
	 * // Add one field and one value
	 * $db->addField("foo", "bar");
	 * 
	 * // Add a field and value using a string
	 * $db->addField("foo1=bar1");
	 * 
	 * // Add a field and value using an array
	 * $db->addField(array(
	 * 	"foo1"	=> "bar1"
	 * ));
	 * </code>
	 * 
	 * @example Can generate the following queries
	 * <code type='sql'>
	 * /* If used in an UPDATE query
	 * SELECT `table`.`foo` FROM table
	 * 
	 * /* If used in an UPDATE query
	 * INSERT INTO table (`table`.`foo`) VALUES ('bar')
	 * 
	 * /* If used in an UPDATE query
	 * UPDATE table SET `table`.`foo`='bar'
	 * </code>
	 * 
	 * @param String $fields Array[name=>value] or string "name=value" of field data
	 * @param String $value Value of the field
	 * @param Array $params Array of additional parameters
	 * 
	 * @return QueryBuilder
	 */
	public function addField() {
		
		// Get arguments
		@list($field, $value, $params) = func_get_args();
		$args = func_num_args();
		
		// if there is only one argument
		if ($args==1) {
			if (is_array($field)) {
				$this->addFields($field, $params);
				return $this;
			}
			if (strstr($field, "&")) {
				$this->addFields($field, $params);
				return $this;
			}
		}
		
		// Add field to query
		if ($field!="*")
			$field = $this->formatField($field);
		$this->query[$this->query_id]["field"][$field] = $value;
		return $this;
	}
	
	/**
	 * Add fields that will ONLY be used for an INSERT query
	 * 
	 */
	public function addInsertField() {
		// Get arguments
		@list($field, $value) = func_get_args();
		$args = func_num_args();
		
		// if there is only one argument
		if ($args==1) {
			if (is_array($field)) {
				if (count($field)) {
					foreach ($field as $f=>$v)
						$this->addInsertField($f, $v);
				}
				return $this;
			}
			if (strstr($field, "&")) {
				$fields = $this->makeArray($field);
				$this->addInsertField($fields);
				return $this;
			}
		}
		
		// Add field to query
		if ($field!="*") $field = $this->formatField($field);
		$this->query[$this->query_id]["field_insert"][$field] = $value;
		
		return $this;
	}
	
	/**
	 * Add fields that will ONLY be used for an UPDATE query
	 * 
	 */
	public function addUpdateField() {
		// Get arguments
		@list($field, $value) = func_get_args();
		$args = func_num_args();
		
		// if there is only one argument
		if ($args==1) {
			if (is_array($field)) {
				if (count($field)) {
					foreach ($field as $f=>$v)
						$this->addUpdateField($f, $v);
				}
				return $this;
			}
			if (strstr($field, "&")) {
				$fields = $this->makeArray($field);
				$this->addUpdateField($fields);
				return $this;
			}
		}
		
		// Add field to query
		if ($field!="*") $field = $this->formatField($field);
		$this->query[$this->query_id]["field_update"][$field] = $value;
		
		return $this;
	}
	
	/**
	 * Add filters to the query. A filter is a field that populates the where clause of an array.
	 * 
	 * @example Add an array of filters
	 * <code type='php'>
	 * $results = $db->table("users")->addFilters(array(
	 * 	"email"		=> "name@example.com",
	 * 	"firstName"	=> "Luke"
	 * ))->select()->results;
	 * </code>
	 * 
	 * @example The example above will generate the following SQL
	 * <code type='sql'>
	 * SELECT * FROM users WHERE (`users`.`email`='name@example.com' AND `users`.`firstName`='Luke')
	 * </code>
	 * 
	 * @param String/Array	$fields	Array[name=>value] or string "name=value" of field data
	 * @param Array	$params	Array of additional parameters
	 * 
	 * @return QueryBuilder
	 */
	public function addFilters($filters='', $params=array()) {
		if (is_string($filters))
			$filters = $this->makeArray($filters);
		if (count($filters)) {
			foreach ($filters as $field=>$value)
				$this->addFilter($field, $value, $params);
		}
		return $this;
	}
	
	/**
	 * Copies the filters from a previous query into the current query
	 * 
	 * @param Integer $offset Offset of the last query to copy.
	 * 
	 * @example Add filters into the current query from the last query
	 * <code type='php'>
	 * $filters = $db->addLastFilters(1);
	 * </code>
	 * 
	 * @example Add filters into the current query from the query before last
	 * <code type='php'>
	 * $filters = $db->addLastFilters(2);
	 * </code>
	 * 
	 * @return QueryBuilder
	 */
	public function addLastFilters($offset=1) {
		return $this->addFilter($this->getFilters($offset));
	}
	
	/**
	 * Merge filters returned from getFilters() into another query. This function
	 * is primarily for internal use.
	 * 
	 * @example Get filters from a query
	 * <code type='php'>
	 * $filters = $db->getFilters();
	 * </code>
	 * 
	 * @example Merge the filters into a new query
	 * <code type='php'>
	 * $db->mergeFilters($filters);
	 * </code>
	 * 
	 * @param Object $filters Object returned from getFilters()
	 * 
	 * @return QueryBuilder
	 */
	public function mergeFilters($filters) {
		if (!is_object($filters)) return;
		foreach ($filters as $group=>$fields) {
			foreach ($fields as $field) {
				$this->query[$this->query_id]["where"][$group][] = $field;
			}
		}
		return $this;
	}
	
	/**
	 * Add one or more filters to a query. A filter is a field that populates the WHERE clause of an array.
	 * 
	 * @example Add a one filter to a query
	 * <code type='php'>
	 * $db->addFilter("foo", "bar");
	 * </code>
	 * 
	 * @example Add one filter to a query in a single string
	 * <code type='php'>
	 * $db->addFilter("foo=bar");
	 * </code>
	 * 
	 * @example Add multiple filters to the query
	 * <code type='php'>
	 * $db->addFilter(array(
	 * 	"foo1"	=> "bar1",
	 * 	"foo2"	=> "bar2"
	 * ));
	 * </code>
	 * 
	 * @param String/Array	$fields	Array[name=>value] or string "name=value" of field data
	 * @param String	$value	Value of the field
	 * @param Array	$params	Array of additional parameters
	 * 
	 * @return QueryBuilder
	 */
	public function addFilter() {
		
		// Get arguments
		@list($field, $value, $params) = func_get_args();
		
		// Merge filter objects into current filters
		if (is_object($field)) {
			$this->mergeFilters($field);
			return $this;
		}
		
		// If only $field as passed
		if (func_num_args()==1) {
			if (@strstr($field, "=") || @is_array($field)) {
				$this->addFilters($field);
				return $this;
			}
		}
		
		// Set filter group defaults
		$group = isset($params["group"]) ? $params["group"] : 0 ;
		$glue  = isset($params["glue"]) ? $params["glue"] : "AND" ;
		
		// Prepare value
		if(is_array($value)) {
			foreach($value as $i=>$r) {
				if(is_string($r)) {
					$value[$i] = mysqli_real_escape_string($this->db, $r);
				}
			}
		}
		
		// Add filter to query
		$field = $this->formatField($field);
		$this->query[$this->query_id]["where"][$group][] = array(
			"sql"		=> $this->formatWhere($field, $value, $params),
			"params"	=> $params
		);
		
		// Set group glue
		$this->query[$this->query_id]["glue"][$group] = $glue;
		
		return $this;
	}
	
	/**
	 * Copy all field data into filters. This function is primarily for internal use. There
	 * are not too many reasons why you would need this when generating a single query. However,
	 * in the event that you do need it, here is how you use it.
	 * 
	 * @example If you have added fields into a query like this...
	 * <code type='php'>
	 * $db->table("users")->addField("email", "foo@bar.com");
	 * </code>
	 * 
	 * @example They could be converted into filters like this...
	 * <code type='php'>
	 * $db->fieldsToFilters();
	 * </code>
	 * 
	 * @example The resulting query would be...
	 * <code type='sql'>
	 * SELECT * FROM users WHERE `user`.`email`='foo@bar.com'
	 * </code>
	 * 
	 * @param Boolean $clearFilters Delete existing filters before copying field data
	 * 
	 * @return void
	 */
	public function fieldsToFilters($clearFilters=false) {
		$query = $this->query[$this->query_id];
		if ($clearFilters) {
			$this->query[$this->query_id]["where"] = array();
		}
		if (count($query["field"])) {
			foreach ($query["field"] as $field=>$value) {
				if (!is_null($value)) {
					$this->query[$this->query_id]["where"][0][] = array(
						"sql" => $this->formatWhere($field, @$value)
					);
				}
			}
		}
	}
	
	/**
	 * Add a custom string into the WHERE clause of a query. The purpose of this function
	 * is to allow complex statements to be injected into the WHERE clause of a SQL statement
	 * when generating the statement would be more work than to simply write it.
	 * 
	 * @example Search for a user by birthday date range
	 * <code type='php'>
	 * $db->table("user")
	 * 	->addWhere("(DATE(user.birthdate) BETWEEN DATE('2011-01-01') AND DATE('2011-02-02'))")
	 * 	->select();
	 * </code>
	 * 
	 * @example Will generate the following SQL
	 * <code type='sql'>
	 * SELECT * FROM user WHERE (DATE(user.birthdate) BETWEEN DATE('2011-01-01') AND DATE('2011-02-02'))
	 * </code>
	 * 
	 * @param String $sql	SQL
	 * @param Array	$params	Array of additional parameters
	 */
	public function addWhere($sql, $params=array()) {
		$group = isset($params["group"]) ? $params["group"] : 0 ;
		$this->query[$this->query_id]["where"][$group][] = array(
			"sql"		=> $sql,
			"params"	=> $params
		);
		
		return $this;
	}
	
	/**
	 * Set glue for grouped filters in the WHERE clause. If a group is not specified when
	 * adding filters to the query, then all filters will automatically be added to group[0]. If
	 * there are multiple groups, they will be glued together in the WHERE clause of the query
	 * using the "AND" glue by default.
	 * 
	 * In the following example, there are two filters in group[0] glued with "AND" by default.
	 * "SELECT * FROM table WHERE (field1=value AND field2=value)"
	 * 
	 * However, if you specify "OR" by using ->setGlue("OR"), the query will look like this.
	 * "SELECT * FROM table WHERE (field1=value OR field2=value)"
	 * 
	 * @group	String	$glue	The glue, "OR", "AND", etc.
	 * @glue	String	$group	Group index
	 * 
	 * @return QueryBuilder
	 */
	public function setGlue($glue, $group=null) {
		
		$query = $this->query[$this->query_id];
		
		if ($group==null && @count($query["glue"])) {
			foreach ($query["glue"] as $group_id=>$row)
				$this->query[$this->query_id]["glue"][$group_id] = $glue;
		} else {
			$this->query[$this->query_id]["glue"][$group] = $glue;
		}
		
		return $this;
	}
	
	/**
	 * Alias for setGlue()
	 * 
	 * @return QueryBuilder
	 */
	public function glue($glue, $group=null) {
		$this->setGlue($glue, $group);
		return $this;
	}
	
	/**
	 * Set the query limit. If no limit is specified, the default limit of 100 will be applied. However,
	 * this default can be changed using QueryBuilder::setDefault().
	 * 
	 * @param Integer	$limit	Limit the number of records returned to this number
	 * 
	 * @return QueryBuilder
	 */
	public function limit($limit=0, $upperLimit=0) {
		$this->query[$this->query_id]["limit"] = $limit;
		return $this;
	}
	
	/**
	 * Return a specific page of results, for pagination. If the limit is "25", and the page is "2", the query
	 * will look like this: "SELECT * FROM table LIMIT 25, 50"
	 * 
	 * @param Integer	$page	Page number
	 * 
	 * @return QueryBuilder
	 */
	public function page($page=1) {
		$this->query[$this->query_id]["page"] = $page;
		return $this;
	}
	
	/**
	 * If you do not want to use both ->limit() and ->page() functions, you can use paginate()
	 * like this ->paginate(array("page"=>2, "limit"=>25));
	 * 
	 * @param Array	$params	Array containing a "limit" and "page" value
	 */
	public function paginate($params=array()) {
		extract($params);
		if (!isset($page)) return $this;
		if (!is_numeric($page)) return $this;
		$this->query[$this->query_id]["page"] = $page;
		if (isset($limit)) {
			if (is_numeric($limit)) {
				$this->query[$this->query_id]["limit"] = $limit;
			}
		}
		return $this;
	}
	
	/**
	 * Apply a mask to grouped filters in a query.
	 * 
	 * If there are three groups of filters, it is possible to set a mask like this:
	 * "((1) AND (2)) OR (3)"
	 * 
	 * Which will generate a query like this:
	 * "SELECT * FROM table WHERE (field1=value) AND (field2=value) OR (field3=value)"
	 * 
	 * @param	String	$mask	String containing the filter mask
	 * @param	Array	$params	Array of additional parameters
	 * 
	 * @return QueryBuilder
	 */
	public function filterMask($mask="", $params=array()) {
		$this->query[$this->query_id]["mask"] = $mask;
		return $this;
	}
	
	/**
	 * Clear any fields that may have been added into the SELECT clause of a query
	 * 
	 * @return QueryBuilder
	 */
	public function clearSelect() {
		$this->query[$this->query_id]["select"] = array();
		return $this;
	}
	
	/**
	 * Unset query pagination, which removes the limit and page from a query.
	 * 
	 * @return QueryBuilder
	 */
	public function clearLimit() {
		unset($this->query[$this->query_id]["limit"]);
		unset($this->query[$this->query_id]["page"]);
		return $this;
	}
	
	/**
	 * Replace the SELECT clause of a query with anything you want.
	 * 
	 * @param String	$sql	Replacement
	 * @param Array		$params	Array of additional parameters
	 * 
	 * @return QueryBuilder
	 */
	public function replaceSelect($sql, $params) {
		$this->query[$this->query_id]["select"] = array();
		return $this->addCustomField($sql, $params);
	}
	
	/**
	 * Add custom SQL to the SELECT clause of your query
	 * 
	 * @param String	$sql	SQL
	 * @param Array		$params	Array of additional parameters
	 * 
	 * @return QueryBuilder
	 */
	public function addSelect($sql, $params=array()) {
		$this->query[$this->query_id]["select"][] = array(
			"sql"		=> $sql,
			"params"	=> $params
		);
		return $this;
	}
	
	/**
	 * Alias for getCount()
	 * 
	 * @return QueryBuilder
	 */
	public function addCount($field="*", $clear=false) { return $this->getCount($field, $clear); }
	
	/**
	 * Get a record count
	 * 
	 * $db->table("table")->getCount()->select(); Will generate "SELECT COUNT(*) FROM table"
	 * 
	 * @param String	$field	field to count, the default is *
	 * @param Boolean	$clear	Clear fields in the SELECT clause
	 * 
	 * @return QueryBuilder
	 */
	public function getCount($field="*", $clear=false) {
		return $this->getSelectFunction($field, $clear, "COUNT");
	}
	public function getSum($field="*", $clear=false) {
		return $this->getSelectFunction($field, $clear, "SUM");
	}
	public function getSelectFunction($field="*", $clear=false, $function) {
		if ($clear)
			$this->clearSelect();
		$this->limit(false);
		$alias = "count";
		if ($field!="*") {
			if (strstr($field, ":")) {
				$fields = explode(":", $field, 2);
				$field = $fields[0];
				$alias = $fields[1];
			}
			$field = $this->formatField($field);
		}
		return $this->addSelectFunction($field, $alias, $function);
	}
	private function addSelectFunction($field, $alias, $function="COUNT") {
		$this->addSelect("$function($field) as $alias");
		return $this;
	}
	
	/**
	 * Get an object containing the filters from any previously executed query by a query ID offset.
	 * Using an offset of "0" will return the filters from the last query.
	 * 
	 * @param Integer	$offset
	 * 
	 * @return QueryBuilder
	 */
	public function getFilters($offset=0) {
		return (object) $this->query[$this->query_id-$offset]["where"];
	}
	
	/**
	 * Add a field to GROUP BY
	 * 
	 * @param String	$field	Field to GROUP BY
	 * @param Array		$params	Array of additional parameters
	 * 
	 * @return QueryBuilder
	 */
	public function addGroup($field, $params=array()) {
		if (is_array($field)) {
			foreach($field as $value)
				$this->addGroup($value);
		} else {
			$this->query[$this->query_id]["group"][] = $field;
		}
		return $this;
	}
	
	/**
	 * Add fields to the ORDER BY clause
	 * 
	 * @example Add a sort
	 * <code type='php'>
	 * // Add a simple sort
	 * $db->addSort("foo");
	 * 
	 * // Add a reverse sort
	 * $db->addSort("foo DESC");
	 * 
	 * // Or like this
	 * $db->addSort("foo", "DESC");
	 * 
	 * // Add multiple sorts
	 * $db->addSort(array(
	 * 	"foo"	=> "DESC",
	 * 	"bar"	=> "ASC"
	 * ));
	 * </code>
	 * 
	 * @param Array/String $field Fields to sort by
	 * @param String $direction	ASC, DESC
	 * 
	 * @return QueryBuilder
	 */
	public function addSort() {
		
		// Get arguments
		@list($field, $direction) = func_get_args();
		$args = func_num_args();
		
		// Make sure they at least passed $field
		if (!$field) return $this;
		
		// If it's a string, then attempt to parse it
		if (is_string($field)) {
			$fields = $this->makeArray($field, ",", " ");
		
		// If it's not a string, it's probably an array
		} else {
			if (is_array($field)) {
				foreach ($field as $f)
					$this->addSort($f, $direction);
				return $this;
			}
		}
		
		// Add the sorts to the query
		if (is_array($fields)) {
			foreach ($fields as $fieldName=>$fieldDirection) {
				$fieldName = $this->formatField($fieldName);
				($d = $fieldDirection) || ($d = $direction);
				$this->query[$this->query_id]["sort"][] = "$fieldName $d";
			}
		}
		return $this;
	}
	
	/**
	 * Randomly sort the results
	 * 
	 * @return QueryBuilder
	 */
	public function randomSort() {
		$this->query[$this->query_id]["sort"][] = "RAND()";
	}
	
	/**
	 * Remove all joins from a query
	 * 
	 * @return Object QueryBuilder
	 */
	public function removeJoins() {
		unset($this->query[$this->query_id]["join"]);
		return $this;
	}
	
	/**
	 * Add an inner join.
	 * 
	 * Join table 1 to table 2 on a specified field:
	 * 
	 * ->joinTable("table1.field=table2.field");
	 * 
	 * Join table 1 to table 2 on a common field:
	 * 
	 * ->joinTable("table2.field");
	 * 
	 * @param	String	$tables	Tables to join
	 * 
	 * @return QueryBuilder
	 */
	public function innerJoin($tables, $params=array()) {
		return $this->addJoin($tables, "INNER", $params);
	}
	
	/**
	 * Alias for innerJoin()
	 * 
	 * @return QueryBuilder
	 */
	public function joinTable($tables, $params=array()) {
		return $this->addJoin($tables, "INNER", $params);
	}
	
	/**
	 * Add a left join.
	 * 
	 * Join table 1 to table 2 on a specified field:
	 * 
	 * ->joinTable("table1.field=table2.field");
	 * 
	 * Join table 1 to table 2 on a common field:
	 * 
	 * ->joinTable("table2.field");
	 * 
	 * @param	String	$tables	Tables to join
	 * 
	 * @return QueryBuilder
	 */
	public function leftJoin($tables, $params=array()) {
		return $this->addJoin($tables, "LEFT", $params);
	}
	
	/**
	 * Add a right join.
	 * 
	 * Join table 1 to table 2 on a specified field:
	 * 
	 * ->joinTable("table1.field=table2.field");
	 * 
	 * Join table 1 to table 2 on a common field:
	 * 
	 * ->joinTable("table2.field");
	 * 
	 * @param	String	$tables	Tables to join
	 * 
	 * @return QueryBuilder
	 */
	public function rightJoin($tables, $params=array()) {
		return $this->addJoin($tables, "RIGHT", $params);
	}
	
	/**
	 * Add join to a query. Like everything else in QueryBuilder, this method requires
	 * that the table has been set by table(). If you are joining tableA to tableB on a commonly
	 * named foreign key, the first parameter only needs to contain "foreign_table.foreign_key".
	 * However, if you wish to join two tables on a field that is named differently in each table,
	 * then it is necessary to list both "table.field" in the first parameter.
	 * 
	 * The second parameter dictates the direction of the join. If a direction is not specified,
	 * the default direction is an INNER JOIN.
	 * 
	 * @example  PHP usage
	 * <code type='php'>
	 * // Join user profile to user with a similarly named foreign key
	 * $db->table("user")->addJoin("profile.user_id");
	 * 
	 * // Join user profile to user with a differently named foreign key
	 * $db->table("user")->addJoin("user.user_id=profile.the_user_id");
	 * 
	 * // Specify a left join
	 * $db->table("user")->addJoin("profile.user_id", "LEFT");
	 * </code>
	 * 
	 * @example  SQL that is generated
	 * <code type='php'>
	 * // Join user profile to user with a similarly named foreign key
	 * SELECT * FROM user INNER JOIN profile ON `profile`.`user_id`=`user`.`user_id`
	 * 
	 * // Join user profile to user with a differently named foreign key
	 * SELECT * FROM user INNER JOIN profile ON `profile`.`the_user_id`=`user`.`user_id`
	 * 
	 * // Specify a left join
	 * SELECT * FROM user LEFT JOIN profile ON `profile`.`user_id`=`user`.`user_id`
	 * </code>
	 * 
	 * @param String $tables Tables to join
	 * @param String $direction Direction of the join, "INNER", "RIGHT", "LEFT", etc.
	 * 
	 * @return QueryBuilder
	 */
	public function addJoin($tables, $direction='INNER') {
		// Allow for :alias at the end of the specification
		@list($tables, $alias) = explode(":", $tables, 2);
		$alias_ = $alias ? " AS $alias " : "";
		
		// Expects table1.field1=table2.field2 or table.field
		@list($part1, $part2) 		= array_reverse(explode("=", $tables, 2));
		@list($field1, $table1) 	= array_reverse(explode(".", $part1, 2));
		@list($field2, $table2) 	= array_reverse(explode(".", $part2, 2));
		$field2 = @$field2 ? $field2 : $field1 ;
		$table2 = @$table2 ? $table2 : $this->query[$this->query_id]["table"] ;
		if (!@$this->query[$this->query_id]["tables"][$table1]) {
			if($alias_) $tables = "$direction JOIN $table1 $alias_ ON $table1.$field1=$alias.$field2"; 
			else $tables = "$direction JOIN $table1 ON $table1.$field1=$table2.$field2";
			$this->query[$this->query_id]["tables"][$table1] = "JOIN";
		} else {
			if($alias_) $tables = "$direction JOIN $table2 $alias_ ON $alias.$field2=$table1.$field1";
			else $tables = "$direction JOIN $table2 ON $table2.$field2=$table1.$field1";
			$this->query[$this->query_id]["tables"][$table2] = "JOIN";
		}
		$this->query[$this->query_id]["join"][] = $tables;
		return $this;
	}
	
	//
	// TODO: Add support for other subqueries, currently only
	// supports a joined subquery. More support to come!
	//
	/**
	 * Create a subquery. A new instance of QueryBuilder will be generated and every parameter that was passed
	 * into this function will be called as functions on the new instance of QueryBuilder. A table must be
	 * specified or the subquery will not work.
	 * 
	 * @example Execute a subquery that counts every purchase every user has made
	 * <code type="php">
	 * $results = $db->table("users")->subQuery(array(
	 * 	"table"     => "orders",
	 * 	"addCount"  => "order_id",
	 * 	"on"		=> "orders.user_id",
	 * 	"addGroup"	=> array("users.user_id")
	 * ))->select()->results;
	 * </code>
	 * 
	 * @example The above example will generate the following SQL
	 * <code type='sql'>
	 * SELECT * FROM users LEFT JOIN
	 * 	(
	 * 		SELECT COUNT(`orders`.`order_id`) as count
	 * 		FROM orders
	 * 		GROUP BY users.user_id
	 * 	)
	 * AS orders ON orders.user_id
	 * </code>
	 * 
	 * @param Array	$params	Array of paramters
	 * @param Array $params["clause"]	Portion of the query to inject the subquery, currently only "join" is supported.
	 * @param Array	$params["type"]		Type of subquery to create, SELECT is default
	 * @param Array $params["table"]	Required parameter to set subquery table
	 * 
	 * @return QueryBuilder
	 */
	public function subQuery($params=false) {
		if (!$params) return;
		
		// Load default parameters
		$defaults = array(
			"clause"	=> "join",
			"type"		=> "select"
		);
		$params = Jackal::merge_arrays($defaults, $params);
		
		// If params is only a string
		if (is_string($params)) {
			$this->query[$this->query_id]["join"][] = $params;
			return $this;
		}
		// Do something if already an instance of QueryBuilder, but nothing right now =(
		if ($params instanceof QueryBuilder) {
			return $this;
		}
		// Call methods of QueryBuilder starting with table
		if (is_array($params)) {
			
			// Parameters
			$p =& $params;
			
			// Required parameters
			$required = array("table");
			foreach ($required as $r) {
				if (!isset($p[$r]))
					return $this;
			}
			
			// Confirm a valid type was passed
			$types 	= array("select", "insert", "delete", "update");
			$type	= $p["type"];
			$continue = false;
			foreach ($types as $t) {
				if ($t==$type)
					$continue = true;
			}
			if (!$continue) return $this;
			
			// Execute table method followed by all passed methods
			$db = new QueryBuilder();
			$methods = get_class_methods($db);
			$nonArrays = array("table", "addCount", "addSelect");
			$nonArrays = array_flip($nonArrays);
			if (!is_array($p["table"])) $p["table"] = array($p["table"]);
			call_user_func_array(array($db, "table"), $p["table"]);
			foreach ($methods as $method) {
				if ($method=="table") continue; // table() must be run first and cannot be run after
				if (isset($p[$method]) && !is_array($p[$method]) && isset($nonArrays[$method])) {
					$p[$method] = array($p[$method]);
				}
				if (@is_array($p[$method])) {
					call_user_func_array(array($db, $method), $p[$method]);
				}
			}
			
			// Get alias
			$alias = $this->getTableAlias($db->getParameter("table"));
			
			// Build SQL statement and add to current statement's join clause
			$sql = $db->buildSQL($type);
			if ($sql) {
				$this->buildSubQuery($p["clause"], Jackal::merge_arrays($params, array(
					"sql"		=> $sql,
					"alias"		=> $alias
				)));
			}
		}
		
		return $this;
	}
	
	/**
	 * Add your own custom join
	 * 
	 * @param String	$sql	SQL
	 * 
	 * @return QueryBuilder
	 */
	public function addCustomJoin($sql) {
		$this->query[$this->query_id]["join"][] = $sql;
		return $this;
	}
	
	/**
	 * Build the subquery
	 * 
	 * @return Boolean
	 */
	protected function buildSubQuery($type="JOIN", $params=array()) {
		$sql 	= "";
		$p 		=& $params;
		
		switch (strtoupper($type)) {
			
			// Build a joined sub query
			case "JOIN":
				$required 	= array("sql", "on", "alias");
				if (!@$p["direction"]) 	$p["direction"] = "LEFT";
				$p["direction"] = strtoupper($p["direction"]);
				foreach ($required as $r) {
					if (!isset($p[$r]))
						return false;
				}
				$p["on"] = str_replace($p["table"], $p["alias"], $p["on"]);
				$sql = "$p[direction] JOIN ($p[sql]) AS $p[alias] ON $p[on]";
				$this->query[$this->query_id]["join"][] = $sql;
				break;
		}
		return true;
	}
	
	/**
	 * Create a subquery subcount. This is useful if you need to count the number of records related to another record.
	 * 
	 * USAGE:
	 * 
	 * ->subCount("table2.field2") will generate
	 * 
	 * SELECT * FROM table LEFT JOIN (SELECT field2, COUNT(field2) AS COUNT FROM table2 GROUP BY field2) AS table_2 ON table_2.field2=table.field2"
	 * 
	 * @param String $statement
	 * @param Array $params Array of additional parameters
	 * 
	 * @return QueryBuilder
	 */
	public function subCount($statement, $params=array()) {
		return $this->sub($statement, $params, "COUNT");
	}
	public function subSum($statement, $params=array()) {
		return $this->sub($statement, $params, "SUM");
	}
	private function sub($statement, $params=array(), $function="COUNT") {
		// supports table1.field1=table2.field2:alias
		// supports table2.field2:alias
		// supports table2.field2
		
		// Get alias
		@list($sql, $alias) = explode(":", $statement, 2);
		$alias = $alias ? $alias : "count" ;
		
		// Get direction
		$direction 	= @$params["direction"] ? $params["direction"] : "LEFT" ;
		$where 		= @$params["where"] ? "WHERE ".$params["where"] : "" ;
		
		// Table table and fields
		@list($part1, $part2) 	= array_reverse(explode("=", $sql, 2));
		@list($field1, $table1) 	= array_reverse(explode(".", $part1, 2));
		@list($field2, $table2) 	= array_reverse(explode(".", $part2, 2));
		$field2 = @$field2 ? $field2 : $field1 ;
		$table2 = @$table2 ? $table2 : $this->query[$this->query_id]["table"] ;
		if (!@$this->query[$this->query_id]["tables"][$table1]) {
			@($functionField = $params["field"]) || ($functionField = $field1);
			$tableAlias = $this->getTableAlias($table1);
			$sql = "$direction JOIN (SELECT $field1, $function($functionField) AS $alias FROM $table1 $where GROUP BY $field1) AS $tableAlias ON $tableAlias.$field1=$table2.$field2";
		} else {
			@($functionField = $params["field"]) || ($functionField = $field2);
			$tableAlias = $this->getTableAlias($table2);
			$sql = "$direction JOIN (SELECT $field2, $function($functionField) AS $alias FROM $table2 $where GROUP BY $field2) AS $tableAlias ON $tableAlias.$field2=$table1.$field1";
			$this->query[$this->query_id]["tables"][$table2] = "JOIN";
		}
		$this->query[$this->query_id]["join"][] = $sql;
		return $this;
	}
	
	/**
	 * Get a table alias to ensure a table is not accidentally referenced more than once per query
	 * 
	 * @param String	$table	Name of the table
	 * 
	 * @return String	Alias
	 */
	public function getTableAlias($table) {
		$i = 2;
		while (!@$created) {
			if (!@$this->query[$this->query_id]["ALIAS"][$table]) {
				$this->query[$this->query_id]["ALIAS"][$table] = $table;
				$created = true;
				return $table;
			} else {
				$table = "$table$i";
			}
			$i++;
		}
		return;
	}
	
	/**
	 * Alias for subCount()
	 * 
	 * @return QueryBuilder
	 */
	public function addSubCount($statement, $params=array()) {
		return $this->subCount($statement, $params);
	}
	
	/**
	 * Copy the a query from previously executed queries into the current query.
	 * 
	 * @param Integer	$offset	"1" will copy the last query, "2" will copy the query before last
	 * 
	 * @return QueryBuilder
	 */
	public function copyLastQuery($offset=1) {
		$this->query[$this->query_id] = $this->query[$this->query_id-$offset];
		return $this;
	}
	
	/**
	 * Execute a count on the last query
	 * 
	 * @param	Integer	$offset
	 * 
	 * @return QueryBuilder
	 */
	public function countLast($offset=1) {
		$this->copylastQuery($offset);
		$this->addCount()->select();
		if (count($this->results)) return $this->results[0]["count"];
			else return false;
	}
	
	public function dump($data, $title="") {
		echo "<pre>";
		if($title) echo "<h2>$title</h2>";
		var_dump($data);
		echo "</pre>";
	}
	
	/**
	 * Generate a SELECT query
	 * 
	 * @return QueryBuilder
	 */
	public function select() {
		
		// Get query SQL
		$sql = $this->buildSQL("SELECT");
		
		// If somehow the query was not valid
		if (!$sql) return $this;
		
		// Run the query
		$this->run($sql);
		
		/*
		// Run the statement
		$result = mysqli_query($this->db, $sql);
		$this->error = mysqli_error($this->db);
		
		// Log error
		if ($this->error) {
			$this->errors[] = array(
				"sql"	=> $sql,
				"error"	=> $this->error
			);
		}
		
		// Get results if there are any
		$this->results = array();
		if (@mysqli_affected_rows($this->db)>0 || @mysqli_num_rows($result)>0)
			$this->results = $this->get_results($result);
		
		// Go to next query
		$this->logQuery($sql);
		*/
		
		// Get debugging information
		$this->debugInfo($sql);
		
		return $this;
	}
	
	/**
	 * Alias for select()
	 */
	public function runSelect() {
		$this->select();
	}
	
	/**
	 * Executes an INSERT query
	 * 
	 * @return QueryBuilder
	 */
	public function insert() {
		$this->query[$this->query_id]["update"] = true;
		
		$sql = $this->buildSQL("INSERT");
		
		// Only execute the query if there is permission to write
		if (self::$writing) {
			$this->run($sql);
		}
		
		$this->debugInfo($sql);
		$this->logQuery($sql);
		return $this;
	}
	
	/**
	 * Execute an UPDATE query
	 * 
	 * @return QueryBuilder
	 */
	public function update() {
		$this->query[$this->query_id]["update"] = true;

		$sql = $this->buildSQL("UPDATE");
		
		// Only execute the query if there is permission to write
		if (self::$writing) {
			$this->run($sql);
		}
		
		$this->debugInfo($sql);
		$this->logQuery($sql);
		return $this;
	}
	
	/**
	 * Execute an INSERT or UPDATE query
	 * 
	 * @return QueryBuilder
	 */
	public function insertOrUpdate() {
		$this->query[$this->query_id]["update"] = true;
		
		$sql = $this->buildSQL("INSERTORUPDATE");
		
		// Only execute the query if there is permission to write
		if (self::$writing) {
			$this->run($sql);
		}
		
		$this->debugInfo($sql);
		$this->logQuery($sql);
		return $this;
	}
	
	/**
	 * Execute a DELETE query
	 * 
	 * @return QueryBuilder
	 */
	public function erase() {
		$this->query[$this->query_id]["update"] = true;
		
		$sql = $this->buildSQL("DELETE");
		
		// Only execute the query if there is permission to write
		if (self::$writing) {
			$this->run($sql);
		}
		
		$this->debugInfo($sql);
		$this->logQuery($sql);
		return $this;
	}
	
	/**
	 * Alias for delete()
	 * 
	 * @return QueryBuilder
	 */
	public function delete() {
		return $this->erase();
	}
	
	// ------------------------------------------------------------------------------------------------------------------------
	// ALL SEARCH OPTIONS
	// ------------------------------------------------------------------------------------------------------------------------
	
	/**
	 * Get all indexed fields on a table with a FULLTEXT
	 * 
	 * @return Array $fields Array of indexed fields
	 */
	public function getIndexFields() {
		
		// Get primary table
		$table = $this->query[$this->query_id]["table"];
		
		// Get fields
		$fields = array();
		if (@count($results = $this->runSQL("SHOW INDEX FROM $table"))) {
			foreach ($results as $row) {
				if ($row["Index_type"]=='FULLTEXT')
					$fields[] = $row["Table"].".".$row["Column_name"];
			}
		}

		return $fields;
	}
	
	/**
	 * Execute a search query. If the table has a FULLTEXT index on any fields, it will generate a query
	 * using MATCH AGAINST and words provided by QueryBuilder::addWords(). If the table does not have a 
	 * FULLTEXT index it will generate a query using LIKE '%words%'.
	 * 
	 * If the table has a FULLTEXT index the query will look like this:
	 * 
	 * SELECT MATCH(field) AGAINST ('+ words') AS score FROM table MATCH(field) AGAINST ('+ words') IN BOOLEAN MODE
	 * 
	 * If the table does not have a FULLTEXT index, the query will look like this:
	 * 
	 * SELECT * FROM table WHERE field LIKE '%words%'
	 * 
	 * @return QueryBuilder
	 */
	public function search() {
		$sql = $this->buildSQL("SEARCH");
		$this->run($sql);
		$this->debugInfo($sql);
		$this->logQuery($sql);
		return $this;
	}
	
	/**
	 * Add fields to use in the query generated by search()
	 * 
	 * @param String/Array	$fields	String or array of fields to search
	 * 
	 * @return QueryBuilder
	 */
	public function addSearchField($fields) {
		if (is_string($fields)) {
			$fields = $this->makeArray($fields);
		}
		if (is_array($fields)) {
			foreach ($fields as $field=>$value) {
				$this->query[$this->query_id]["likeFields"][] = $this->formatField($field);
			}
		}
		return $this;
	}
	
	/**
	 * Add words to the search query. Every word you add will be added to a single string
	 * to be matched against in the query.
	 * 
	 * If the table has a FULLTEXT index the query will look like this:
	 * 
	 * SELECT MATCH(field) AGAINST ('+ words') AS score FROM table MATCH(field) AGAINST ('+ words') IN BOOLEAN MODE
	 * 
	 * If the table does not have a FULLTEXT index, the query will look like this:
	 * 
	 * SELECT * FROM table WHERE field LIKE '%words%'
	 * 
	 * @param String	$words	Space delimited words
	 * @param Array		$params	Array of additional parameters
	 * 
	 * @return QueryBuilder
	 */
	public function addWords($words, $params=array()) {
		if (@$params["fields"]) {
			$this->addSearchField($params["fields"]);
		}
		if (is_string($words)) {
			$words = str_replace(",", " ", $words);
			$words = str_replace("  ", " ", $words);
			return $this->addWords(explode(" ", $words));
		}
		if (is_array($words)) {
			if (count($words)) {
				foreach($words as $word)
					$this->query[$this->query_id]["words"][] = mysqli_real_escape_string($this->db, $word);
			}
		}
		return $this;
	}
	
	/**
	 * Manually add fields to be matched against
	 * 
	 * @param String/Array $fields
	 * @param Array $params Array of additional paramters
	 * 
	 * @return QueryBuilder
	 */
	public function addMatch($fields, $params=array()) {
		if (is_string($fields)) {
			$fields = str_replace(",", " ", $fields);
			$fields = str_replace("  ", " ", $fields);
			$this->addMatch(explode(" ", $fields));
		}
		if (is_array($fields)) {
			if (count($fields)) {
				foreach($fields as $field) {
					// Make sure the match fields are correctly formatted as table.field
					list($f, $t) = array_reverse(explode(".", $field, 2));
					if (!$t) $t = $this->query[$this->query_id]["table"];
					$this->query[$this->query_id]["match"][] = "$t.$f";
				}
			}
		}
		return $this;
	}
	
	/**
	 * Build a query without executing it and return the SQL
	 * 
	 * @param String	$type	Type of query to build
	 * @param Integer	$query_id	ID of the query to execute
	 * 
	 * @return QueryBuilder
	 */
	public function getSQL($type='SELECT', $query_id=0) {
		return $this->buildSQL(strtoupper($type), $query_id);
	}
	
	/**
	 * Get the last inserted key
	 * 
	 * return Integer The last inserted key
	 */
	public function lastKey() {
    	return $this->db->insert_id;
    }
	
	/**
	 * Get the number of affected rows from a specified query. If an ID is not provided, the 
	 * affected rows from the last query will be returned
	 * 
	 * @param $query_id
	 */
	public function affected($query_id=0) {
		$query_id = $query_id ? $query_id : $this->query_id-1 ;
		return $this->queries[$query_id]["affected"];
	}
	
	protected function fail() {
		echo "Query failed!";
	}
	
	/**
	 * Build query
	 * 
	 * @param String $type			The type of query to build
	 * @param Integer $query_id 	The ID of the query to build
	 * 
	 * @return	String	The generated SQL
	 */
	protected function buildSQL($type, $query_id=false) {
		$query_id = $query_id ? $query_id : $this->query_id ;

		$query = $this->query[$query_id];
		
		if (!$this->validQuery($query)) return;
		
		switch (strtoupper($type)) {
			
			case "SELECT":
				$sql = $this->buildSelectSQL($query);
				break;
			
			case "INSERT":
				$sql = $this->buildInsertSQL($query);
				break;
			
			case "INSERTORUPDATE":
				$function = self::$functions["buildInsertUpdateSQL"];
				$sql = $this->$function($query);
				break;
			
			case "UPDATE":
				$sql = $this->buildUpdateSQL($query);
				break;
			
			case "DELETE":
				$sql = $this->buildDeleteSQL($query);
				break;
			
			case "SEARCH":
				$sql = $this->buildSearchSQL($query);
				break;
		}
		
		return $sql;
	}
	
	/**
	 * Build SQL for a SELECT query
	 * 
	 * @param Array $query	The query to process
	 * 
	 * @return String	The SELECT query
	 */
	protected function buildSelectSQL($query) {
		
		// Select statement
		$sql = "SELECT ";
		
		// Get fields, filters, and joins
		$fields 	= $this->prepareSelectFields();
		$filters 	= $this->prepareFilters();
		$joins		= $this->prepareJoins();
		
		// Add fields
		$sql .= join(" ,", $fields);
		
		// From statement
		$sql .= " FROM $query[table] ";
		
		// Add Joins
		if ($joins)
			$sql .= " $joins ";
		
		// Add Filters
		if ($filters)
			$sql .= " WHERE $filters";
		
		// Add groups
		if (@count($query["group"]))
			$sql .= " GROUP BY ".join(", ", $query["group"]);
		
		// Add sorts
		if (@count($query["sort"]))
			$sql .= " ORDER BY ".join(", ", $query["sort"]);
		
		// Add limit
		$sql .= $this->prepareLimit();
		return $sql;
	}
	
	/**
	 * Build SQL for an INSERT or UPDATE query in MySQL4
	 * 
	 * @param Array $query	The query to process
	 * 
	 * @return String	The query
	 */
	protected function buildInsertUpdateSQL4($query) {
		$this->fieldsToFilters();
		$sql = "";
		$results = $this->runSQL($this->buildSelectSQL($query));
		if (count($results)) {
			$sql = $this->buildUpdateSQL($query);
		} else {
			$sql = $this->buildInsertSQL($query);
		}
		return $sql;
	}
	
	/**
	 * Build SQL for an INSERT or UPDATE query in MySQL5
	 * 
	 * @param Array $query	The query to process
	 * 
	 * @return String	The query
	 */
	protected function buildInsertUpdateSQL5($query) {
		$updateSQL = $this->buildUpdateSQL($query, false);
		$insertSQL = $this->buildInsertSQL($query);
		return "$insertSQL ON DUPLICATE KEY $updateSQL";
	}
	
	/**
	 * Build SQL for an INSERT query
	 * 
	 * @param Array $query	The query to process
	 * 
	 * @return String	The query
	 */
	protected function buildInsertSQL($query) {
		$fields = array();
		if (@is_array($query["field"])) {
			foreach ((array) $query["field"] as $field=>$value) {
				if (!is_numeric($value)) $value = "'".mysqli_real_escape_string($this->db, $value)."'";
				$fields[$field] = $value;
			}
		}
		// Overwrite with values specifically intended for an insert
		if (@is_array($query["field_insert"])) {
			foreach ((array) $query["field_insert"] as $field=>$value) {
				if (!is_numeric($value)) $value = "'".mysqli_real_escape_string($this->db, $value)."'";
				$fields[$field] = $value;
			}
		}
		$sql = "INSERT INTO $query[table] (".implode(", ", array_keys($fields)).") VALUES (".implode(", ", $fields).") ";
		return $sql;
	}
	
	/**
	 * Build SQL for an UPDATE query
	 * 
	 * @param Array $query	The query to process
	 * 
	 * @return String	The query
	 */
	protected function buildUpdateSQL($query, $includeTable=true) {
		$sql = "";
		if (@count($query["where"]) || !$includeTable) {
			$fields = array();
			if (@count($query["field"])) {
				foreach ($query["field"] as $field=>$value) {
					if (is_numeric($value)) {
						@$fields[$field] = "$field=$value";
					} else {
						@$fields[$field] = "$field='".mysqli_real_escape_string($this->db, $value)."'";
					}
				}
			}
			// Ovewrite with values specifically intended for an update
			if (@count($query["field_update"])) {
				foreach ($query["field_update"] as $field=>$value) {
					if (is_numeric($value)) {
						@$fields[$field] = "$field=$value";
					} else {
						@$fields[$field] = "$field='".mysqli_real_escape_string($this->db, $value)."'";
					}
				}
			}
			
			$table = $includeTable ? "$query[table] SET" : "" ;
			$sql = "UPDATE $table ".implode(", ", $fields)." ";
			
			$filters 	= $this->prepareFilters();
			$joins		= $this->prepareJoins();
			
			// Add Joins
			if ($joins)
				$sql .= " $joins ";
			
			// Add Filters
			if ($filters)
				$sql .= " WHERE $filters";
			
		}
		return $sql;
	}
	
	/**
	 * Build SQL for a DELETE query
	 * 
	 * @param Array $query	The query to process
	 * 
	 * @return String	The query
	 */
	protected function buildDeleteSQL($query) {
		$sql = "";
		if (@count($query["where"])) {
			$sql = "DELETE FROM $query[table] ";
			
			$filters 	= $this->prepareFilters();
			$joins		= $this->prepareJoins();
			
			// Add Joins
			if ($joins)
				$sql .= " $joins ";
			
			// Add Filters
			if ($filters)
				$sql .= " WHERE $filters";
		}
		return $sql;
	}
	
	/**
	 * Build SQL for a SELECT query with MATCH AGAINST or LIKE '%%'
	 * 
	 * @param Array $query	The query to process
	 * 
	 * @return String	The query
	 */
	protected function buildSearchSQL($query) {
		
		// Add match fields if they don't exist
		if (!@count($query["match"])) {
			$fields = $this->getIndexFields();
			$this->addMatch($fields);
		}
		
		// Add match to where clause
		if (@count($query["match"])) {
			$this->addWhere("MATCH(".join(", ",$query["match"]).") AGAINST ('+".join(" +", $query["words"])."' IN BOOLEAN MODE)");
			$fields[] = " MATCH(".join(", ",$query["match"]).") AGAINST ('".join(" ", $query["words"])."') AS score ";
		} else {
			if (@count($query["likeFields"]) && count($query["words"])) {
				foreach ($query["likeFields"] as $field) {
					foreach ($query["words"] as $word) {
						$this->addWhere("$field LIKE '%$word%'");
					}
				}
			}
		}
		
		// Get fields, words, and match fields
		$fields 	= $this->prepareSelectFields();
		$filters 	= $this->prepareFilters();
		$joins		= $this->prepareJoins();
		
		// Select statement
		$sql = "SELECT ";
		
		// Add fields
		$sql .= join(" ,", $fields);
		
		// From statement
		$sql .= " FROM $query[table] ";
		
		// Add Joins
		if ($joins)
			$sql .= " $joins ";
		
		// Add Filters
		if ($filters)
			$sql .= " WHERE $filters";
		
		// Add groups
		if (@count($query["group"]))
			$sql .= " GROUP BY ".join(", ", $query["group"]);
		
		// Add sorts
		if (@count($query["sort"])) {
			$sql .= " ORDER BY ".join(", ", $query["sort"]);
		} else {
			if (@count($query["match"]))
				$sql .= " ORDER BY score DESC";
		}
		
		// Add limit
		$sql .= $this->prepareLimit();
		return $sql;
	}
	
	/**
	 * Make sure the query is valid before processing.
	 * 
	 * @param Array $query	The query to validate
	 * 
	 * @return Boolean
	 */
	protected function validQuery($query=array()) {
		$requiredKeys = array(
			"table"
		);
		foreach ($requiredKeys as $key) {
			if (!isset($query[$key]))
				return false;
		}
		return true;
	}
	
	/**
	 * Build the LIMIT statement for the query
	 * 
	 * @return String	The LIMIT clause
	 */
	protected function prepareLimit() {
		$query 	= $this->query[$this->query_id];
		if (!isset($query["page"]) && !isset($query["limit"])) return "";
		$page 	= isset($query["page"]) ? $query["page"] : 1 ;
		$limit	= isset($query["limit"]) ? $query["limit"] : $this->defaults["limit"] ;
		if (!$limit) return "";
		return " LIMIT ".(($page-1) * $limit).", $limit";
	}
	
	/**
	 * Build fields for the SELECT clause, if there are no fields then an asteriks will be used
	 * 
	 * @return Array array of fields
	 */
	protected function prepareSelectFields() {
		$query = $this->query[$this->query_id];
		$fields = array();
		if (@count($query["field"])) {
			foreach ($query["field"] as $field=>$value)
				$fields[] = $field;
		}
		if (@count($query["select"])) {
			foreach ($query["select"] as $field)
				$fields[] = $field["sql"];
		}
		// Default select everything if nothing selected
		if (!count($fields))
			$fields[] = $this->defaults["field"];
		return $fields;
	}
	
	/**
	 * Process a query and return it's joins
	 * 
	 * @param Integer	ID of the query to process for joins
	 * 
	 * @return String Join statements
	 */
	protected function prepareJoins($query_id=false) {
		$query_id = $query_id ? $query_id : $this->query_id ;
		$query = $this->query[$query_id];
		if (@count($query["join"]))
			return implode(" ", $query["join"]);
		return "";
	}
	
	/**
	 * Build filters for the WHERE clause of a query and apply masks as needed
	 * 
	 * @param Integer	ID of the query
	 * 
	 * @return String The filteres formatted for a query
	 */
	protected function prepareFilters($query_id=false) {
		$query_id = $query_id ? $query_id : $this->query_id ;
		$query = $this->query[$query_id];
		
		// Get all filters
		$statement 	= array();
		$types 		= "";
		$values 	= array();
		$mask 		= array();
		if (@count($query["where"])) {
			foreach ($query["where"] as $group=>$filters) {
				$sql = array();
				foreach ($filters as $filter) {
					$sql[] = $filter["sql"];
					if (!@is_array($filter["value"]))
						$values[] = @$filter["value"];
					$types .= @$filter["type"] ? $filter["type"] : "" ;
				}
				
				$mask[] = "([$group])";
				
				// Replace in future with "group pattern"
				$glue = @$query["glue"][$group] ? $query["glue"][$group] : "AND" ;
				$statement[$group][] = implode(" $glue ", $sql);
			}
			
			// Use provided mask if exists, otherwise use a default mask
			$this->query[$query_id]["mask"] = (isset($query["mask"])) ? $query["mask"] : implode(" AND ", $mask) ;
			
		}
		
		return $this->applyMask($statement);
	}
	
	/**
	 * Apply any masks that were added through addMask()
	 * 
	 * @param String Filters
	 * 
	 * @return String Filters
	 */
	protected function applyMask($filters) {	
		$query = $this->query[$this->query_id];
		
		if (count($filters)) {
			$patterns 		= array();
			$replacements 	= array();
			foreach ($filters as $group=>$sql) {
				$patterns[] 	= "/\\[$group]/";
				$replacements[] = @implode(" ".$query["glue"][$group]." ", $sql);
			}
			return preg_replace($patterns, $replacements, $query["mask"]);
		}
		return "";
	}
	
	/**
	 * Format a field and it's value for there WHERE clause of a query 
	 * 
	 * @param String $field
	 * @param String $value
	 * @param Array $params
	 * 
	 * @return String Formatted filter
	 */
	protected function formatWhere($field, $value='', $params=array()) {
		
		// If the field value is NULL
		if (is_array($value)) {
			$operator = @$params["operator"] ? $params["operator"] : "IN" ;
			if (@$params["operator"]=="!=" || @$params["operator"]=="<>" || @$params["operator"]=="not") $operator = "NOT IN";
			return "$field $operator ('".implode("', '", $value)."')";
		} else {
			if (strtoupper($value)=='NULL') {
				$operator = @$params["operator"] ? $params["operator"] : "IS" ;
				return "$field $operator NULL";
			}
			$value = mysqli_real_escape_string($this->db, $value);
		}
		
		$operator = isset($params["operator"]) ? $params["operator"] : "=" ;
		switch (strtoupper($operator)) {
			case "LIKE":
				return "$field LIKE '%$value%'";
				break;
		}
		
		if (isset($params["function"])) {
			switch (strtoupper($params["function"])) {
				case "DATE":
					return "DATE($field)".$operator."DATE('$value')";
					break;
				
				case "TIME":
					return "TIME($field)".$operator."TIME('$value')";
					break;
				
				case "LIKE":
					return "$field LIKE '%$value%'";
					break;
			}
		}
		
		return "$field $operator '$value'";
	}
	
	/**
	 * Properly format a field for SQL using `table`.`field`
	 * 
	 * @param String $sql The table and field name
	 * 
	 * @return String Formatted table.field
	 */
	protected function formatField($sql) {
		@list($field, $table) = array_reverse(explode(".", $sql, 2));
		@$table = $table ? $table : $this->query[$this->query_id]["table"] ;
		if ($table && !$this->query[$this->query_id]["table"])
			$this->query[$this->query_id]["table"] = $table;
		if ($table)
			$this->query[$this->query_id]["tables"][$table] = "PRIMARY";
		if ($field=="*") return "`$table`.*";
		return "`$table`.`$field`";
	}
	
	/**
	 * Get the current pre-processed query
	 * 
	 * @return Array The current query
	 */
	protected function currentQuery() {
		$query_id = $query_id ? $query_id : $this->query_id ;
		return $this->query[$query_id];	
	}
	
	/**
	 * Parse a string of variables into an array
	 * 
	 * @param String $queryString String of variables
	 * @param String $d1 First delimiter
	 * @param String $d2 Second delimiter
	 * 
	 * @return Array Variables
	 */
	protected function makeArray($queryString="", $d1="&", $d2="=") {
		$array = array();
		$queryString = "1|$queryString$d1";
		strtok($queryString, "|");
		while($name = strtok($d1)) {
			@list($key, $val) = explode($d2, trim($name));
			$array[$key] = $val;
		}
		return $array;
	}
	
	/**
	 * Log the query
	 * 
	 * @param String $sql The query
	 * 
	 * @return void
	 */
	protected function logQuery($sql) {
		
		// Log the query and explanation somewhere
		if(self::$logging) {
			$result = mysqli_query($this->db, "EXPLAIN $sql");
			$explain = $this->get_results($result);
		
			$lines = time()." : $sql\r\n";
			foreach($explain as $rows) {
				foreach($rows as $key=>$value) {
					$lines .= "\t$value";  
				}
				$lines .= "\r\n";
			}
			fwrite(self::$logPointer, $lines);
		}
		
		$this->queries[$this->query_id] = array(
			"sql"		=> $sql,
			"affected"	=> mysqli_affected_rows($this->db)
		);
		
		// Make sure the class is not storing more queries than it needs to
		if (count($this->queries)>self::$saveMax) {
			array_shift($this->queries);
		}
		
		// Make sure the class is not storing more query data than it needs
		if (count($this->query)>self::$saveMax) {
			array_shift($this->query);
		}
		
		// Make sure the class is not storing more errors than it needs to
		if (count($this->errors)>self::$maxErrors) {
			array_shift($this->errors);
		}
		
		$this->query_id++;
	}

}

?>