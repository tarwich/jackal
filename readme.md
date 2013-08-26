Jackal
==================================================

Jackal is a lightweight PHP framework that attmpts to load on demand only the 
things necessary to complete the task at hand. Currently still under major 
development due to issues that prevent easy adoption.

Philosophy
==================================================

Lightweight
--------------------------------------------------
Many very good frameworks for PHP load everything necessary to do anything you
want to do whether or not you want to do those things. This framework requires 
you to invoke other classes via Jackal, allowing Jackal to load required 
classes on-demand. This makes 90% of your pages load only themselves, since 
that's all they need anyway.

You call other modules like this:

````php
Jackal::call("OtherClass/method");
````

Fast
--------------------------------------------------
Jackal doesn't attmept to wrap things that PHP already does. 

**Just as one example:** Many frameworks, enumerate files in a directory or 
directories to search for the desired file. However, glob() is an internal 
function already supported by PHP which will return optimal results faster. 

Existing frameworks might
````php
$results = array();

foreach(scandir("/foo") as $file) 
  if(substr($file, 0, 5) == "class") 
  	if(substr($file, -4) == ".") 
			$results[] = $file;
````

Jackal does this:
````php
$results = glob("/foo/class*.php");
````

TODO:
--------------------------------------------------
Add information about how to add Jackal as a submodule to other git repositories.

<!-- vim: set ft=Markdown : -->
