<?php

/**
 * A where clause wrapper
 */

class ActiveRecordWhere {
	/**
	 * The ActiveRecordDriver to use when delimiting fields
	 * 
	 * @var ActiveRecordDriver
	 */
	public $driver = null;
	
	/**
	 * The left hand side (lhs) of the expression
	 * 
	 * @var mixed
	 */
	public $leftHand;
	
	/**
	 * The operator used in the where clause
	 * 
	 * @var string
	 */
	public $operator = "=";
	
	/**
	 * List of operators supported by this class
	 * 
	 * @var array
	 */
	public static $OPERATORS = array(
		"<"        => "<",
		"<="       => "<=",
		"="        => "=",
		">"        => ">",
		">="       => ">=",
		"IN"       => "IN",
		"IS NOT"   => "IS NOT",
		"IS"       => "IS",
		"LIKE"     => "LIKE",
		"NOT IN"   => "NOT IN",
		"NOT LIKE" => "NOT LIKE",
	 );
	
	/**
	 * The right hand side (rhs) of the expression
	 * 
	 * @var mixed
	 */
	public $rightHand;
	
	/**
	 * Compile the where clause into an ANSI SQL string
	 * 
	 * @return string The ANSI SQL version of the where clause
	 */
	public function __toString() {
		return "{$this->leftHand} {$this->operator} {$this->rightHand}";
	}
}
