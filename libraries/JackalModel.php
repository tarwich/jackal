<?php

Jackal::loadLibrary("Spyc");

/**
 * The base model class that other model classes (should) extend
 * 
 * @author SammyD
 */
class JackalModel {
	/**
	 * A list of queries that were recently run.  This helps prevent running a 
	 * query for the exact same information twice in one HTTP request
	 * 
	 * @var array 
	 */
	private $_cache = array();
	
	/**
	 * Default values for different types 
	 * 
	 * When a blank object is created, the values for each field will be pulled
	 * from this array.
	 * 
	 * @var array
	 */
	protected static $_blankTypes = array(
		"char"    => "",
		"varchar" => "",
		"int"     => 0,
		"float"   => 0.0,
		"bit"     => 0,
	);
	
	/**
	 * A list of the local fields that refer to other tables
	 * 
	 * This is used when building a query in order to determine how to get 
	 * data from another table. This array contains a map of what field links
	 * to what field in order to determine how to join on different tables
	 * 
	 * @var array 
	 */
	protected $_foreignKeys = array();
	
	/**
	 * If true, when writing to the database, prepare primitives by typecasting 
	 * 
	 * If this is true, then {@link http://www.php.net/settype settype} 
	 * is called on each field prior to passing to the database. This ensures
	 * that a zero-length integer field will be passed as an integer instead
	 * of a null string.
	 * 
	 * @var boolean 
	 */
	protected $formatWrites = true;
	
	/**
	 * Keywords to provided extended functionality from load() (or find())
	 * 
	 * Array of words that can be passed into the filter that do things other  
	 * than filter (such as sort). Keywords can be extended by each model by
	 * adding to this array. The following keywords are available by
	 * default:
	 * 
	 * <b>:SORT</b> @see QueryBuilder::addSort()
	 * 
	 * <b>:RSORT</b> Same as :SORT, but DESC
	 * 
	 * <b>:GROUP</b> @see QueryBuilder::addGroup()
	 * 
	 * <b>:COUNT</b> @see QueryBuilder::addCount()
	 * 
	 * <b>:LIMIT</b> @see QueryBuilder::limit()
	 * 
	 * @var array 
	 */
	protected static $keywords = NULL;
	
	/**
	 * Flag to tell JackalModel to prefix all tables with primary table name
	 * 
	 * If set to true, JackalModel will prefix all the tables in the model with
	 * the same name as the primary table.  The primary table will be 
	 * un-prefixed, so this only applies if there are more than one table.
	 * 
	 * @var bool  
	 */
	protected $prefixTables = false;
	
	/**
	 * The main table - used for some assumptions throughout the code
	 * 
	 * JackalModel refers back to this to remember which table is the primary
	 * table in the list. 
	 * 
	 * @var array
	 */
	private $_primaryTable = NULL;
	
	/**
	 * Flag to tell JackalModel to remove old fields in fixDB()
	 * 
	 * If this is true, then JackalModel will check for fields that no longer
	 * belong in the table when fixDB() is called. JackalModel will <a href='http://dev.mysql.com/doc/refman/5.1/en/alter-table.html#id2967188'>drop</a> 
	 * these fields if the flag is set to true, and ignore them if set to false.
	 * 
	 * @var bool
	 */
	protected $removeAliens = false;
	
	/**
	 * This is what determines if a field is slow (not fast)
	 * @var array
	 */
	private static $_slowFields = array(
		"tinytext"   => 1,
		"text"       => 1,
		"mediumtext" => 1,
		"longtext"   => 1
	);
	
	/**
	 * An internal array used to store the information about the tables in this 
	 * model
	 * @var array
	 */
	private $_structures = NULL;
	
	/**
	 * The table map is a mapping of all the names that tables could be looked 
	 * up by 
	 * @var string
	 */
	private $_tableMap = NULL;
	
	/**
	 * These are the fields that will exist on a table structure
	 * 
	 * Because Jackal is a flat system, value-objects don't exist, and 
	 * therefore we need some sort of structure definition and this is it.
	 *  
	 * @var array
	 */
	private static $_tableStructureFields = array(
		"fastFields",
		"fields", 
		"model", 
		"name", 
		"primary", 
		"prefix",
		"shortName", 
	);
	
	/**
	 * A mapping from MySQL types to PHP types
	 * 
	 * @var array
	 */
	public static $typeMap = array(
		// Grouped by SQL 92 grouping http://en.wikipedia.org/wiki/SQL
		"character" => "string",
		"char"      => "string",
		"varchar"   => "string",
		
		"bit"       => "integer",
		"tinyint"   => "integer",
		
		"integer"   => "integer",
		"int"       => "integer",
		"smallint"  => "integer",
		"bigint"    => "integer",
		"float"     => "float",
		"real"      => "float",
		"double"    => "float",
	);
	
	/**
	 * Creates a new instance of JackalModel
	 * 
	 * This method does initialization of the JackalModel base class the first 
	 * time that it is called. If two classes extend JackalModel, then the 
	 * constructor will only setup JackalModel the first time.
	 * 
	 * This is required in order to utilize JackalModel in your code as it 
	 * contains the basic setup and configuration of JackalModel
	 * 
	 */
	function __construct() {
		if(!self::$keywords) {
			self::$keywords = array();
			// SORT
			self::$keywords[":SORT"] = create_function('$data, $query, $model', '$query->addSort($data);');
			// R SORT
			self::$keywords[":RSORT"] = create_function('$data, $query, $model', '$query->addSort($data, "DESC");');
			// GROUP
			self::$keywords[":GROUP"] = create_function('$data, $query, $model', '$query->addGroup($data);');
			// COUNT
			self::$keywords[":COUNT"] = create_function('$data, $query, $model', '$query->addCount($data);');
			// LIMIT
			self::$keywords[":LIMIT"] = create_function('$data, $query, $model', '$query->limit($data);');
			// OR
			self::$keywords[":OR"] = create_function('$data, $query, $model', '$query->addFilter($data[0], $data[1], array("glue" => "OR"));');
			// LIKE
			//self::$keywords[":LIKE"] = create_function('$data, $query, $model', '$query->addFilter($data, NULL, array("operator" => "LIKE"));');
			
			// Assembly language style functions GT, GE, LT, LE, EQ, NE
			
			// GT - Greater than
			self::$keywords[":GT"] = create_function('$data, $query, $model', '$query->addFilter($data[0], $data[1], array("operator" => ">"));');
			
			// Special purpose keywords
			self::$keywords[":EXACT"] = create_function('', '');
		}
		
		// If this is the base class, then return
		if(get_class($this) == __CLASS__) return;
	}
	
	/**
	 * Adds calculated fields to data
	 * 
	 * This method adds calculated fields to the data and returns the new array.
	 * 
	 * Currently the supported fields are:
	 * 
	 * <b>created:</b> Set the first time that a record is created (if there is
	 * no primary key).
	 * 
	 * <b>updated:</b> Set for all subsequent updates to a record.
	 * 
	 * <b><i>Note:</i></b> At the moment this feature is in beta 
	 * 
	 * @param array $data The associative array of data that represents a record
	 * @param array $fields Array of field names to insert (created|updated) 
	 * 
	 * @return array 
	 */
	protected function addCalculations($data, $fields=NULL) {
		if(!$fields) $fields = array("created", "updated");
		// Key the fields array on the field names
		$fields = array_combine($fields, $fields);
		
		$data = array_merge(array(
			$fields["created"] => date("Y-m-d H:i:s"),
			$fields["updated"] => date("Y-m-d H:i:s"),
		), $data);
		
		return $data;
	}
	
	/**
	 * Get the table and the derivation from the URI
	 * 
	 * This is an internal method used by JackalModel to determine how to handle
	 * $URI passed into find() save() or delete(). The logic used by these 
	 * methods should be the same so that there is less confusion on how to 
	 * use JackalModel -- for this purpose these methods all call analyzeURI in
	 * order to parse the URI.
	 * 
	 * This method looks for the following items:
	 * 
	 * <b>$table(s)</b>: Tables come from either $URI[0] or $URI["tables"] and 
	 * 		is either a comma delimited list of table names or an array of 
	 * 		table names
	 * 
	 * <b>$derivation</b>: Array of name=>value filters where 'name' is the name
	 * 		of the field to filter and 'value' is the value desired. Value can
	 * 		be an array for a query such as "WHERE field IN (a, b)". If name
	 * 		begins with a colon (:), then it is a keyword. The default keywords
	 * 		are in $keywords. 
	 * 
	 * @param array $URI The original URI passed from Jackal::model()
	 * 
	 * @return array
	 */
	protected function analyzeURI($URI) {
		// See that tables they're looking for
		($tables = @$URI[0])
		|| ($tables = @$URI["tables"]);
		
		if(is_numeric($tables)) $tables = null;

		// If the table still wasn't found, then load the primary table
		if(!$tables) {
			// Parse the table definition
			$this->getStructure();
			// Get the primary table name
			$tables = $this->_primaryTable["name"];
		}
		
		// Get the paths
		$paths = @$URI[2];
		// Make sure that tables is a comma delimited list of tables
		if(is_array($tables)) $tables = implode(",", $tables);
		// The derivation should be the rest of the URI
		$derivation = (array) $URI;
		// If $derivation[1] is present and is a number, then only the ID was 
		// passed
		if(is_numeric($id = @$derivation[1])) $derivation = $id;
		// If $derivation[1] is present and is an array, the derivation was 
		// passed into [1]
		elseif(is_array(@$derivation[1])) $derivation = $derivation[1]; 
		
		// If derivation is an integer, then resolve the primary field of the primary table
		if(is_numeric($derivation)) {
			// Get the name of the first table off the stack
			$firstTable = strtok($tables, ", .");
			// Get the structure of the first table
			$table = $this->getTable($firstTable);
			// Change derivation to be an associative array of $key=>$value where
			// $key is the primary key of the primary table and $value is the 
			// old integer value of $derivation
			$derivation = array($table["primary"] => $derivation);
		}
		
		// Remove 0 and segments from the URI
		unset($derivation[0], $derivation["segments"]);
		// Prepare the array for returning
		return array(
			"tables"		=> isset($tables) ? $tables : array(),
			"derivation"	=> isset($derivation) ? $derivation : array(),
			"options"		=> isset($options) ? $options : array(),
			"paths"			=> isset($paths) ? $paths : array()
		);
	}

	/**
	 * Returns the number of matching rows
	 * 
	 * Calls load() much the same way that find() does, but passes a flag stating
	 * to only return the count of the matching records.
	 * 
	 * Segments: table
	 * 
	 * @param string $URI[table] Name of table to search
	 * @param array $URI Associative array of filters (see find())
	 * 
	 * @return int
	 */
	public function count($URI) {
		// Set default values
		$tables = array(); $derivation = array();
		// Parse the URI
		extract($this->analyzeURI($URI));
		// Tell load that we're just looking for a count
		$derivation[":COUNT"] = "*";
		// Let load handle the rest
		$result = $this->load($tables, $derivation);
		// Return the result
		return (integer) @$result[0]["count"];
	}

	/**
	 * Delete a record or series of records from the database
	 * 
	 * This method deletes records from the table or tables specified in URI
	 * that match the filter specified in URI. This method gets $tables and 
	 * $derivation from analyzeURI()
	 * 
	 * Segments: table
	 * 
	 * @param string $URI[table] Table(s) to delete from
	 * @param array $URI[...] Derivation 
	 * 
	 * @return void
	 */
	public function delete($URI) {
		// I keep forgetting to pass an array from within the model
		assert('is_array($URI)');
		// Parse the URI
		extract($this->analyzeURI($URI));
		// Get the name of the delegate method
		$delegate = "delete" . ucfirst($tables);
		// If the delegate exists, then run it instead of load
		if(method_exists($this, $delegate)) $result = $this->$delegate($derivation);
		// Let load handle the rest
		else $result = $this->doDelete($tables, $derivation);
		// Flush the cache so that the record will not longer show up
		$this->flush();
		// Return the result
		return (array) $result;
	}
	
	/**
	 * Deletes the entry from $table that matches $filter
	 * 
	 * Internal method used to delete the record from the table. This is usually
	 * called directly from delete() after being parsed with analyzeURI()
	 * 
	 * @param string $table The name of the table to delete from 
	 * @param array $filter The filter of records to delete (see find()) 
	 * 
	 * @return void
	 */
	protected function doDelete($table, $filter) {
		// Get the structure of first table
		$tableStructure = $this->getTable($table, $this->getStructure());
		// Find the name of the first table
		$tableName = $tableStructure["name"];
		// Get the instance of QueryBuilder
		$query = Jackal::loadLibrary("QueryBuilder");
		// Add the table to the query 
		$query->table($tableName);
		
		// See if only the ID was passed 
		if(is_numeric($filter)) {
			// Get the primary key of the first table
			$primaryField = $tableStructure["primary"];
			$query->addFilter($primaryField, $filter);
		} else {
			// Iterate over each filter
			foreach($filter as $name=>$value) {
				// Add the filter to the querybuilder
				$query->addFilter($name, $value);
			}
		}
		
		// Do the delete
		$query->delete();
	}
	
	/**
	 * Perform a search in the model and return the results
	 * 
	 * This method calls analyzeURI() in order to parse the table+derivation
	 * and passes the results to load(), which searches for the records and 
	 * returns an array of results.
	 * 
	 * Derivation is an associative array of name=>value where 'name' is the 
	 * name of a field in the table and 'value' is the value desired. Value can
	 * also be an array if the field should be any of the values in said array.
	 * 
	 * Segments: table
	 * 
	 * @param string $URI[table] The name of the table
	 * @param array $URI[...] Derivation
	 * 
	 * @return array
	 */
	public final function find($URI) {
		// Set the defaults
		$tables = array(); $derivation = 0;
		// Parse the URI
		extract($this->analyzeURI($URI));
		// Allow cached responses
		if($cached = @$this->_cache[$tables][serialize($derivation)]) return $cached;
		// Get the name of the delegate method (foo.*,bar.* -> findFooBar)
		$delegate = "find" . preg_replace('/(\w+)\W*,?/e', 'ucfirst("$1")', $tables);
		// If the delegate exists, then run it instead of load
		if(method_exists($this, $delegate)) return $this->$delegate($derivation);
		// Let load handle the rest
		$result = $this->load($tables, $derivation);
		// Cache the response
		$this->_cache[$tables] = (array) @$this->_cache[$tables];
		$this->_cache[$tables][serialize($derivation)] = $result;
		// Return the result
		return (array) $result;
	}
	
	/**
	 * Determines the manner in which $fromTable relates to $toTable
	 * 
	 * The array returned will be 
	 * <code type="php">
	 * 	array(
	 * 		"direction" => "LEFT", // Or "RIGHT" 
	 * 		"join"      => "$fromTable.$field1 = $toTable.$field2", 
	 * 	);
	 * </code>
	 * 
	 * @param mixed $fromTable The name (or tableStructure) of the source table
	 * @param mixed $toTable The name (or tableStructure) of the destination table
	 * 
	 * @return array of join information or null if no join available 
	 */
	public static function findJoin($fromTable, $toTable) {
		$fromTable = $this->getTable($fromTable);
		$toTable = $this->getTable($toTable);
		
		if($join = $fromTable["joins"][$toTable]) {
			return $join;
		}
		
		elseif($join = $toTable["joins"][$fromTable]) {
			return $join;
		}
		
		else {
			return NULL;
		}
	}
	
	/**
	 * Executes find and returns the first item
	 * 
	 * Simply put, this method calls find() but only returns the first item. 
	 * In the future this method will also use the :LIMIT keyword in order to 
	 * tell the database to do less work, but for the time being, it just 
	 * returns only one item.
	 * 
	 * @param array $URI @see find() 
	 * 
	 * @return array The first record in the result set
	 */
	public final function findOne($URI) {
		// If exact isn't set, then set it to true
		$URI = ((array) $URI) + array(":EXACT" => true);
		// Execute the find
		$results = (array) $this->find($URI);
		// Get just the first item
		@list($result) = $results;
		// Return the result
		return $result;
	}
	
	/**
	 * Retrieves the first record, or a blank record if none found
	 * 
	 * This function calls findOne() and passes $URI. If nothing was found,
	 * then it calls getBlank() and passes $URI. In the future, this method will
	 * only pass tables to getBlank.
	 * 
	 * @param array $URI @see find()
	 * 
	 * @return array 
	 */
	public final function findOrBlank($URI) {
		// Execute the find
		$result = (array) $this->findOne($URI);
		// If nothing found, then create a blank
		if(!$result) $result = $this->getBlank($URI);
		// Return the result
		return $result;
	}
	
	/**
	 * Rebuilds the tables for this model
	 * 
	 * This method gets the structure of the model and rebuilds all the tables.
	 * If a table doesn't exist, then it will be created. If a column doesn't 
	 * exist, then it will be added. If a column has changed it will be 
	 * modified. If a column does not exist and $removeAliens is true, then it
	 * will be dropped.
	 * 
	 * Currently this method does not parse the column information from the 
	 * database, so it can only tell if a column exists or not. If a column 
	 * exists, then this method modifies the column, because it can't tell if 
	 * it's different or not.
	 * 
	 * Old tables do not get deleted with this method, because it has no way of
	 * knowing that an old table used to belong to this model.
	 *
	 * @return void
	 */
	public function fixDB() {
		// Don't fix the table more than once in the same pass. If errors are
		// still occurring, then it's not my fault
		if(@$this->_fixed) return false;
		
		?>
		<style type='text/css'>
			.repair-error {
				background-color: #fdd;
				color: black;
				font-family: monospaced;
			}
			.repair-information {
				background-color: #ddf;
				color: black;
				font-family: monospace;
			}
			.repair-action {
				background-color: #ddd;
				color: black;
				font-family: monospaced;
				padding-left: 0.25in;
			}
			.repair-table {
				margin-top: 25px; 
				background-color: #ddf;
				color: black;
				font-family: monospace;
				font-weight: bold;
			}
			
			.terminal .repair-error {
				background-color: inherit;
				color: #B44;
			}
			
			.terminal .repair-information {
				background-color: inherit;
				color: #3366cc;
			} 
			.terminal .repair-action {
				background-color: inherit;
				color: inherit;
			}
			.terminal .repair-table {
				background-color: inherit;
				color: #3366cc;
			}
		</style>
		<?php 
		
		// Get the structure data
		$structure = $this->getStructure();

		// Rebuild each table
		foreach($structure as $table) {
			// Fix the table
			$this->fixTable($table["name"], $table["primary"], $table["fields"]);
			
			// Find out how many rows are in the table
			$query = Jackal::loadLibrary("QueryBuilder");  (false) || ($query = new QueryBuilder()); 
			$results = $query->table($table["name"])->addCount("*")->select()->results;
			if(!$results[0]["count"]) $insertTables[] = $table["name"];
		}
		
		// Tell the model to insert defaults 
		$this->insertDefaults((array) @$insertTables);
		
		// Remember that we've fixed the database
		$this->_fixed = true;
	}
	
	/**
	 * Called by fixDB to fix one table at a time.  
	 * 
	 * The purpose of this function is to keep the fixDB function clean. This 
	 * method is responsible for checking the individual columns of each table.
	 * 
	 * If a column doesn't exist, then it is added.
	 * 
	 * If a column exists and shouldn't and $removeAliens is set, then it is 
	 * dropped.
	 * 
	 * If a column exists then it is modified, because the system currently 
	 * cannot parse the column definition to see if it needs changed or not.
	 * 
	 * @param string $table Name of the table to fix
	 * @param string $primary Name of the primary key field
	 * @param array $fields Array of field definitions
	 * 
	 * @return void
	 */
	private function fixTable($table, $primary, $fields) {
		echo '<div class="repair-table">Rebuilding ', $table, '</div>';
		
		// Get the QueryBuilder
		$qb = Jackal::loadLibrary("QueryBuilder");
		/* @var $qb QueryBuilder */
		$qb->debug(true);
		
		// Get table information
		echo '<div class="repair-action">', $sql = "DESCRIBE `$table`;", '</div>';
		$description = $qb->runSQL($sql);
		
		if(!$description) {
			// Remove the 'table does not exist' error
			@array_pop($qb->errors);
			
			// Table didn't exist, so build from scratch
			$fieldSet = "";

			if($primary)
			$primaries = "primary key ($primary)";

			foreach($fields as $fieldName=>$fieldDefinition)
			$fieldSet .= "`$fieldName` $fieldDefinition, ";
			
			echo '<div class="repair-action">', 
				$sql = "CREATE TABLE `$table` ($fieldSet $primaries);",
				'</div>';
			$qb->run($sql);
		} else {
			// Keep track of the fields we have yet to implement
			$fieldsLeft = $fields;

			// Table found, build it out
			$fields = $description;
			
			foreach($fields as $field) {
				$fieldName = $field["Field"];
				
				// If we're NOT supposed to have this field
				if(!@$fieldsLeft[$fieldName]) {
					// And if we're supposed to remove aliens
					if($this->removeAliens) {
						echo '<div class="repair-action delete-action">', 
							$sql = "ALTER TABLE `$table` DROP COLUMN `$fieldName`;",
							'</div>';
						$qb->run($sql);
					}
				} else {
					// We're supposed to have this field
					$fieldDefinition = $fieldsLeft[$fieldName];
					echo '<div class="repair-action change-action">', 
						$sql = "ALTER TABLE `$table` MODIFY COLUMN `$fieldName` $fieldDefinition;",
						'</div>';
					$qb->run($sql);
					// Remove this field from the list of fields we have to implement
					unset($fieldsLeft[$fieldName]);
				}
			}

			// Add the leftover fields
			foreach($fieldsLeft as $fieldName=>$fieldDefinition) {
				if($fieldName == $primary) {
					echo '<div class="repair-action add-action">', 
						$sql = "ALTER TABLE `$table` ADD COLUMN `$fieldName` $fieldDefinition PRIMARY KEY;",
						'</div>';
					$qb->run($sql);
				} else {
					echo '<div class="repair-action">', 
						$sql = "ALTER TABLE `$table` ADD COLUMN `$fieldName` $fieldDefinition;",
						'</div>';
					$qb->run($sql);
				}
			}
		}
		
		//  __________________________________________________
		// / Output errors                                    \
		
		if(count((array) $qb->errors)) {
			foreach(@ (array) $qb->errors as $error) $errors[] = $error["error"];
			echo '<div class="repair-error">', implode("<br>", $errors), '</div>';
			$qb->errors = array();
			echo '<div class="repair-information"><b>', $table, '</b> finished rebuilding with errors.</div>';
		} else {
			echo '<div class="repair-information"><b>', $table, '</b> rebuilt successfully.</div>';
		}
		
		// \__________________________________________________/
		
		$qb->debug(false);
	}
	
	/**
	 * Clear all the cached result sets
	 * 
	 * This method is used to erase the internal record cache. 
	 * 
	 * When find() is called it stores the results in an array with the tables
	 * and derivation as the key. If the same query is called a second time, it
	 * returns the previous results instead of communicating with the database
	 * a second time.
	 * 
	 * If you need to clear those results for any reason, simply call flush and
	 * it will erase the entire cache.
	 * 
	 * When save() is called, it calls flush() automatically 
	 * 
	 * @return void
	 */
	public final function flush() {
		$this->_cache = array();
	}
	
	/**
	 * Returns an empty result as if it were queried from the database.
	 * 
	 * Pass in a table specification in order to determine the results to get.
	 * 
	 * Segments: table
	 * 
	 * @param string $URI[table] Name of the table to simulate
	 * 
	 * @return array
	 */
	public function getBlank($URI) {
		// I keep forgetting to pass URI as an array when calling directly
		assert('is_array($URI); // You must pass an array to getBlank');
		// TODO: Cache the blank item
		
		// Get the structure
		$this->getStructure();
		// Figure out what the table name should be
		($tableName = @$URI["table"])
		|| ($tableName = @$URI[0])
		|| ($tableName = $this->_primaryTable["name"]);
		
		// Allows for multiple inheritance
		$tables = explode(",", $tableName);
		// Initialize the result structure
		$result = array();
		
		foreach($tables as $tableName) {
			$table = strtok($tableName, ". "); 
			// Get the physical table
			$table = $this->getTable($table);
			
			// Iterate over each field in the table
			foreach($table["fields"] as $fieldName=>$fieldType) {
				// Make sure we only have the 'type' of the field and nothing else
				$type = strtok($fieldType, " ");
				// Map this type to a default value
				$value = @self::$_blankTypes[$type];
				// See if this type is meant to be executed
				if($value[0] == "!") $value = eval($value);
				// Finally, insert the value into the structure
				$result[$fieldName] = $value;
			}
		}
		
		return $result;
	}
	
	/**
	 * Returns the structure definition for this object
	 * 
	 * Called by JackalModel to determine the structure of the model. Models 
	 * that extend JackalModel should override this method and return a {@link http://www.yaml.org/ YAML}
	 * string 
	 * 
	 * Every key will be seen as a table, with the name of the key as the table
	 * name. If $prefixTables is true then JackalModel will prefix all tables 
	 * (except the first one) with the name of the first table (ie table2 
	 * becomes table1_table2). This helps to keep module tables grouped 
	 * together.
	 * 
	 * Every child of that table is converted into a field where the name is 
	 * the name of the field and the (string) value is the MySQL field 
	 * definition.
	 * 
	 * If a field begins with an exclamation mark (!) then it is seen as the 
	 * primary index for that table. This is used by JackalModel when attempting 
	 * to resolve {@link http://dev.mysql.com/doc/refman/5.0/en/join.html join} 
	 * relationships 
	 * 
	 * If a field definition is prefixed with -> and contains the name of 
	 * another table, then JackalModel will try to create a join relationship
	 * to that foreign table, and will use the field definition of the foreign
	 * key as the definition for the local field. For non-primary tables in
	 * another model the table name should be the name of the model followed
	 * by the name of the table (ie OtherModel_table2).
	 * 
	 * @example This is sample YAML string for a model of widgets
	 * 
	 * <code class='brush:yaml'>
	 * widget:
	 * 		!widget_id: int not null auto-increment
	 * 		name: varchar(100)
	 * 		type: -> type
	 * type:
	 * 		!type_id: int not null auto_increment
	 * 		name: varchar(25)
	 * </code>
	 *
	 * @return string definition of model
	 */
	protected function getDefinition() { }
	
	/**
	 * Returns the structure of this model 
	 * 
	 * Parses getDefinition() and returns an array with the interpreted 
	 * structure. 
	 * 
	 * This method could be overwritten by your class if you decided not to 
	 * implement getDefinition, but it is not recommended 
	 * 
	 * @return array structure of model
	 */
	public function getStructure() {
		// Allow caching
		if($this->_structures) return $this->_structures;
		
		// Initialize the structures array
		$this->_structures = array();
		
		// Get the structure declaration
		$structureString = $this->getDefinition();

		// Parse the structure
		$structure = Spyc::YAMLLoadString($structureString);
		
		// Parse each table
		foreach($structure as $tableName=>$tableStructure) {
			// Prepare fields array
			$fastFields = 
			$fields = array();
			// The primary field default to the first field in the table.  If 
			// the table definition explicitly states that a different field
			// should be the primary, then it will automatically replace the 
			// original primary key
			$primary = key($tableStructure);
			
			// Iterate over all the fields
			foreach($tableStructure as $fieldName=>$fieldType) {
				// Is this field a primary key?
				if(substr($fieldName, 0, 1) == "!") {
					// Pull out the !
					$fieldName = ltrim($fieldName, "! ");
					// Remember which field was the primary field
					$primary = $fieldName;
				}
				
				// If this field is NOT slow
				(@self::$_slowFields[$fieldType])
				// Then add it to the fast fields
				|| ($fastFields[$fieldName] = $fieldType);
				
				// Store the field in the fields list
				$fields[$fieldName] = $fieldType;
			}
			
			// Remember the short name
			$shortName = $tableName;
			
			// If this is the primary table
			if(!@$this->_primaryTable) {
				// If we're supposed to prefix tables
				if($this->prefixTables) {
					$prefix = is_string($this->prefixTables) ? $this->prefixTables : $tableName;
					if($prefix != $tableName) $tableName = "${prefix}_$tableName";
				} else $prefix = "";
			}
			
			// If this isn't the primary table
			else {
				// If we're supposed to prefix tables
				if($this->prefixTables) {
					// Rename the table appropriately 
					// and map the old table name to the new one 
					$tableName = 
					$this->_tableMap[$tableName] = 
					$prefix."_".$tableName;
				} else $prefix = "";
			}
			
			// Get the name of the model
			($modelName = @$this->modelName) || ($modelName = implode("", explode("Model", get_class($this))));
			
			// Store the current table into structures
			$this->_structures[$tableName] = array(
				"name"       => $tableName, 
				"shortName"  => $shortName, 
				"model"      => $modelName,
				"primary"    => $primary, 
				"prefix"     => $prefix,
				"fields"     => $fields,
				"fastFields" => $fastFields
			);
			
			// Map the table
			$this->_tableMap[$tableName] = $tableName;
			
			// Set the primary table to the first one.  If you're wondering 
			// why I'm doing this for every iteration... it's because it's 
			// actually faster than any conditional statement you could use
			$this->_primaryTable = reset($this->_structures);
		}
		
		// 
		// Until now we couldn't parse foreign keys because they might have 
		// been trivial references, or even circular references.  Now it's time
		// to look up all the foreign keys.
		// 
		
		// Iterate over each table looking for foreign keys
		foreach($this->_structures as $i=>$table) {
			// Iterate over each field
			foreach($table["fields"] as $j=>$field) {
				// Is this a link to a foreign field?
				if(substr($field, 0, 2) == "->") {
					// Remove the link symbol from the field name
					$field = ltrim($field, "-> ");
					// Remeber that this is a foreign key
					$this->_foreignKeys[$j] = $field;
					
					//
					// Find the foreign field definition
					//
					$otherTable = $this->getTable($field);
					$otherField = $otherTable["primary"];
					$definition = $otherTable["fields"][$otherField];
					
					// Let the definition default to INT
					if($definition) {
						// Strip illogical definitions
						$definition = preg_replace('/(\bnot null\b|\bauto.?increment\b|\bprimary\b|\bkey\b)/', '', $definition);
						// Clean up a bit
						$definition = trim($definition);
					} else {
						$definition = "int";
					}
					
					// Update the local table structure
					$this->_structures[$i]["fields"][$j] = $definition;
					$this->_structures[$i]["foreignTables"][$j] = $otherTable;
					$this->_structures[$i]["joins"][$otherTable["name"]] = array(
						"direction"    => "LEFT",
						"foreignTable" => $otherTable["name"],
						"foreignField" => $otherField,
						"localTable"   => $table["name"],
						"localField"   => $j
					);
				}
			}
		}
		
		// And give back the new structures array
		return $this->_structures;
	}
	
	/**
	 * Finds a table and returns the structure
	 * 
	 * This method looks up a table with $tableName and returns the result of
	 * getStructure(). This is the root method used throughout the system to
	 * find a table. 
	 * 
	 * If the $tableName is not found in this model, then it will be searched
	 * for in other models. The search begins with models with the exact name
	 * of the other table. If the table wasn't found, then the search splits
	 * the table name on _ and uses the first half as the model name and the 
	 * second half as the table name (ie model1_table2).
	 * 
	 * If $structure is passed, then getTable will search that structure first
	 * before looking around in the system. This is useful if you are looking
	 * for a table in a specific context and there is a high probability of
	 * the table being found in that structure as it saves time.
	 * 
	 * @param string $tableName The name of the table to find
	 * @param array $structure A structure hint to search in
	 * 
	 * @return array the table structure of the found table, or false upon 
	 * 		failure
	 */
	public function getTable($tableName, $structure = NULL) {
		Jackal::loadHelper("grammar");
		
		// See if $tableName is in fact a table (allows this function to be called as a wrapper)
		if(@array_keys($tableName) === self::$_tableStructureFields) return $tableName;
		// Allow for calling from Jackal::model
		if(is_array($tableName)) @( list($tableName, $structure) = $tableName );
		// Default to the structure of this model
		if(!$structure) $structure = $this->getStructure();
		
		// If a reference model was passed...
		if($structure) {
			// See if the table is in the structure
			if($t = @$structure[$tableName]) return $t;
			// See if the table is in the structure but has a prefix
			$prefix = reset($structure);
			$prefix = $prefix["prefix"];
			if($t = @$structure["{$prefix}_$tableName"]) return $t;
		}
		
		// See if the class is local to this model
		if(@$this->_tableMap[$tableName]) {
			$class = $this;
		} else {
			// Get the class from Jackal
			$class = Jackal::getModelClass(pluralize($tableName));
			
			// If the class wasn't found, then we need to find it by breaking 
			// apart by _
			if(!$class) {
				// Get the first part
				$model = strtok($tableName, "_");
				
				// Walk through class looking for the table part
				while(($table = strtok("_")) !== false) {
					// If this is the model, then we're done
					if($class = Jackal::getModelClass(pluralize($model))) break;
					// If it wasn't the model, then perhaps the table is part of the model name
					$model = "${model}_$table";
				}
			}
		}
		
		// Make sure a class was found
		if(!$class) {
			Jackal::error(500, "Unable to find table '$tableName'");
			return false;
		}
		
		// Get the structure from the class
		$structure = $class->getStructure();
		// See if the structure is keyed on table name
		if($theTable = @$structure[$tableName]) return $theTable;
		// Nope, find it by looping through the array
		foreach($structure as $theTable) 
		if($theTable["name"] == $tableName) break;
		
		// This might or might not be a good thing to put in the log
		if(!$theTable) Jackal::error(500, "Unable to find table $tableName");
		
		// At this point everything should be in order
		return $theTable;
	}
	
	/**
	 * Insert default records into table when performing a fixDB()
	 * 
	 * This method is called just before fixDB terminates. The purpose of this
	 * method is to allow models to bring default data with them. 
	 * 
	 * This method should be overridden in your modules in order to implement
	 * the desired functionality.
	 * 
	 * @param array $tables This array will contain the name of every empty
	 * 		table in the model. If a table is non-empty it will not be present
	 * 		in this array.
	 * 
	 * @return void
	 */
	protected function insertDefaults($tables=array()) {}
	
	/**
	 * Builds and executes a query and returns the results in an array
	 * 
	 * This method will create a query based on the information provided and
	 * pass it to QueryBuilder, returning the results in an array. 
	 * 
	 * $tables should be a comma separated string with the name of each table
	 * to query. The table may be followed by .field in order to include only
	 * that field. Multiple fields may be included as 
	 * "<code>table.field1,table.field2</code>". If a table is specified without
	 * the .field suffix, then load will add all the fields of that table 
	 * (with the exception of $_slowFields). If all the fields are desired, 
	 * then use "table.*".
	 * 
	 * $filter is an associative array ($key=>$value) where $key is the field 
	 * and $value is the value desired. If $value is an array, then any of the
	 * values in that array are valid matches (as opposed to all are required). 
	 * 
	 * @param string $tables comma separated list of tables to return
	 * @param string $filter associative array of filters
	 * @param string $paths comma separated list of paths (not implemented) 
	 * @param QueryBuilder $query instance of QueryBuilder to append to
	 * 
	 * @return array
	 */
	protected function load($tables, $filter, $paths="", $query=null) {
		// Initialize variables
		$fields 			= array(); // A list of fields we're going to add
		$currentTables 		= array(); // A list of tables already present in the query
		$tableStructures 	= array(); // The structure of all the tables in the query

		// Parse the table structure for this model
		$this->getStructure();
		// Break apart tables by comma
		preg_match_all('/'.
			'(\w+)'                 . //      (table name)
			'(?:\.([^,]+))?'        . // .*   (field)
			'(?:\s*AS\s*([\w+$]+))?'. // AS * (table aliases)
			',?'                    . // ,    (delimiter)
		'/i', $tables, $matches, PREG_SET_ORDER);
//		@list($nothing, $tables["table"], $tables["field"], $tables["tag"]) = @$tables;

		// Change tables from the scan string to the
		$tables = array();

		// Convert tables into an array, and assign each result
		foreach($matches as $i=>$result) {
			$tables[$i] = array(
				"table" => @$result[1],
				"field" => @$result[2],
				"alias" => @$result[3],
			);
		}

		//  _________________________________
		// /---------- Table paths ----------\
		
		// Break apart the path by comma
		$paths = explode(",", implode("", (array) $paths));
		// Break apart each path by ->
		foreach($paths as $i=>$path) ($path = $paths[$i] = explode("->", $path)) && ($target = end($path)) && ($paths[$target] = $path); 
		// Get the QueryBuilder instance
		if(!$query) $query = Jackal::loadLibrary("QueryBuilder"); /* @var $query QueryBuilder */
		
		// Replace all instances of a pathed table with the actual path
		for($i = count($tables)-1; $i>=0; --$i) {
			// Get the path that corresponds to this table
			$path = (array) @$paths[$tables[$i]["table"]];
			
			// If a path was found, we need to inject it into the query
			if($path) {
				$newTables = array_slice($path, 0, -1);
				foreach($newTables as $i=>$name) $newTables[$i] = array( "table" => $name, "field" => "-" );
				if(@$newTables[0]) array_splice($tables, $i, 0, $newTables);  
			}
		}
		
		// \_________________________________/
		
		// ----------
		// Add tables
		// -----
		
		// Until nothingFound
		do {
			// Will be set to true later is something is found
			$somethingFound = false;
			
			// Go through the rest of the tables and add them all as joins
			foreach($tables as $i=>$table) {
				// Find the structure of the first table
				$tableStructure = $this->getTable(@$table["table"], $this->getStructure());
				// Find the name of the primary table
				$tableName = $tableStructure["name"];
				
				//  ____________________________________
				// /---------- Remember field ----------\
				
				// Get the name of the field
				$fieldName = @$table["field"];
				
				// - is a special field that means no fields
				if($fieldName == "-");
				// If the field is blank, then get just the fast fields
				elseif(!$fieldName) {
					// If the model declares a set of fastfields 
					if($fastFields = @$tableStructure["fastFields"]) {
						// Allow all fields in this table to be tagged
						if($tag = @$table["tag"]) {
							foreach($fastFields as $fieldName=>$discard) {
								$FieldName = ucfirst($fieldName);
								$fields[] = $tableName . '.' . $fieldName . ' AS ' . preg_replace('/(\$\w+)/e', '$1', $tag);
							}
						} 
						// No tag was specified, so add the fields normally
						else {
							$fields[] = "$tableName." 
							. implode(", $tableName.", array_keys($fastFields));
						}
					} else {
						$fields[] = "$tableName.*";
					}
				} else {
					// Add this field to the list we're going to look for
					$fields[] = "$tableName.$fieldName";
				}
				
				// \____________________________________/
				
				// If this table is already in the query, then skip it
				if(in_array($tableName, $currentTables)) {
					$somethingFound = true;
					unset($tables[$i]);
					continue;
				}
				
				//  ___________________________________
				// /---------- Primary Table ----------\
				if(!count($currentTables)) {
					// Remember that this table is in the query
					$currentTables[] = $tableName;
					// Store the table structure
					$tableStructures[$tableName] = $tableStructure;
					// Add this table to the query
					$query->table($tableName);
					// Remove this table
					unset($tables[$i]);
					// Remember that we've found something
					$somethingFound = true;
				}
				// \___________________________________/
				
				//  ______________________________
				// /---------- Add join ----------\
				else {
					
					// Get this table structure
					$table = $this->getTable($tableName, $this->getStructure());
					
					// Add joins by inferred paths (tables already present 
					// in query, or present in the model)
					
					// TODO: Move this into a method that other classes can call
					
					// Go through all the tables in this query
					foreach($currentTables as $otherTableName) {
						// Get the structure for this table
						$otherTable = $this->getTable($otherTableName, $this->getStructure());
						// See if any of the fields in the table link to the destination table
						if($join = @$otherTable["joins"][$tableName]) {
							$query->addJoin("$join[localTable].$join[localField]=$join[foreignTable].$join[foreignField]", $join["direction"]);
							// Remove this table from the todo list
							unset($tables[$i]);
							// We found something, so we should loop again see if there any tables left
							$somethingFound = true;
						} elseif($join = @$table["joins"][$otherTableName]) {
							// The other table actually joins to this one
							
							// Add the join
							$query->addJoin("$join[localTable].$join[localField]=$join[foreignTable].$join[foreignField]", $join["direction"]);
							// Remove this table from the todo list
							unset($tables[$i]);
							// We found something, so we should loop again see if there any tables left
							$somethingFound = true;
						}
					}
					
					// Report as error if nothing found
					if(!$somethingFound) Jackal::error(500, "Unable to find join to $tableName from " . implode(", or ", $currentTables));
					// Remember that we've added this table
					$currentTables[] = $tableName;
					// Store the table structure
					$tableStructures[$tableName] = $tableStructure;
				}
				// \______________________________/
				
			} // End foreach
		} while($somethingFound); 
		
		//  _______________________________________
		// /---------- Add select fields ----------\
		$fields = array_combine($fields, $fields);
		foreach($fields as $field) $query->addSelect("$field");
		// \_______________________________________/
		
		//  ______________________________
		// /---------- Keywords ----------\
		$keywords = array_intersect_key((array) $filter, self::$keywords);
		
		if(count($keywords)) {
			foreach($keywords as $function=>$data) {
				$f = self::$keywords[$function];
				$f($data, $query, $this);
			}
			
			$filter = array_diff_key($filter, $keywords);
		}
		// \______________________________/
		
		//  _____________________________________
		// /---------- Add the filters ----------\
		
		// See if only the ID was passed 
		if(is_numeric($filter)) {
			// Get the primary key of the first table
			$primaryField = $tableStructure["primary"];
			$query->addFilter($primaryField, $filter);
		} else {
			// Iterate over each filter
			foreach($filter as $name=>$value) {
				// Find the table this filter pertains to
				foreach($tableStructures as $tableStructure) {
					if(@$tableStructure["fields"][$name]) {
						// Add the filter to the querybuilder
						$query->addFilter("$tableStructure[name].$name", $value);
						// Quit searching for the table
						break;
					}
				}
			}
		}
		
		// If the 'exact' keyword is set, then there must be a filter -- 
		// otherwise return an empty array
		if(@$keywords[":EXACT"]) if(!$filter) return array();
		
		// \_____________________________________/
		
		// Execute the query
		$results = $query->select()->results;
		
		// Finally, return the results 
		return $results;
	}
	
	/**
	 * Attempts to load one record and returns a blank record if none found
	 * 
	 * @see load
	 * 
	 * @return array
	 */
	protected function loadOrBlank() {
		$URI = func_get_args();
		$result = call_user_func_array(array($this, "load"), $URI);
		@list($result) = $result;
		if(!$result) $result = $this->getBlank($URI);
		return $result;
	}
	
	/**
	 * Creates or updates a record in the database
	 * 
	 * If a primary key is present in the data, then this method calls update(),
	 * otherwise this method calls insert(). The primary key is decided based
	 * on the ! field in the getDefinition.
	 * 
	 * This method checks to see if the model contains a special save method 
	 * with the name of the table being update (ie saveTable1) and executes
	 * that method with $URI[data]. In order to save from this delegate method,
	 * you must call write. Otherwise, you will create an infinite loop.
	 * 
	 * Segments: table / data
	 * 
	 * @param string $URI[table] The name of the table to update
	 * @param array $URI[data] The data to save
	 * 
	 * @return array 
	 */
	public function save($URI) {
		// Parse the structure
		$this->getStructure();
		
		// Get the table
		@($tableName = @$URI[0])
		|| @($tableName = @$URI["table"])
		|| ($tableName = $this->_primaryTable["name"]);
		// Get the table
		$tableStructure = $this->getTable($tableName, $this->getStructure());
		// Get the data
		@(
		($data = $URI["data"])
		|| ((is_array($URI[1])) && ($data = $URI[1]))
		|| ($data = $URI)
		);
		
		if($tableStructure["model"]."Model" != get_class($this)) {
			return Jackal::model($tableStructure["model"]."/save/$tableStructure[name]", $data);
		} 
		
		// Figure out what a custom method would be named
		$method="save$tableStructure[shortName]";
		
		// Save the data
		if(method_exists($this, $method)) {
			// Allow for custom saver
			$data = $this->$method($data);
		} else {
			// Handle the save myself
			$data = $this->write($tableName, $data);
		}
		
		// Flush the cache so that the new values will show up
		$this->flush();
		// Remove the table name from the results
		unset($data[0]);
		
		return $data;
	}
	
	/**
	 * Updates the record in the database
	 * 
	 * This method is intended to be called from write()
	 * 
	 * After this method updates the data it returns the data
	 * 
	 * Segments: table / data
	 * 
	 * @param string $URI[table] Name of the table to update
	 * @param array $URI[data] Data to update
	 * 
	 * @return array
	 */
	protected function update($URI) {
		//  __________________________________________________
		// / Parse URI                                        \
		
		($tableName = $URI["table"]) || ($tableName = @$URI[0]);
		($data = $URI["data"]) || ($data = @$URI[1]);;

		// \__________________________________________________/
		
		
		// Get the table structure
		$tableStructure = $this->getTable($tableName, $this->getStructure());
		// Get the right table name
		$tableName = $tableStructure["name"];
		// Get the instance of QueryBuilder
		$query = Jackal::loadLibrary("QueryBuilder");
		// Select the table
		$query->table($tableName);
		// Get the name of the primary key
		$primaryFieldName = $tableStructure["primary"];
		// Add the ID filter
		$query->addFilter($primaryFieldName, $data[$primaryFieldName]);
		// Add precalculated fields
		$data = $this->addCalculations($data);
		// Add only the fields from data that are in the table structure
		$values = array_intersect_key($data, $tableStructure["fields"]);
		
		// See if we're supposed to perform typecasting
		if($this->formatWrites) {
			// Go through all the fields in the structure
			foreach($values as $name=>$value) {
				// Get the field definition
				$field = $tableStructure["fields"][$name];
				// Get the PHP type for ths field definition
				($type = @self::$typeMap[ strtok($field, " (\t") ])
				// Default to string type
				|| ($type = "string");
				// Type cast
				settype($value, $type);
				// Add field to query
				$query->addField($name, $value);
			}
		} else {
			// Dump all the fields in
			$query->addFields($values);
		}
		
		// Run the query
		$query->update();
	}
	
	/**
	 * Called by save() or its delegates in order to execute the write
	 * 
	 * This method checks to see if an insert or update is required and performs 
	 * them appropriately, returning the data inserted or updated. If a new
	 * record is inserted, then the returned data will contain the new ID.
	 * 
	 * @param string $tableName The name of the table to update
	 * @param array $data The data to insert or update
	 *  
	 * @return array
	 */
	protected function write($tableName='', $data='') {
		// Get the table structure
		$tableStructure = $this->getTable($tableName, $this->getStructure());
		// Get the right table name
		$tableName = $tableStructure["name"];
		// Get the instance of QueryBuilder
		$query = Jackal::loadLibrary("QueryBuilder"); 
		/** @var QueryBuilder $query */  
		// Select the table
		$query->table($tableName);
		
		//  __________________________________________________
		// / Add precalculated fields                         \
		
		// If created exists and is a datetime field
		if(preg_match("/datetime/i", @$tableStructure["fields"]["created"])) $query->addInsertField("created", date("Y-m-d H:i:s"));
		// If created exists and is an int field
		if(preg_match("/int/i", @$tableStructure["fields"]["created"])) $query->addInsertField("created", time());
		// If updated exists and is a datetime field
		if(preg_match("/datetime/i", @$tableStructure["fields"]["updated"])) $query->addField("updated", date("Y-m-d H:i:s"));
		// If updated exists and is an int field
		if(preg_match("/int/i", @$tableStructure["fields"]["updated"])) $query->addField("updated", time());
		
		// \__________________________________________________/
		
		// Add only the fields from data that are in the table structure
		$values = array_intersect_key($data, $tableStructure["fields"]);
		// If this is an insert query unset the primary key field so that it doesn't get sent
		if(!@$data[ $tableStructure["primary"] ]) unset($values[ $tableStructure["primary"] ]);
		
		// See if we're supposed to perform typecasting
		if($this->formatWrites) {
			// Go through all the fields in the structure
			foreach($values as $name=>$value) {
				// Get the field definition
				$field = $tableStructure["fields"][$name];
				// Get the PHP type for ths field definition
				($type = @self::$typeMap[ strtok($field, " (\t") ])
				// Default to string type
				|| ($type = "string");
				// Type cast
				settype($value, $type);
				// Add field to query
				$query->addField($name, $value);
			}
		} else {
			// Dump all the fields into the query
			$query->addFields($values);
		}
		
		$query->insertOrUpdate();
		(@$data[ $tableStructure["primary"] ]) 
		|| ($data[ $tableStructure["primary"] ] = $query->lastKey());
		
		// Flush the cache so that the new values will show up
		$this->flush();
		// Return the updated information
		return $data;
	}
}
