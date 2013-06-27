<?php

/**
 * Change the template message
 * 
 * This method changes the template message that will be output when the 
 * script terminates.
 * 
 * Segments: module / action
 * 
 * @return void
 */

@($template = "$URI[0]/$URI[1]");
Jackal::putSettings("template/template-message", $template);

