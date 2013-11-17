<?php

Jackal::loadLibrary("ActiveRecordWhere");

class ActiveRecordJoin {
	/**
	 * The ANSI SQL alias for this join
	 * 
	 * Leave blank for no alias
	 * 
	 * @var string
	 */
	public $alias = "";
	
	/**
	 * The direction of the join
	 * 
	 * May be LEFT, INNER, OUTER, NATURAL
	 * 
	 * @var string
	 */
	public $direction = "INNER";
	
	/**
	 * The table to which to join
	 * 
	 * @var string
	 */
	public $joinTable = "";
	
	/**
	 * Set this to true in order to leave fields unescaped
	 * 
	 * In its default state (false), this field will cause ActiveRecordJoin to delimit fields when generating SQL. If 
	 * set to true, then the fields will be treated literally and not delimited. This is useful if you want to include 
	 * an expression in place of an actual field.
	 * 
	 * @var boolean
	 */
	public $literal = false;
	
	/**
	 * The previous table or alias for the purpose of populating right hand values during the on() call
	 * 
	 * @var string
	 */
	public $previousTable = "";
	
	/**
	 * An ANSI SQL join clause
	 * 
	 * @var ActiveRecordWhere
	 */
	public $onClause = "";
	
	/**
	 * Create a new LEFT JOIN
	 * 
	 * This method will create a new LEFT JOIN to $joinTable. 
	 * 
	 * @param string $joinTable     The table to which to join
	 * @param string $alias         (optional) The alias for this join clause. If present, it will be included in the 
	 *                              generated SQL. If omitted, then no alias will be present in the generated SQL.
	 * @param string $previousTable Stores the previous table or alias for the purpose of populating right hand values
	 *                              during the on() call.
	 * 
	 * @return ActiveRecordJoin
	 */
	public static function left($joinTable, $alias="", $previousTable=null) {
		// Make a new instance
		$self = new ActiveRecordJoin();
		// Store the joinTable for SQL generation
		$self->joinTable = $joinTable;
		// If no alias provided, then ensure $alias is an empty string 
		$self->alias = $alias ?: "";
		// Store the previous table for on() calls
		$self->previousTable = $previousTable;
		
		return $self;
	}
	
	/**
	 * Set the join clause
	 * 
	 * The join clause is an ActiveRecordWhere clause, and as such, either parameters for creating a new 
	 * ActiveRecordWhere instance, or an existing instance may be passed.
	 * 
	 * @return ActiveRecordJoin $this object for chaining
	 */
	public function on($clause) {
		// If the clause is already an ActiveRecordWhere clause, then simply store it
		if($clause instanceof ActiveRecordWhere) $this->onClause = $clause;
		// Make an ActiveRecordWhere instance and store it
		else $this->onClause = Jackal::call("ActiveRecordWhere", Jackal::$EXPAND, func_get_args());
		
		return $this;
	}
	
	/**
	 * Generate the ANSI SQL version of this join
	 * 
	 * @return string The ANSI SQL string that this join represents
	 */
	public function __toString() {
		$result = "{$this->direction} JOIN {$this->joinTable} {$this->alias} ON {$this->onClause}";
		
		return $result;
	}
}
