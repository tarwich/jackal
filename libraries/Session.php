<?php

/**
 * Class to manage setting up, reading from, and writing to session
 * 
 * The purpose of this class is to simplify and consolidate session access in order to resolve common issues with 
 * cross-platform compatibility.
 */
class Session {
	/**
	 * Return a value from the session
	 * 
	 * @example Get a variable from the session
	 * <code language='php'>
	 * // Returns $_SESSION["foo"]["bar"]
	 * $value = Jackal::call("Session/read/foo/bar");
	 * </code>
	 * 
	 * @Segments: name
	 * 
	 * @param string $name Slashed path to the variable you want to retrieve.
	 * 
	 * @return mixed 
	 */
	public function read($URI) {
		$this->start();
		$node = $_SESSION;
		
		foreach($URI as $name) {
			$node = $node[$name];
		}
		
		return $node;
	}
	
	/**
	 * Starts the session if it isn't already started
	 * 
	 * This method will check to see if the session is started, and start it if not.
	 * 
	 * @return void
	 */
	public function start() {
		// See if the session is started
		if($this->started()) return;
		// Start the session
		session_start();
	}
	
	/**
	 * Returns true if the session is started
	 * 
	 * @return boolean
	 */
	public function started() {
		// Started if there is a session id
		return !!session_id();
	}
	
	/**
	 * Write a variable to the session
	 * 
	 * This method will traverse $_SESSION until it finds the specified key, at which point it will set the value.
	 * 
	 * @example Setting a value in the session
	 * <code type='php'>
	 * // Store 1234 in $_SESSION["foo"]["bar"]
	 * Jackal::call("Session/store/foo/bar", 1234);
	 * // Also works all inline
	 * Jackal::call("Session/store/foo/bar/1234");
	 * </code>
	 * 
	 * @param string $path  A slashed path to the value to set
	 * @param mixed  $value The value to store in the variable
	 * 
	 * @return void
	 */
	public function store($URI) {
		// If session not started, then start it
		$this->start();
		
		// The value is the last item in the URI
		$value = array_pop($URI);
		// The path is what's left of the URI
		$path = $URI;
		// Start at the root of session
		$node =& $_SESSION;
		
		// Walk the path to find the setting we're going to update
		foreach($path as $name) {
			// Ensure this session item exists
			if(!@$_SESSION[$name]) $_SESSION[$name] = array();
			// Descend into this item
			$node =& $node[$name];
		}
		
		// Store this item in the session
		$node = $value;
	}
}
