<?php

Jackal::loadLibrary("Component");

class __TestClass__Component_safeArray extends Component {
	public function callSafeArray() {
		return call_user_func_array(array($this, "safeArray"), func_get_args());
	}
}

$tester = new __TestClass__Component_safeArray();

//  __________________________________________________
// / Scalar array                                     \

$this->startSubTest(array("Pass scalar array into safeArray"));
$result = $tester->callSafeArray(array("a", "b", "c"));
$this->assertEquals(array($result, array("a", "b", "c")));

// \__________________________________________________/


//  __________________________________________________
// / Markup                                           \

$this->startSubTest(array("Pass markup into safeArray"));
$markup = "
	<li>a</li>
	<li>b</li>
	<li>c</li>
";
$result = $tester->callSafeArray($markup);
$this->assertEquals(array($result, array("a", "b", "c")));

// \__________________________________________________/


//  __________________________________________________
// / Markup + Depth                                   \

$this->startSubTest(array("Markup with depth"));
$markup = "
	<ul>
		<li foo='bar'>x</li>
		<li foo='bin'>y</li>
		<li foo='baz'>z</li>
	</ul>
	<ul>
		<li foo='bar'>1</li>
		<li foo='bin'>2</li>
		<li foo='baz'>3</li>
	</ul>
";
$result = $tester->callSafeArray($markup, 2);
$this->assertEquals(array($result, array(
	array("x", "y", "z"),
	array(1, 2, 3),
)));

// \__________________________________________________/

