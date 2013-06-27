<?php

//  __________________________________________________
// / Parse URI                                        \

@( ($name = $URI[0]) || ($name = $URI["name"]) );

// \__________________________________________________/


if(@$this->inSubTest) $this->endSubTest();

$this->inSubTest = true;
$this->subTestStatus = "pass";
$this->subTestName = $name;

ob_start();

