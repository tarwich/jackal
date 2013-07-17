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

// Start output buffering so we can format code blocks later
ob_start();

echo "
	<div class='documentation-reference-section reference'>";

if(count($narrative)) {
	echo "
		<div class='class-information'>
			<div class='class-description'>
				".$this->addLinks(
					"<p>" . implode("</p><p>", $narrative) . "</p>"
				)."
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
	/* @var $property ReflectionProperty */
	$doc = $this->parseDocComment($property->getDocComment());
	// Get the names of all the flags that are set
	$flags = array_keys(array_filter($this->getModifiers($property)));
	// See if this is an inherited property
	if($property->getDeclaringClass() == $class) {
		$local = "local";
		$definedBy = $className;
		$page = "";
	} else {
		$local = "inherited";
		$definedBy = $property->getDeclaringClass()->name;
		$page = Jackal::siteURL("Documentation/rightPane/$definedBy");
	}
	$oddEven = ($i++%2) ? "even" : "odd" ;
	echo "
				<tr local='$local' class='$oddEven'>
					<td>
						<a href='$page#$property->name-property'>\$$property->name</a> : <a class='return'>$doc[type]</a>
						<div>";
						
foreach($flags as $flag) {
	echo "<div class='flag $flag-flag' title='$flag'><q>$flag</q></div>";
} 
						
echo "
							{$this->addLinks($doc["summary"])}
						</div>
					</td>
					<td>
						".($page?"<a href='$page'>$definedBy</a>":"$definedBy")."
					</td>
				</tr>";
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
	/* @var $method ReflectionMethod */
	$doc = $this->parseDocComment($method->getDocComment());
	// Get the names of all the flags that are set
	$flags = array_keys(array_filter($this->getModifiers($method)));
	
	// See if this is an inherited property
	if($method->getDeclaringClass() == $class) {
		$local = "local";
		$definedBy = $className;
		$page = "";
	} else {
		$local = "inherited";
		$definedBy = $method->getDeclaringClass()->name;
		$page = Jackal::siteURL("Documentation/rightPane/$definedBy");
	}
	$oddEven = ($i++%2) ? "even" : "odd" ;
	echo "
				<tr local='$local' class='$oddEven'>
					<td>
						<a href='$page#$method->name-method'>$method->name()</a> : <a class='return'>$doc[returnType]</a>
						<div>";
foreach($flags as $flag) {
	echo "<div class='flag $flag-flag' title='$flag'><q>$flag</q></div>";
} 
						
echo "
							{$this->addLinks($doc["summary"])}
						</div>
					</td>
					<td>
						".($page?"<a href='$page'>$definedBy</a>":"$definedBy")."
					</td>
				</tr>";
}

echo "
			</table>
		</div>
		<div class='property-details'>
			<h1>Property Details</h1>";

foreach($properties as $property) {
	$doc = $this->parseDocComment($property->getDocComment());
	// Only show local properties / methods
	if($property->getDeclaringClass() != $class) continue;
	
	echo "
			<a name='$property->name-property'></a>
			<h2>\$$property->name</h2>
			\$$property->name : $doc[type]
			<div class='description'>
				".$this->addLinks(
					$doc["summary"]
					. "<p>" . implode("</p><p>", $doc["description"]) . "</p>"
				)."
			</div>"; 
}

echo "
		</div>
		<div class='method-details'>
			<h1>Method Details</h1>";

foreach($methods as $method) {
	$doc = $this->parseDocComment(array($method->getDocComment(), $documentationPath));
	// Only show local properties / methods
	if($method->getDeclaringClass() != $class) continue;
	
	echo "
			<div class='method'>
				<a name='$method->name-method'></a>
				<h2>$method->name() : $doc[returnType]</h2>
				<div class='description'>
					".$this->addLinks(
						$doc["summary"]
						."<p>".implode("</p><p>", $doc["description"])
					)."</p>";
	
	if(count($doc["segments"])) {
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
					
	if (count($doc["parameters"])) {
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

		foreach($doc["parameters"] as $parameter) {
			echo "
							<tr>
								<th nowrap>$parameter[name]</th>
								<td nowrap>",$this->addLinks($parameter["type"]),"</td>
								<td width='100%'>",$this->addLinks($parameter["description"]),"</td>
							</tr>";
		}
	
		echo "
						</table>
					</div>";
	}
	
	foreach($doc["examples"] as $example) {
		echo "
				<p class='example'>
					<h4>$example[title]</h4>
					$example[body]
				</p>";
	}
	
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