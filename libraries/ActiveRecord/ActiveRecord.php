<?php

Jackal::loadLibrary("ActiveRecordJoin", "ActiveRecordWhere");

class ActiveRecord implements ArrayAccess, Iterator {
	/**
	 * The database to which we will connect
	 */
	public $db = null;
	
	/**
	 * Log of queries this class has run
	 * 
	 * @var array
	 */
	public static $log = array();
	
	// List of aliases and table names in order to get the last one used (for joining purposes)
	public $aliases      = array();
	public $fields       = array();
	public $groups       = array();
	public $isExplain    = false;
	public $joins        = array();
	public $limitFrom    = null;
	public $limitTo      = null;
	public $orders       = array();
	public $tables       = array();
	public $values       = array();
	public $whereClauses = array();
	
	public static $defaults = array(
		"aliases"      => array(),
		"fields"       => array(),
		"groups"       => array(),
		"isExplain"    => false,
		"joins"        => array(),
		"limitFrom"    => null,
		"limitTo"      => null,
		"orders"       => array(),
		"tables"       => array(),
		"values"       => array(),
		"whereClauses" => array(),
	);
	
	public function all() {
		// Initialize the results array
		$results = array();
		// Add each result to the array
		while($row = $this->next()) $results[] = $row;
		
		return $results;
	}
	
	public function clear() {
		$this->aliases      = array();
		$this->fields       = array();
		$this->groups       = array();
		$this->isExplain    = false;
		$this->joins        = array();
		$this->limitFrom    = null;
		$this->limitTo      = null;
		$this->orders       = array();
		$this->tables       = array();
		$this->values      = array();
		$this->whereClauses = array();
		return $this;
	}
	
	public function current() { return $this->db->current(); }
	
	/**
	 * Returns the delimited form of $text
	 * 
	 * All database drivers delimit text differently. This allows the driver to delimit the text right.
	 * 
	 * @return string
	 */
	public function delimit($text) {
		return $this->db->delimit($text);
	}
	
	public function escape($text) {
		return $text;
	}
	
	public function explain($isExplain=true) {
		// TODO: Add the detailed version
		// This query should (or should not) be prefixed with explain
		$this->isExplain = $isExplain;

		// For chaining
		return $this;
	}
	
	public function from($table) {
		// Add each table in func_get_args() to the tables 
		foreach(func_get_args() as $table) {
			// Add this table to the list of tables
			$this->tables[$table] = $table;
			// Add this table to the aliases for joins
			$this->aliases[] = $table;
		}
		
		// For chaining
		return $this; 
	}
	
	/**
	 * Returns the named item in this query with the specified index
	 * 
	 * @param  int $index The index from which to retrieve the alias. Negative offsets pull from the end of the array.
	 * 
	 * @return string     The requested alias
	 */
	public function getAlias($index) {
		// Use array_slice to get the item
		list($result) = array_slice($this->aliases, $index, 1);
		
		return $result;
	}
	
	public function getFromSQL() {
		if(!$this->tables) return "";
		return " FROM " . implode(", ", $this->tables);
	}

	public function getInsertSQL() {
		// Return empty string if no table specified.
		if(!$this->tables) return "";

		return "INSERT INTO " . implode(",", $this->tables) . " (" . implode(",",$this->fields) . " )" . 
			" VALUES (" . implode(",", $this->values) . ")";
	}
	
	public function getGroupBySQL() {
		// Initialize the result to an empty string
		$result = "";
		// Glue should start as GROUP BY
		$glue = 'GROUP BY';
		
		// Add each group
		foreach($this->groups as $group) {
			// Add this group
			$result .= " $glue $group";
			// Subsequent glue is ','
			$glue = ",";
		}
		
		return $result;
	}
	
	/**
	 * Returns the join at $index
	 * 
	 * @param  int          $index The index where we should find the join to return. Negative values are accepted to 
	 *                             return the nth-from-end item.
	 * @return ActiveRecord        $this for chaining
	 */
	public function getJoin($index) {
		// Get the requested join
		list($join) = array_slice($this->joins, $index, 1);
		
		return $join;
	}
	
	public function getJoinSQL() {
		// Call toString on each join and use ' ' as padding
		$result = " " . implode(" ", $this->joins);
		
		return $result;
	}
	
	public function getLimitSQL() {
		if($this->limitFrom) return " LIMIT $this->limitFrom, $this->limitTo";
		if($this->limitTo) return " LIMIT $this->limitTo";
	}
	
	public function getOrderBySQL() {
		$result = "";
		$glue = " ORDER BY";
		
		foreach($this->orders as $clause) {
			$result .= "$glue $clause";
			$glue = ",";
		}
		
		return $result;
	}
	
	public function getSelectSQL() {
		return 
			($this->isExplain ? "EXPLAIN " : "") // Explain
			. "SELECT "
			. implode(", ", $this->fields)
			. $this->getFromSQL()
			. $this->getJoinSQL()
			. $this->getWhereSQL()
			. $this->getGroupBySQL()
			. $this->getOrderBySQL()
			. $this->getLimitSQL()
			;
	}

	public function getUpdateSQL() {
		// Return empty string if the number of fields and values don't match
		if(sizeof($this->fields) != sizeof($this->values)) return "";
		// Create SQL string
		$sql =  
			"UPDATE "
			. implode(", ", $this->tables)
			. " SET";

		// update patient set history_id=9, person_id=9, account_id=9 where patient_id=6;
		$index = 0;
		foreach($this->fields as $field) {
			// Don't append a ',' if we're on the last field
			if($index == (sizeof($this->fields)-1)) $sql .= " $field=" . $this->values[$index];
			else $sql .= " $field=" . $this->values[$index] . ",";
			++$index;
		}

		// Add where SQL
		$sql .= $this->getWhereSQL();

		return $sql;
	}
	
	public function getWhereSQL() {
		$result = "";
		$useGlue = false;
		
		foreach($this->whereClauses as $clause) {
			// Add the glue
			$result .= 
				($useGlue ? " $clause[glue] " : " WHERE ")
				. implode(" ", array_filter(array($clause["key"], $clause["comparator"], $clause["value"])));
			
			$useGlue = true;
		}
		
		return $result;
	}
	
	public function groupBy($field) {
		// Break up into table and field
		// list($field, $table) = array_reverse(explode(".", $field));
		// Add this group 
		$this->groups[] = $field;
	}

	public function insert($fields) {
		// Update the core type
		$this->coreType = "INSERT";

		// Add each of the fields that will be inserted
		foreach(func_get_args() as $field) 
			// Add this field to the list of fields
			$this->fields[$field] = !preg_match('/\W/', $field) ? "`$field`" : $field;

		return $this;
	}

	public function into($table) {
		// Add this table to the list of tables
		$this->tables[$table] = $table;

		// For chaining
		return $this;
	}
	
	public function join($clause) {
		// Add this join
		$this->joins[] = $clause;
	}
	
	public function key() {
		return $this->db->key();
	}
	
	/**
	 * Creates an instance of ActiveRecordJoin with left orientation
	 * 
	 * This method will instantiate an ActiveRecordJoin and set the orientation to left. The join table will be 
	 * $joinTable, and it will be aliased to $alias if provided.
	 * 
	 * This is the same as the ANSI SQL syntax:
	 * <code language='sql'>
	 * 	LEFT JOIN $leftTable $alias
	 * </code>
	 * 
	 * Alternatively you may pass in an ActiveRecordJoin instance instead of the parameters and it will be added 
	 * directly to the internal join array.
	 * 
	 * In order to further operate on this join, you can either call getJoin(-1) or use the on() method to specify a 
	 * where type join condition. Failure to do either of these will result in a SQL syntax error.
	 * 
	 * Joins created with this method are considered figurative by default. To change the ActiveRecordJoin to be 
	 * literal, you must retrieve it with getJoin(-1) and disable the literal flag.
	 * 
	 * @param  string       $joinTable The name of the table to join
	 * @param  string       $alias     An alias for this particular join
	 * @return ActiveRecord            $this for chaining
	 */
	public function leftJoin($joinTable, $alias=false) {
		// If it's already a join, then don't do anything
		if($joinTable instanceof ActiveRecordJoin) $join = $joinTable;
		// Create the join
		else $join = Jackal::call("ActiveRecordJoin/left", $joinTable, $alias);
		// Set the direction
		$join->direction = "LEFT";
		// Store in our joins
		$this->joins[] = $join;
		
		return $this;
	}
	
	/**
	 * Calls leftJoin after delimiting its arguments
	 * 
	 * @return ActiveRecord $this for chaining
	 */
	public function leftJoinD() {
		// Delimit all the arguments
		$arguments = array_map(array($this, "delimit"), func_get_args());
		// Call the other function
		return call_user_func_array(array($this, "leftJoin"), $arguments);
	}
	
	public function lastAlias() {
		return @end($this->aliases);
	}
	
	public function limit() {
		// Process the function arguments
		@list($this->limitTo, $this->limitFrom) = array_reverse(func_get_args());
		
		return $this;
	}
	
	public function next() {
		return $this->db->next();
	}
	
	public function offsetExists($offset) { return $this->db->offsetExists($offset); }
	public function offsetGet($offset) { return $this->db->offsetGet($offset); }
	public function offsetSet($offset, $value) { return $this->db->offsetSet($offset, $value); }
	public function offsetUnset($offset) { return $this->db->offsetUnset($offset); }
	
	/**
	 * Set the where clause for the previous join
	 * 
	 * @param  string       $whereClause A where clause that should be passed to the join
	 * @return ActiveRecord              This instance for chaining
	 */
	public function on($whereClause) {
		// Get the last join we added
		$join = $this->getJoin(-1);
		// Make a where clause
		$where = new ActiveRecordWhere();
		// Get all the function arguments for the purpose of iteration
		$arguments = func_get_args();
		// Find the argument that is a valid operator
		$i = key(array_intersect($arguments, ActiveRecordWhere::$OPERATORS));
		
		switch($i) {
			case 1: $where->leftHand = $arguments[0]; break;
			case 2: $where->leftHand = "$arguments[0].$arguments[1]"; break;
		}
		
		switch(count($arguments)-$i) {
			case 2: $where->rightHand = $arguments[$i+1]; break;
			case 3: $where->rightHand = "{$arguments[$i+1]}.{$arguments[$i+2]}";
		}
		
		// Set the on clause of the join to the where clause we created
		$join->onClause = $where;
		
		// Return this for chaining
		return $this;
	}
	
	/**
	 * Calls on() after delimiting the arguments
	 * 
	 * @return ActiveRecord $this for chaining
	 */
	public function onD() {
		// Cache the arguments for easier access
		$arguments = func_get_args();
		// Delimit all the arguments that aren't operators
		foreach($arguments as $i=>$argument) 
			// Check the operator array to make sure this isn't an operator
			if(!isset(ActiveRecordWhere::$OPERATORS[$argument])) 
				// Execute the delimit
				$arguments[$i] = $this->delimit($argument);
		// Delegate the rest of the logic to on()
		return call_user_func_array(array($this, "on"), $arguments);
	}
	
	public function orderBy($field) {
		// Add this field to the list of order by clauses
		$this->orders[] = $field;
	}
	
	/**
	 * Create a new query
	 * 
	 * @return ActiveRecord The handle for the query
	 */
	public function query() { 
		// Apply all the defaults again
		foreach(self::$defaults as $name=>$value) $this->$name = $value;
		// And return this
		return $this;
	}
	
	public function rewind() { return $this->db->rewind(); }
	
	public function run($sql="") {
		if(!$sql) $sql = "$this";
		// Log the SQL
		self::$log[] = $sql;
		$this->db->query($sql);

		return (@$this->coreType === "SELECT") ? $this : $this->db->connection->insert_id;
	}
	
	public function select($field) {
		// Set the core query type
		$this->coreType = "SELECT";
		// Add each field to the list of fields
		foreach(func_get_args() as $field) $this->fields[$field] = $field;
		
		return $this;
	}
	
	public function selectD($field) {
		// Cache the arguments for delimition
		$arguments = func_get_args();
		// Delimit all the arguments
		foreach($arguments as $i=>$argument) 
			$arguments[$i] = implode(".", array_map(array($this, "delimit"), explode(".", $argument)));
		// Delegate to select
		return call_user_func_array(array($this, "select"), $arguments);
	}

	public function set($fields) {
		// Add each of the fields that will be inserted
		foreach(func_get_args() as $field) 
			// Add this field to the list of fields
			$this->fields[$field] = !preg_match('/\W/', $field) ? "`$field`" : $field;

		return $this;
	}
	
	public function smartLeftJoin($pattern) {
		// Kill all whitespace
		$pattern = preg_replace('/\s*/', '', $pattern);
		// Split on =
		@list($left, $right) = explode('=', $pattern);
		// Split left on .
		@list($left, $leftField) = explode('.', $left);
		// Split right on .
		@list($right, $rightField) = explode('.', $right);
		// Use rightField for left if left is missing
		if(!$leftField) $leftField = $rightField;
		// Use previous table for right if missing
		if(!$right) $right = $this->lastAlias();
		// Use leftField for rightField if right is missing
		if(!$rightField) $rightField = $leftField;
		
		// Don't add if already in aliases
		if(!in_array($left, $this->aliases)) {
			// Add the left table to the aliases
			$this->aliases[] = $left;
			// Quote everything
			$left       = $this->delimit($left);
			$leftField  = $this->delimit($leftField);
			$right      = $this->delimit($right);
			$rightField = $this->delimit($rightField);
			// Join!
			$this->joins["LEFT"][] = "$left $left ON $left.$leftField = $right.$rightField";
		}
		
		return $this;
	}
	
	public function __toString() {
		switch(@$this->coreType) {
			case "SELECT": return $this->getSelectSQL();
			case "INSERT": return $this->getInsertSQL();
			case "UPDATE": return $this->getUpdateSQL();
			default:
				return ""
				. $this->getFromSQL()
				. $this->getJoinSQL()
				. $this->getWhereSQL()
				;
		}
	}
	
	public function update($table) {
		// Update coretype
		$this->coreType = "UPDATE";
		// Add this table to the list of tables
		$this->tables[$table] = $table;

		// For chaining
		return $this;
	}

	/**
	 * Point the driver to a specific data source
	 * 
	 * Sets the driver to use for communicating with the database
	 * 
	 * Returns an instance of ActiveRecord for chaining
	 * 
	 * @param ActiveRecordDriver $datasource An ActiveRecordDriver for connecting to the database
	 * 
	 * @return ActiveRecord
	 */
	public function using($datasource) {
		// If $datasource isn't an object, then it's probably a URI
		if(!is_object($datasource)) {
			// Make a uri out of our arguments
			$URI = JackalModule::toURI(func_get_args());
			// Get the driver
			$driver = "ActiveRecord_$URI[driver]";
			// Tell Jackal to load the driver, telling the driver to connect
			$datasource = Jackal::call("$driver/init", $URI);
		}
		
		// Store the datasource
		$this->db = $datasource;
		
		return $this;
	}
	
	public function valid() { return $this->db->valid(); }

	public function values($values) {
		// Store all of the values
		foreach(func_get_args() as $value) $this->values[] = "'$value'";

		return $this;
	}
	
	public function where() {
		switch(func_num_args()) {
			case 0: break;
			
			case 1: 
				// Setup the where clause
				$this->whereClauses[] = array(
					"key" => func_get_args()
				); 
				break;
				
			case 2:
				// Get the arguments for this variant
				list($key, $value) = func_get_args();
				// Quote the value
				if(is_string($value)) $value = "'".$this->escape($value)."'";
				// Setup the where clause
				$this->whereClauses[] = array(
					"key"        => $key,
					"value"      => $value,
					"comparator" => "=",
					"glue"       => "AND",
				);
				break;
			
			case 3:
				// Get the arguments for this variant
				list($key, $comparator, $value) = func_get_args();
				// Quote the value
				if(is_string($value)) $value = "'".$this->escape($value)."'";
				$this->whereClauses[] = array(
					"key"        => $key,
					"value"      => $value,
					"comparator" => $comparator,
					"glue"       => "AND",
				);
				break;
				
			case 4:
				// Get the arguments for this variant
				list($key, $comparator, $value, $glue) = func_get_args();
				// Quote the value
				if(is_string($value)) $value = "'".$this->escape($value)."'";
				$this->whereClauses[] = array(
					"key"        => $key,
					"value"      => $value,
					"comparator" => $comparator,
					"glue"       => $glue,
				);
				break;
		}
		
		return $this;
	}
}
