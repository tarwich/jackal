<?php

//  __________________________________________________
// / Parse URI                                        \

@($moduleName = $URI["module"]) || @($moduleName = $URI[0]);
@($class = $URI["class"]) || @($class = $URI["segments"][1]) || ($class = false);

// \__________________________________________________/


//  __________________________________________________
// / Load helpers and libraries                       \


// \__________________________________________________/


//  __________________________________________________
// / Get module code                                  \

// Get the module path on the filesystem
$files = Jackal::files(Jackal::setting("class-path"), array(
	"MODULE" => $moduleName
));

// I am not sure if there would ever be more than one result, but this will help me find out
assert('count($files)==1; // There should only be one result');

// Get the first item (there should only be one)
$path = $files[0];

// LPK ~ Only try to get the class if it does not exist
if (!$class) {
	if(is_dir($path)) $class = $this->getModuleDataFromFolder(array($path));
	else $class = $this->getModuleDataFromFile(array($path));
}

// \__________________________________________________/

$className = $class->getName();
if(substr($className, 0, 8) == "Nothing_") $className = substr($className, 8);

echo "
	<div class='title'>
		<div>
			<table width='100%' border='0' cellpadding='0' cellspacing='0'>
				<tr>
					<td width='100%'>
						<h4>Jackal ".Jackal::$VERSION." Component Reference</h4>
						<h1>$className</h1>
					</td>
					<td nowrap>
						<div class='shortcuts'>
							<a href='".Jackal::siteURL("Documentation/moduleTOC")."'>Index</a>
							<span>|</span>
							<a href='#this-class-properties'>Properties</a>
							<span>|</span>
							<a href='#this-class-methods'>Methods</a>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<div class='title-spacer'></div>";

return $class;