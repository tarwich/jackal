<?php

// Get the ActiveRecord class
$db = Jackal::loadLibrary("ActiveRecord");

// ==================================================
// 
// 
// 
// Basic SQL Generation 
// 
// 
// 
// ==================================================

$this->startSubTest(array("Basic SQL Generation"));

// SELECT a
$this->assertEquals(array(
	"SELECT `a`", 
	(string) $db->clear()->select("a")
));

// SELECT a, b
$this->assertEquals(array(
	"SELECT `a`, `b`", 
	(string) $db->clear()->select("a", "b")
));

// SELECT a, b FROM c
$this->assertEquals(array(
	"SELECT `a`, `b` FROM c",
	(string) $db->clear()->select("a", "b")->from("c")
));

// SELECT a, b FROM c, d
$this->assertEquals(array(
	"SELECT `a`, `b` FROM c, d", 
	(string) $db->clear()->select("a", "b")->from("c", "d")
));

// SELECT a FROM b WHERE c = 'd'
$this->assertEquals(array(
	"SELECT `a` FROM b WHERE `c` = 'd'", 
	(string)  $db->clear()->select("a")->from("b")->where("c", "d")
));

// SELECT a FROM b WHERE c > 'd'
$this->assertEquals(array(
	"SELECT `a` FROM b WHERE `c` > 'd'", 
	(string)  $db->clear()->select("a")->from("b")->where("c", ">", "d")
));

// SELECT a FROM b WHERE c = 12
$this->assertEquals(array(
	"SELECT `a` FROM b WHERE `c` > 12", 
	(string)  $db->clear()->select("a")->from("b")->where("c", ">", 12)
));

// SELECT a FROM b WHERE c > 12
$this->assertEquals(array(
	"SELECT `a` FROM b WHERE `c` > 12", 
	(string)  $db->clear()->select("a")->from("b")->where("c", ">", 12)
));

// ==================================================
// 
// 
// 
// SQL Generation via Combinatorics
// 
// 
// 
// ==================================================

// Get the ActiveRecord class
$db = Jackal::loadLibrary("ActiveRecord");

$input = array();
$input[(1 << count($input))] = array("select", array("field_1"));
$input[(1 << count($input))] = array("select", array("field_2"));
$input[(1 << count($input))] = array("from"  , array("table_1"));
$input[(1 << count($input))] = array("from"  , array("table_2"));
$input[(1 << count($input))] = array("limit" , array(1        ));
$input[(1 << count($input))] = array("limit" , array(1, 2     ));
$input[(1 << count($input))] = array("where" , array("foo", "bar"));

$expectedResults = array(
	"SELECT `field_1`",
	"SELECT `field_1`, `field_2`",
	"SELECT `field_1` FROM table_1",
	"SELECT `field_1`, `field_2` FROM table_1",
	"SELECT `field_1` FROM table_2",
	"SELECT `field_1`, `field_2` FROM table_2",
	"SELECT `field_1` FROM table_1, table_2",
	"SELECT `field_1`, `field_2` FROM table_1, table_2",
	"SELECT `field_1` LIMIT 1",
	"SELECT `field_1`, `field_2` LIMIT 1",
	"SELECT `field_1` FROM table_1 LIMIT 1",
	"SELECT `field_1`, `field_2` FROM table_1 LIMIT 1",
	"SELECT `field_1` FROM table_2 LIMIT 1",
	"SELECT `field_1`, `field_2` FROM table_2 LIMIT 1",
	"SELECT `field_1` FROM table_1, table_2 LIMIT 1",
	"SELECT `field_1`, `field_2` FROM table_1, table_2 LIMIT 1",
	"SELECT `field_1` LIMIT 1, 2",
	"SELECT `field_1`, `field_2` LIMIT 1, 2",
	"SELECT `field_1` FROM table_1 LIMIT 1, 2",
	"SELECT `field_1`, `field_2` FROM table_1 LIMIT 1, 2",
	"SELECT `field_1` FROM table_2 LIMIT 1, 2",
	"SELECT `field_1`, `field_2` FROM table_2 LIMIT 1, 2",
	"SELECT `field_1` FROM table_1, table_2 LIMIT 1, 2",
	"SELECT `field_1`, `field_2` FROM table_1, table_2 LIMIT 1, 2",
	"SELECT `field_1` LIMIT 1, 2",
	"SELECT `field_1`, `field_2` LIMIT 1, 2",
	"SELECT `field_1` FROM table_1 LIMIT 1, 2",
	"SELECT `field_1`, `field_2` FROM table_1 LIMIT 1, 2",
	"SELECT `field_1` FROM table_2 LIMIT 1, 2",
	"SELECT `field_1`, `field_2` FROM table_2 LIMIT 1, 2",
	"SELECT `field_1` FROM table_1, table_2 LIMIT 1, 2",
	"SELECT `field_1`, `field_2` FROM table_1, table_2 LIMIT 1, 2",
	"SELECT `field_1` WHERE `foo` = 'bar'",
	"SELECT `field_1`, `field_2` WHERE `foo` = 'bar'",
	"SELECT `field_1` FROM table_1 WHERE `foo` = 'bar'",
	"SELECT `field_1`, `field_2` FROM table_1 WHERE `foo` = 'bar'",
	"SELECT `field_1` FROM table_2 WHERE `foo` = 'bar'",
	"SELECT `field_1`, `field_2` FROM table_2 WHERE `foo` = 'bar'",
	"SELECT `field_1` FROM table_1, table_2 WHERE `foo` = 'bar'",
	"SELECT `field_1`, `field_2` FROM table_1, table_2 WHERE `foo` = 'bar'",
	"SELECT `field_1` WHERE `foo` = 'bar' LIMIT 1",
	"SELECT `field_1`, `field_2` WHERE `foo` = 'bar' LIMIT 1",
	"SELECT `field_1` FROM table_1 WHERE `foo` = 'bar' LIMIT 1",
	"SELECT `field_1`, `field_2` FROM table_1 WHERE `foo` = 'bar' LIMIT 1",
	"SELECT `field_1` FROM table_2 WHERE `foo` = 'bar' LIMIT 1",
	"SELECT `field_1`, `field_2` FROM table_2 WHERE `foo` = 'bar' LIMIT 1",
	"SELECT `field_1` FROM table_1, table_2 WHERE `foo` = 'bar' LIMIT 1",
	"SELECT `field_1`, `field_2` FROM table_1, table_2 WHERE `foo` = 'bar' LIMIT 1",
	"SELECT `field_1` WHERE `foo` = 'bar' LIMIT 1, 2",
	"SELECT `field_1`, `field_2` WHERE `foo` = 'bar' LIMIT 1, 2",
	"SELECT `field_1` FROM table_1 WHERE `foo` = 'bar' LIMIT 1, 2",
	"SELECT `field_1`, `field_2` FROM table_1 WHERE `foo` = 'bar' LIMIT 1, 2",
	"SELECT `field_1` FROM table_2 WHERE `foo` = 'bar' LIMIT 1, 2",
	"SELECT `field_1`, `field_2` FROM table_2 WHERE `foo` = 'bar' LIMIT 1, 2",
	"SELECT `field_1` FROM table_1, table_2 WHERE `foo` = 'bar' LIMIT 1, 2",
	"SELECT `field_1`, `field_2` FROM table_1, table_2 WHERE `foo` = 'bar' LIMIT 1, 2",
	"SELECT `field_1` WHERE `foo` = 'bar' LIMIT 1, 2",
	"SELECT `field_1`, `field_2` WHERE `foo` = 'bar' LIMIT 1, 2",
	"SELECT `field_1` FROM table_1 WHERE `foo` = 'bar' LIMIT 1, 2",
	"SELECT `field_1`, `field_2` FROM table_1 WHERE `foo` = 'bar' LIMIT 1, 2",
	"SELECT `field_1` FROM table_2 WHERE `foo` = 'bar' LIMIT 1, 2",
	"SELECT `field_1`, `field_2` FROM table_2 WHERE `foo` = 'bar' LIMIT 1, 2",
	"SELECT `field_1` FROM table_1, table_2 WHERE `foo` = 'bar' LIMIT 1, 2",
	"SELECT `field_1`, `field_2` FROM table_1, table_2 WHERE `foo` = 'bar' LIMIT 1, 2",
);

// Required indices
$required = 1;

// Begin the test section
$this->startSubtest(array("SQL Combinatorics"));

// Perform the combinatorics permutations
for($i=1, $iMax = pow(2, count($input))-1; $i<=$iMax; ++$i) {
	// Skip invalid items
	if(($i & $required) != $required) continue;
	// Remove all the query information from the ActiveRecord instance
	$db->clear();
	
	// Go through the tests and add 
	foreach($input as $key=>$test) {
		list($method, $arguments) = $test;
		
		// Check to see if this is a test we're running
		if(($i & $key) == $key) {
			// Call the requested function on the database
			call_user_func_array(array($db, $method), $arguments);
		}
	}
	
	// Test the values
	$this->assertEquals(array(@array_shift($expectedResults), "$db"));
}

