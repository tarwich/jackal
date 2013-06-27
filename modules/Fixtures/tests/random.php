<?php

//  __________________________________________________
// / Get first names                                  \

$this->startSubTest(array("Get first names"));
$results = Jackal::model("Fixtures/random/:firstName", array(":LIMIT" => 5));

foreach($results as $result) {
	$this->assertTrue(array(is_string($result[0])));
}

// \__________________________________________________/

//  __________________________________________________
// / Range                                            \

$this->startSubTest(array("Range"));
$results = Jackal::model("Fixtures/random/(1..9)", array(":LIMIT" => 5));

foreach($results as $result) {
	$this->assertTrue(array($result[0] >= 1));
	$this->assertTrue(array($result[0] <= 9));
}

// \__________________________________________________/

//  __________________________________________________
// / Limit to 5                                       \

$this->startSubTest(array("Limit to 5"));
$results = Jackal::model("Fixtures/random/:firstName", array(":LIMIT" => 5));
$this->assertEquals(array(count($results), 5));

// \__________________________________________________/

