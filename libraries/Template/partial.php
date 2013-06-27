<?php

/**
 * Template used to output the head block but not wrap page in a template
 * 
 * @return void
 */

$content = ob_get_contents(); ob_clean();

$this->head();

echo $content;
