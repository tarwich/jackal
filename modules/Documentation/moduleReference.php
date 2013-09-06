<?php 

/**
 * Displays a class-reference style page with the module documentation. 
 * 
 * Shows all the properties and methods of a module. If the module is a folder
 * based module, then it is built with Documentation::getModuleDataFromFolder 
 * prior to the documentation generation.
 * 
 * The builder will scan each method page and put use the first 
 * <a href='http://en.wikipedia.org/wiki/PHPDoc'>PHPDoc</a> style comment it 
 * finds as the DocBlock comment for the method.
 * 
 * @param string $URI[0] The module to generate documentation for  
 * 
 * @return array
 */

//  __________________________________________________
// / Parse URI                                        \

@($moduleName = $URI["module"]) || @($moduleName = $URI[0]);
@($class = $URI["class"]) || @($class = $URI["segments"][1]) || ($class = false);

// \__________________________________________________/


//  __________________________________________________
// / Load helpers and libraries                       \

Jackal::loadHelper("url");
Jackal::loadHelper("array"); // For rekey

// \__________________________________________________/


// Load formatter stuff
// Load formatter stuff
js("Documentation/resources/formatters.js");
css("Documentation/resources/formatters.css");

//  __________________________________________________
// / Get module code                                  \

// Get the module path on the filesystem
$files = Jackal::files(Jackal::setting("class-path"), array(
	"MODULE" => $moduleName
));

// I am not sure if there would ever be more than one result, but this will help me find out
//assert('count($files)==1; // There should only be one result');

// Get the first item (there should only be one)
$path = $files[0];

// LPK ~ Only try to get the class if it does not already exist
if (!$class) {
	if(is_dir($path)) $class = $this->getModuleDataFromFolder(array($path));
	else $class = $this->getModuleDataFromFile(array($path));
}

// \__________________________________________________/

// Class documentation
$doc = $this->parseDocComment($class->getDocComment());
// The narrative is the summary and description. This combines them into one 
// array and removes empties
$narrative = array_filter(array_merge((array) $doc["summary"], $doc["description"])); 
// addLinks() will check this class for methods and properties
$this->documentationClass = $class;
// Get the properties
$properties = rekey($class->getProperties(), "name");
// Sort the properties
uksort($properties, "strnatcasecmp");
// Get the methods
$methods = rekey($class->getMethods(), "name");
// Sort the methods
uksort($methods, "strnatcasecmp");
// Remove 'Nothing' namespace from className
$className = $class->getName();
if(substr($className, 0, 8) == "Nothing_") $className = substr($className, 8);
// Get the path to examples
@list($documentationPath) = Jackal::files(
	Jackal::setting("class-path") . "/Documentation",
	array(
		"MODULE" => $moduleName
	)
);

// Parse the properties into a structure for the HTML output
foreach($properties as $name=>$property) {
	// Parse the doc comment for this property
	$doc = $this->parseDocComment($property->getDocComment());
	// Parse out the flags for this property
	$flags = array_keys(array_filter($this->getModifiers($property)));
	// Get the class that declared this property
	$declaringClass = $property->getDeclaringClass();
	// See if this is a local or inherited property
	$local = $declaringClass == $class;
	// Put the parsed property back in the array
	$properties[$name] = array(
		"definedBy" => $declaringClass->name,
		"doc"       => $doc,
		"flags"     => $flags,
		"isLocal"   => $local,
		"local"     => $local ? "local" : "inherited",
		"name"      => $property->name,
		"page"      => $local ? "" : Jackal::siteURL("Documentation/rightPane/$declaringClass->name"),
	);
	// Add the doc comment information to the property
	$properties[$name] += $properties[$name]["doc"];
}

// Parse the documentation into a structure for the HTML output
foreach($methods as $i=>$method) {
	// Search upward for docComments
	for($parent = $class, $docComment=""; ($parent) && (!$docComment); $parent = $parent->getParentClass()) 
		$docComment = $parent->getMethod($method->name)->getDocComment();
	// Put the documentation into the parsed method
	$doc = $this->parseDocComment(array($docComment, $documentationPath));
	// Get the class in which this method was declared
	$declaringClass = $method->getDeclaringClass();
	// See if this is a local method
	$local = $declaringClass == $class;
	// Put this item back into the array
	$methods[$i] = array(
		"class"     => $declaringClass,
		"definedBy" => $declaringClass->name,
		"doc"       => $doc,
		"flags"     => array_keys(array_filter($this->getModifiers(array($method)))),
		"isLocal"   => $local,
		"local"     => $local ? "local" : "inherited",
		"name"      => $method->name,
		"page"      => $local ? "" : Jackal::siteURL("Documentation/rightPane/$declaringClass->name"),
	);
	// Add the doc comment information to the method
	$methods[$i] += $methods[$i]["doc"];
}

// Start output buffering so we can format code blocks later
ob_start();

echo "
	<div class='documentation-reference-section reference'>";

if(count($narrative)) {
	echo "
		<div class='class-information'>
			<div class='class-description'>";
	foreach($narrative as $paragraph) echo "
			<p>$paragraph</p>";
	echo"
			</div>
		</div>";
}

echo "
		<div class='class-properties'>
			<a name='this-class-properties'></a>
			<h2>Properties</h2>
			<div class='inherited-links'>
				<a class='toggle-inherited' show='1'>Show inherited properties</a>
				<a class='toggle-inherited' show='0' style='display: none;'>Hide inherited properties</a>
			</div>
			<table>
				<tr class='header'>
					<th class='left' width='100%'> Property </th>
					<th class='right'> Defined By </th>
				</tr>";
$i=0;

// Properties
foreach($properties as $property) {
	// Row alternator
	$oddEven = ($i++%2) ? "even" : "odd" ;
	
	echo "
				<tr local='$property[local]' class='$oddEven'>
					<td>
						<a href='$property[page]#$property[name]-property'>\$$property[name]</a> : <a class='return'>$property[type]</a>
						<div>";
	foreach($property["flags"] as $flag) 
		echo "
							<div class='flag $flag-flag' title='$flag'><q>$flag</q></div>";
	echo $this->addLinks("
							$property[summary]
						</div>
					</td>
					<td>
						$property[definedBy]
					</td>
				</tr>");
}

echo "
			</table>
		</div>
		<div class='class-methods'>
			<a name='this-class-methods'><span id='ClassName' style='display: none;'>$className</span></a>
			<h2>Methods</h2>
			<div class='inherited-links'>
				<a class='toggle-inherited' show='1'>Show inherited methods</a>
				<a class='toggle-inherited' show='0' style='display: none;'>Hide inherited methods</a>
			</div>
			<table>
				<tr class='header'>
					<th class='left' width='100%'> Method </th>
					<th class='right'> Defined By </th>
				</tr>";

// Methods
foreach($methods as $method) {
	// Row alternator
	$oddEven = ($i++%2) ? "even" : "odd" ;
	echo "
				<tr local='$local' class='$oddEven'>
					<td>
						<a href='$method[page]#$method[name]-method'>$method[name]()</a> : <a class='return'>$method[returnType]</a>
						<div>";
	foreach($method["flags"] as $flag) 
		echo "
							<div class='flag $flag-flag' title='$flag'><q>$flag</q></div>";
	echo $this->addLinks("
							$method[summary]
						</div>
					</td>
					<td>
						$method[definedBy]
					</td>
				</tr>");
}

echo "
			</table>
		</div>
		<div class='property-details'>
			<h1>Property Details</h1>";
foreach($properties as $property) {
	// Only show local properties / methods
	if(!$property["isLocal"]) continue;
	
	echo "
			<a name='$property[name]-property'></a>
			<h2>\$$property[name] : $property[type]</h2>
			<div class='description'>
				<p>{$this->addLinks($property["summary"])}</p>";
	foreach($property["description"] as $paragraph) 
		echo "
				<p>{$this->addLinks($paragraph)}</p>";
	echo "
			</div>";
}

echo "
		</div>
		<div class='method-details'>
			<h1>Method Details</h1>";
foreach($methods as $method) {
	echo "
			<div class='method'>
				<a name='$method[name]-method'></a>
				<h2>$method[name]() : $method[returnType]</h2>
				<div class='description'>
					{$this->addLinks($method["summary"])}";
	foreach($method["description"] as $paragraph) echo "
					<p>{$this->addLinks($paragraph)}</p>";
	
	if(count($method["segments"])) {
		$headerRow = array();
		$bodyRow = array();
		foreach ($doc["segments"] as $key=>$segment) {
			$headerRow[] = "<th>[<b>$key</b>]</th>";
			$bodyRow[] = "<td>\$$segment</td>";
		}
		echo "
					<div class='segments'>
						<table>
							<tr>
								<th class='title'>Segment</th>
								",join("<th></th>", $headerRow),"
							</tr>
							<tr>
								<th class='title'>Parameter</th>
								",join("<td class='slash'>/</td>", $bodyRow),"
							</tr>
						</table>
					</div>";
	}
					
	if (count($method["parameters"])) {
		echo "
					<div class='method-parameter-details'>
						<table>
							<thead>
								<tr>
									<th>Parameter</th>
									<th>Type</th>
									<th width='100%'>Details</th>
								</tr>
							</thead>";
		foreach($method["parameters"] as $parameter) echo "
							<tr>
								<th nowrap>$parameter[name]</th>
								<td nowrap>{$this->addLinks($parameter["type"])}</td>
								<td width='100%'>{$this->addLinks($parameter["description"])}</td>
							</tr>";
		echo "
						</table>
					</div>";
	}
	
	foreach($method["examples"] as $example) echo "
				<p class='example'>
					<h4>$example[title]</h4>
					$example[body]
				</p>";
	echo "
				</div>
			</div>"; 
}

echo "
		</div>
	</div>
	";

$buffer = ob_get_contents();
ob_end_clean();

// FIXME: Addlinks

echo preg_replace(
	'~<code (.*?)(?:type|language)\s*=\s*[\'"](.*?)[\'"](.*?)>(.*?)</code>~se', 
	'"<code $1type=\"$2\"$3 class=\"brush: $2\">" . stripslashes(htmlentities(\'$4\')) . "</code>"', 
	$buffer
);

?>
<script type="text/javascript">
(function(NS, $) {
	//  __________________________________________
	// /---------- Initialize namespace ----------\
	(window[NS]) || (window[NS] = {});
	var $ns, ns = window[NS];
	// \__________________________________________/

	//  _[ Initialize ]___________________________________
	// |                                                  |
	// | One-time initialization of namespace             |
	// |__________________________________________________|
	ns.initialize = function() {
		// Find the namespace element
		$ns = $("." + NS);

		// Hide inherited properties by default
		$ns.find("tr[local=inherited]").hide();

		// Sync the top nav
		var f, u;
		(f = parent.frames["title"]) 
		&& (f.location.href != (u = url("Documentation/moduleTitle/" + $("#ClassName").text(), false)))
		&& (f.location.href = u);

		// Format code
		SyntaxHighlighter.config.tagName = "code";
		SyntaxHighlighter.all();
		
		// Connect event listeners
		ns.rebind();
	};
	
	//  _[ Rebind ]_______________________________________
	// |                                                  |
	// | Reconnect event listeners to their elements      |
	// | (usually as a result of an ajax call)            |
	// |__________________________________________________|
	ns.rebind = function() {
		$ns.find(".toggle-inherited").unbind("click").click(ns.toggleInherited);
	};
	
	//  _[ Toggle Inherited ]_____________________________
	// |                                                  |
	// | Show or hide the inherited rows of the methods   |
	// | or properties table                              |
	// |__________________________________________________|
	ns.toggleInherited = function() {
		var $this = $(this);
		var show = $this.attr("show") == 1;
		
		// Flip the show/hide links
		$this.siblings().andSelf().toggle();
		// Show or hide the inherited rows
		$this.closest(".class-methods,.class-properties").find("tr[local=inherited]").toggle(show);
	};
	
	$(ns.initialize);
})("documentation-reference-section", jQuery);
</script>