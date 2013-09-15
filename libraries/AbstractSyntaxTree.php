<?php

class AbstractSyntaxTree {
	public function __construct($map = array()) {
		// Apply the map to this
		foreach($map as $name=>$value) $this->$name = $value;
	}
	
	public function &addChild($map=array()) {
		return $this->push(new AbstractSyntaxTree($map));
	}
	
	public function &push(&$child) {
		// SEt the parent of the child to this
		$child->parent =& $this;
		return $this->children[] =& $child;
	}
}
