<?php

/**
 * Returns version information about a path
 * 
 * Returns an array with version information about the module, library, or 
 * the entire jackal installation. 
 * 
 * @param $URI[0] Alias for $URI[type]
 * @param $URI[type] The type of component to look for 
 * 		(|module|library|jackal|private)
 * @param $URI[1] Alias for $URI[component]
 * @param $URI[component] The name of the library or module to provide 
 * 		information for 
 * 
 * @return array
 */

//  __________________________________________________
// / Parse URI                                        \

@( ($type = $URI["type"]) || ($type = $URI[0]) );
@( ($component = $URI["component"]) || ($component = $URI[1]) );

// \__________________________________________________/

//  __________________________________________________
// / Initialize version data                          \

if(!@$this->functionData) {
	Jackal::loadLibrary("Spyc");
	$this->functionData = spyc_load("
Exception: 
QueryBuilder: 
QueryBuilder_5: 
Table: 
YAMLDump: 
YAMLLoad: 
YAMLLoadString: 
__construct: 
__load: 
__loadString: 
_debuggingCheck: 
_delete: 
_doFolding: 
_doLiteralBlock: 
_dumpNode: 
_include: 
_include_once: 
_info_BASE_DIR: 
_info_SETTINGS_FILE: 
_inlineEscape: 
_loadConfigs: 
_parseLine: 
_resolveReference: 
_start: 
_startLogging: 
_toType: 
_yamlize: 
_yamlizeArray: 
action: 
addCalculations: 
addslashes: (PHP 4, PHP 5)
ajax: 
analyzeURI: 
array: 
array_combine: (PHP 5)
array_diff: '(PHP 4 >= 4.0.1, PHP 5)'
array_diff_key: '(PHP 5 >= 5.1.0)'
array_flip: (PHP 4, PHP 5)
array_intersect_key: '(PHP 5 >= 5.1.0)'
array_keys: (PHP 4, PHP 5)
array_merge: (PHP 4, PHP 5)
array_pop: (PHP 4, PHP 5)
array_reverse: (PHP 4, PHP 5)
array_shift: (PHP 4, PHP 5)
array_slice: (PHP 4, PHP 5)
array_unique: '(PHP 4 >= 4.0.1, PHP 5)'
array_unshift: (PHP 4, PHP 5)
array_values: (PHP 4, PHP 5)
assert: (PHP 4, PHP 5)
assert_options: (PHP 4, PHP 5)
attr: 
basename: (PHP 4, PHP 5)
call: 
call_user_func: (PHP 4, PHP 5)
call_user_func_array: '(PHP 4 >= 4.0.4, PHP 5)'
chmod: (PHP 4, PHP 5)
chr: (PHP 4, PHP 5)
class: 
className: 
class_exists: (PHP 4, PHP 5)
coalesce: 
compact: (PHP 4, PHP 5)
count: (PHP 4, PHP 5)
createClass: 
create_function: '(PHP 4 >= 4.0.1, PHP 5)'
css: 
currentURL: 
date: (PHP 4, PHP 5)
date_default_timezone_set: '(PHP 5 >= 5.1.0)'
debug_backtrace: '(PHP 4 >= 4.3.0, PHP 5)'
debugging: 
define: (PHP 4, PHP 5)
defined: (PHP 4, PHP 5)
delete: 
die: (PHP 4, PHP 5)
dirname: (PHP 4, PHP 5)
dump: 
e: 
each: (PHP 4, PHP 5)
elseif: 
empty: (PHP 4, PHP 5)
end: (PHP 4, PHP 5)
endMark: 
erase: 
error: 
error_log: (PHP 4, PHP 5)
error_reporting: (PHP 4, PHP 5)
eval: (PHP 4, PHP 5)
executeMethod: 
explode: (PHP 4, PHP 5)
extract: (PHP 4, PHP 5)
extractFields: 
f: 
file: (PHP 4, PHP 5)
file_exists: (PHP 4, PHP 5)
filemtime: (PHP 4, PHP 5)
files: 
find: 
findOne: 
findOrBlank: 
fixDB: 
fixTable: 
flag: 
flagCheck: 
floatval: '(PHP 4 >= 4.2.0, PHP 5)'
flush: (PHP 4, PHP 5)
folder: 
for: 
foreach: 
form_field: 
func_get_arg: (PHP 4, PHP 5)
func_get_args: (PHP 4, PHP 5)
func_num_args: (PHP 4, PHP 5)
function: 
function_exists: (PHP 4, PHP 5)
getBlank: 
getClass: 
getDBTimes: 
getDefinition: 
getElementById: 
getErrorLevel: 
getModelClass: 
getModuleDir: 
getStructure: 
getTable: 
getTimes: 
get_class: (PHP 4, PHP 5)
get_current_user: (PHP 4, PHP 5)
gettype: (PHP 4, PHP 5)
glob: '(PHP 4 >= 4.3.0, PHP 5)'
greedilyNeedNextLine: 
groupArray: 
handleRequest: 
header: (PHP 4, PHP 5)
headers_sent: (PHP 4, PHP 5)
html_quote: 
htmlentities: (PHP 4, PHP 5)
http_build_query: (PHP 5)
if: 0
implode: (PHP 4, PHP 5)
in_array: 
include: 
include_once: 
info: 
ini_set: (PHP 4, PHP 5)
insert: 
insertDefaults: 
intval: (PHP 4, PHP 5)
is_array: (PHP 4, PHP 5)
is_bool: (PHP 4, PHP 5)
is_dir: (PHP 4, PHP 5)
is_file: (PHP 4, PHP 5)
is_float: (PHP 4, PHP 5)
is_int: (PHP 4, PHP 5)
is_null: '(PHP 4 >= 4.0.4, PHP 5)'
is_numeric: (PHP 4, PHP 5)
is_scalar: '(PHP 4 >= 4.0.5, PHP 5)'
is_string: (PHP 4, PHP 5)
is_writable: (PHP 4, PHP 5)
isset: (PHP 4, PHP 5)
jackal__handleError: 
join: (PHP 4, PHP 5)
js: 
json_encode: '(PHP 5 >= 5.2.0, PECL json >= 1.2.0)'
key: (PHP 4, PHP 5)
list: (PHP 4, PHP 5)
load: 
loadHelper: 
loadLibrary: 
loadWithSource: 
ltrim: (PHP 4, PHP 5)
mark: 
markDB: 
md5: (PHP 4, PHP 5)
method: 
methodName: 
method_exists: (PHP 4, PHP 5)
microtime: (PHP 4, PHP 5)
model: 
name: 
next: (PHP 4, PHP 5)
ob_end_clean: (PHP 4, PHP 5)
ob_get_contents: (PHP 4, PHP 5)
ob_start: (PHP 4, PHP 5)
obfuscate: 
parse_str: (PHP 4, PHP 5)
pluralize: 
preg_grep: (PHP 4, PHP 5)
preg_match: (PHP 4, PHP 5)
preg_match_all: (PHP 4, PHP 5)
preg_replace: (PHP 4, PHP 5)
print: (PHP 4, PHP 5)
print_r: (PHP 4, PHP 5)
putSettings: 
query: 
rand: (PHP 4, PHP 5)
readfile: (PHP 4, PHP 5)
realpath: (PHP 4, PHP 5)
redirect: (PHP 4, PHP 5)
rekey: 
reset: (PHP 4, PHP 5)
resources: 
returnCall: 
rtrim: (PHP 4, PHP 5)
save: 
scope: 
serialize: (PHP 4, PHP 5)
set_error_handler: '(PHP 4 >= 4.0.1, PHP 5)'
set_error_level: 
set_time_limit: (PHP 4, PHP 5)
setting: 
settype: (PHP 4, PHP 5)
siteURL: 
splitAttributes: 
spyc_load: 
spyc_load_file: 
start: 
str_repeat: (PHP 4, PHP 5)
str_replace: (PHP 4, PHP 5)
strlen: (PHP 4, PHP 5)
strpos: (PHP 4, PHP 5)
strtok: (PHP 4, PHP 5)
strtolower: (PHP 4, PHP 5)
strtoupper: (PHP 4, PHP 5)
strtr: (PHP 4, PHP 5)
strval: (PHP 4, PHP 5)
substr: (PHP 4, PHP 5)
switch: 
table: 
time: (PHP 4, PHP 5)
touch: (PHP 4, PHP 5)
trigger_error: '(PHP 4 >= 4.0.1, PHP 5)'
trim: (PHP 4, PHP 5)
ucfirst: (PHP 4, PHP 5)
unset: (PHP 4, PHP 5)
update: 
url: 
url2uri: 
urldecode: (PHP 4, PHP 5)
urlencode: (PHP 4, PHP 5)
while: 
wordwrap: '(PHP 4 >= 4.0.2, PHP 5)'
write: 
	");
	
	foreach($this->functionData as $name=>$version) {
		if(!is_array($version)) {
			preg_match_all('/PHP([\s\d\.]+)[\s=>]*([\d\.]+)/', $version, $matches);
			$this->functionData[$name] = @min($matches[2]);
		}
	}
}

// \__________________________________________________/


if(false); // HAX for legibility

elseif($type == "jackal") {
	// Get the path to the jackal folder
	$jackalFolder = Jackal::files("<ROOT>/<JACKAL>");
	// Get all the files up to 10 folders deep
	$files = array_flip(Jackal::files("<ROOT>/<JACKAL>/{*/,{*/,{*/,{*/,{*/,{*/,{*/,{*/,{*/,{*/,}}}}}}}}}}*.php"));
	
	// Go through all the files and get version information for each one
	foreach($files as $file=>$discard) {
		// Get the version data for this file
		$data = $this->_versionData("file", $file);
		$files[$file] = $data;
	}
	
	return $files;
}

elseif($type == "file") {
	$contents = file_get_contents($component);
	// Find all the function calls
	preg_match_all('/((?:function|new)\s+)?(?<!:|\w|>|$)(\w+)\(/', $contents, $matches, PREG_SET_ORDER);
	
	foreach($matches as $match) {
		list($discard, $noise, $call) = $match;
		if($noise) continue;
		
		if(!isset($this->functionData[$call])) {
			if(Jackal::debugging()) {
				set_time_limit(0);
				$html = Jackal::call("Curl/get", "http://www.php.net/$call");
				$match = preg_match('/class="verinfo".*?>([^<]+)/', $html, $matches);
				printf('Function found: "%s" => "%s",', $call, @$matches[1]);
				$newData = true;
			}
			$this->functionData[$call] = @$matches[1];
		}
		
		$dependencies[$call] = $this->functionData[$call];
	}
	
	$spyc = Jackal::loadLibrary("Spyc");
	
	if(@$newData) {
		/** @var Spyc $spyc */
		echo "You need to update ".__FILE__." with this information: ";
		echo '<pre>', htmlentities(print_r($spyc->YAMLDump($this->functionData), 1)), '</pre>';
		exit();
	}
	
	return $dependencies;
}

elseif($type == "") {
	Jackal::error(500, "You must provide a type");
}

else {
	Jackal::error(501, "Type $type not supported");
}