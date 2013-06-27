<?php

/**
 * Returns the current page title as a string
 * 
 * It's useful to use setTitle() and getTitle() because this allows templates
 * to show different titles depending on the current page
 * 
 * @return string Current page title
 * 
 */
@( ($title = $this->title) || ($title = Jackal::setting("application/title")) );
return $title;
