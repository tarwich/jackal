<?php

class AbstractSyntaxTree {
	public $type     = "";
	public $name     = "";
	public $children = array();
	
	public function __construct($map = array()) {
		// Apply the map to this
		foreach($map as $name=>$value) $this->$name = $value;
	}
	
	public function &addChild($map=array()) {
		return $this->push(new AbstractSyntaxTree($map));
	}
	
	public function &children() {
		return $this->children;
	}
	
	public function &push(&$child) {
		// Set the parent of the child to this
		$child->parent =& $this;
		return $this->children[] =& $child;
	}
}
