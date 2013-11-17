<?php

Jackal::loadLibrary("ActiveRecordDriver");

/**
 * ActiveRecordDriver implementation for MySQLI
 */
class ActiveRecord_MySQLI extends ActiveRecordDriver {
	public $current = null;
	public $results = null;
	
	public function __construct($connection=null) {
		// Connect to the database
		call_user_func_array(array($this, "init"), func_get_args());
		// Pass along to the parent constructor
		parent::__construct($this->connection);
	}
	
	public function current() {
		return $this->current;
	}
	
	public function delimit($text) {
		return '`'.str_replace('`', '\`', $text).'`';
	}
	
	public function init($connection=null) {
		// Passthrough
		if($connection instanceof mysqli) return parent::__construct($connection);
		// Don't try to make null connections
		if($connection == null) return parent::__construct($connection);
		// Make a new mysqli connection
		$this->connection = new mysqli();
		// At this point we're going to treat it as a URI
		$URI = JackalModule::toURI(func_get_args());
		// Connect the connection to the database
		call_user_func_array(array($this->connection, "connect"), array(
			@$URI["host"],
			@$URI["username"] ?: @$URI["user"],
			@$URI["password"] ?: @$URI["pass"],
			@$URI["database"],
			@$URI["port"],
			@$URI["socket"],
		));
		
		return $this;
	}
	
	public function key() {
		// Try to return the key from the row
		return @reset($this->current);
	}
	
	public function next() {
		// Advance the cursor and return the new current row
		return $this->current = $this->results->fetch_array(MYSQLI_ASSOC);
	}
	
	public function offsetExists($offset) {
		// True if there are more rows than the offset (since the offset is zero based)
		return $offset < $this->results->num_rows;
	}
	
	public function offsetGet($offset) {
		// The function is MySQL is identical to PHP
		$this->results->data_seek($offset);	
		// Now that we're at the Nth offset, return the data
		return $this->next();
	}
	
	public function offsetSet($offset, $value) {
		// Not supported
		trigger_error("This functionality is not supported on a MySQLi_Result");
	}
	
	public function offsetUnset($offset) {
		// Not supported
		trigger_error("This functionality is not supported on a MySQLi_Result");
	}
	
	public function query($sql) {
		// Free the last result set if results is an object of type mysqli_result
		if($this->results instanceof mysqli_result) $this->results->free();
		// Get a new result set
		$this->results = $this->connection->query($sql);
		
		if($this->connection->errno) {
			Jackal::error(500, $this->connection->error);
			Jackal::error(500, "Query failed: $sql");
		}
		
		return $this;
	}
	
	public function rewind() {
		// Get the current row
		$this->next();
		// Go to the first result
		$this->results->data_seek(-1);
	}
	
	public function valid() {
		// No results means false
		if(!$this->results) return false;
		// Not having a current record means false
		if(!$this->current) return false;
		
		// Pretty much anything else means true
		return true;
	}
}
