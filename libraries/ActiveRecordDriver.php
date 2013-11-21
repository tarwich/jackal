<?php

/**
 * Base class for making an active record database driver
 * 
 * Extend this class to make ActiveRecord connect to a particular database
 * 
 * Note: This class should not be used to connect to the database. It is a base class (Interface) only.
 */
abstract class ActiveRecordDriver implements ArrayAccess, Countable, Iterator {
	/**
	 * Creates a new instance of the ActiveRecord driver
	 * 
	 * @param resource $connection The connection to the database
	 * 
	 * @return ActiveRecordDriver
	 */
	public function __construct($connection=null) {
		$this->connection = $connection;
	}
	
	/**
	 * Returns the number of records in the result set
	 * 
	 * @return int The number of records in the result set
	 */
	abstract public function count();
	
	/**
	 * Return the current row
	 * 
	 * This method returns the current row as an array, where the field names are the keys and the values are the values.
	 * 
	 * @return array
	 */
	abstract public function current();
	
	/**
	 * Returns the delimited form of $text
	 * 
	 * All database drivers delimit their text differently. Implement this method in order to provide delimiting
	 * specific to your driver.
	 * 
	 * @return string
	 */
	abstract public function delimit($text);
	
	/**
	 * Initialize the driver with new connection settings
	 * 
	 * This method returns the current instance for chaining
	 * 
	 * @return ActiveRecordDriver
	 */
	abstract public function init();
	
	/**
	 * Get the current key from the underlying database
	 * 
	 * @return scalar
	 */
	abstract public function key    ();
	
	/**
	 * Move the record pointer to the next record and return the current result 
	 * 
	 * This method returns the current result and moves the pointer forward, but not necessarily in that order. 
	 * 
	 * The result returned by this method should be an array of associative arrays where the keys are the field names 
	 * and the values are the values.
	 * 
	 * @return array
	 */
	abstract public function next   ();
	
	/**
	 * Returns true if the database holds an item at a particular offset
	 * 
	 * @param  int     $offset The offset to check
	 * @return boolean         True if the offset exists
	 */
	abstract public function offsetExists($offset);
	
	abstract public function offsetGet($offset);
	abstract public function offsetSet($offset, $value);
	abstract public function offsetUnset($offset);
	
	/**
	 * Run a query against the underlying database
	 * 
	 * @param string $sql The query to run
	 * 
	 * @return void
	 */
	abstract public function query($sql);
	
	/**
	 * Returns a quoted version of $text as quoted by the underlying engine
	 * 
	 * @param  string $text The text to quote
	 * @return string       The quoted text
	 */
	abstract public function quote($text);
	
	/**
	 * Tell the underlying database to go back to the beginning of the result set
	 * 
	 * @return void
	 */
	abstract public function rewind ();
	
	/**
	 * Returns true if the current position is valid
	 * 
	 * @return boolean
	 */
	abstract public function valid  ();
}
