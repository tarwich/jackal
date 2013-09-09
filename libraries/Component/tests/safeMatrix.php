<?php

//  __________________________________________________
// / Setup class to call private members              \

Jackal::loadLibrary("Component");

class __TestClass__Component_safeMatrix extends Component {
	public function callSafeMatrix() {
		return call_user_func_array(array($this, "safeMatrix"), func_get_args());
	}
}

$tester = new __TestClass__Component_safeMatrix();

// \__________________________________________________/


//  __________________________________________________
// / Test with 1 level of markup                      \

$this->startSubTest(array("Test with 1 level of markup"));
$result = $tester->callSafeMatrix("
	<li foo='bar'>a</li>
	<li>b</li>
	<li>c</li>
");
// Depth is not a documented feature
unset($result["depth"]);
$shouldBe = array(
	"tagName" => "root",
	"li" => array(
		array("foo" => "bar", "contents" => "a"),
		array("contents" => "b"),
		array("contents" => "c"),
	)
);
$this->assertEquals(array($result, $shouldBe));

// \__________________________________________________/


//  __________________________________________________
// / Test with 2 levels of markup                     \

$this->startSubTest(array("Test with 2 levels of markup"));
$result = $tester->callSafeMatrix("
	<ul foo='bar'>
		<li bin='baz'>a</li>
		<li>b</li>
		<li>c</li>
	</ul>
");
// Depth is not a documented feature
unset($result["depth"]);
$shouldBe = array(
	"tagName" => "root",
	"ul" => array(
		array(
			"foo" => "bar",
			"contents" => @$result["ul"][0]["contents"],
			"li" => array(
				array("contents" => "a", "bin"=>"baz"),
				array("contents" => "b"),
				array("contents" => "c"),
			)
		)
	)
);
$this->assertEquals(array($result, $shouldBe));

// \__________________________________________________/


//  __________________________________________________
// / Test with an array                               \

$this->startSubTest(array("Test with array"));
$result = $tester->callSafeMatrix(array(
	array("foo" => "a"),
	array("foo" => "b"),
));
// Depth is not a documented feature
$shouldBe = array(
	array("foo" => "a"),
	array("foo" => "b"),
);
$this->assertEquals(array($result, $shouldBe));

// \__________________________________________________/


