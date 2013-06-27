<?php

function form_field($field, $type="text", $default=NULL, $extra="") {
	if($extra) $extra = " $extra";
	
	if(false);
	
	elseif($type=="textarea") {
		$value = @$_REQUEST[$field];
		return "<textarea name='$field'$extra>$value</textarea>";
	}
	
	else {
		$value =@ $_REQUEST[$field];
		return "<input type='$type' name='$field' value='$value'$extra />";
	}
}

?>