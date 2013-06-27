<?php

/**
 * Disables template calls at the end of the script
 * 
 * This disables the template module for the current script. This is useful for
 * things like image generation, redirection, or things like security that 
 * prohibits access to resources.
 * 
 * @return void
 */
$this->disabled = true;
