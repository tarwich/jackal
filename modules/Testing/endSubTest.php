<?php

if(!@$this->inSubTest) return;

$this->inSubTest = false;
$name = @$this->subTestName;

$buffer = ob_get_clean();

//
// PASS
//
if($this->subTestStatus == "pass") {
	echo "
		<div class='subtest subtest-pass'>
			<h2>
				<i>$this->testModule</i>.$this->testName <b>($this->subTestName)</b>
				<a class='status'>pass</a>
			</h2>
			<div class='results'>
				$buffer
			</div>
		</div>
	";
}


//
// FAIL
//
else {
	echo "
		<div class='subtest subtest-fail'>
			<h2>
				<i>$this->testModule</i>.$this->testName <b>($this->subTestName)</b>
				<a class='status'>fail</a>
			</h2>
			<div class='results'>
				$buffer
			</div>
		</div>
	";
}

