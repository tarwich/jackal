<?php

/**
 * Sets the page title for use in template
 * 
 * This method sets the page title for use in templates. This is useful to 
 * allow modules to customize the title of the page.
 * 
 * The title may also be set in the configuration under application/title. This
 * is preferred, because it allows pages to set their own title, but for the 
 * application to have a site-wide default title.
 * 
 * Config setting:
 * <code type="yaml">
 * application:
 * 		title: Example Title
 * </code>
 * 
 * Segments: title
 * 
 * @param string $title The new title of the page
 * 
 * @return void
 */
if(is_string($URI)) $this->title = $URI;
else @($this->title = $URI["title"]) || @($this->title = $URI[0]);

return $this->title;
