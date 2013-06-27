<?php

function hasRight($right) {
	// Makes hasRight easier to call
	if(!$right) return true;
	// Get the current user
	$user = Jackal::model("Users/getCurrentUser");
	
	// If they have the god bit, then they have the right
	if(@$user["god"]) return true;
	
	if(is_string($right)) {
		$right = strtolower($right);

		// Load the roles for this user
		$groups = (array) Jackal::model("Users/find/role,role_assignment", array(
			"user" => (integer) @$user["user_id"],
		));

		// FIXME: There should be a better way to go through this array
		foreach($groups as $group) {
			if(strtolower($group["name"]) == $right) return true;
		}

		//// Load this right
		//$right = Jackal::model("Users/findOrBlank/kind", array("code" => $right));
		//// If the right doesn't exist, then the user doesn't have it
		//if(!$right['kind_id']) return false;
		//// See if the user has the right 
		//if($user["weight"] >= $right["weight"]) return true;
	} else {
		Jackal::error(501, "Not implemented");
	}

	return false;
}
