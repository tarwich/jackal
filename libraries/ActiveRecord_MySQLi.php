<?php

Jackal::loadLibrary("ActiveRecordDriver");

/**
 * ActiveRecordDriver implementation for MySQLI
 */
class ActiveRecord_MySQLI extends ActiveRecordDriver {
	public $current = null;
	public $results = null;
	public $key = 0;
	
	public function __construct($connection=null) {
		// Connect to the database
		call_user_func_array(array($this, "init"), func_get_args());
		// Pass along to the parent constructor
		parent::__construct($this->connection);
	}
	
	public function count() {
		return @$this->results->num_rows ?: 0;
	}
	
	public function current() {
		return $this->current;
	}
	
	public function delimit($text) {
		return preg_replace('/(\w+)/', '`$1`', $text);
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
		return $this->key;
	}
	
	public function next() {
		// Move to the next key
		++$this->key;
		// Advance the cursor and return the new current row
		return $this->current = $this->results->fetch_array(MYSQLI_ASSOC);
	}
	
	public function offsetExists($offset) {
		// True if there are more rows than the offset (since the offset is zero based)
		return $offset < $this->results->num_rows;
	}
	
	public function offsetGet($offset) {
		// Protection against null result sets
		if(!$this->results) return array();
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
		// Reset the key
		$this->key = -1;
		
		if($this->connection->errno) {
			Jackal::error(500, $this->connection->error);
			Jackal::error(500, "Query failed: $sql");
		}
		
		return $this;
	}
	
	public function quote($text) {
		// Escape any quotes
		$text = str_replace('"', '\"', $text);
		
		// Quote and return
		return "\"$text\"";
	}
	
	public function rewind() {
		// Go to the first result
		$this->results->data_seek(-1);
		// Get the current row
		$this->next();
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
