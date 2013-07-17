<style type="text/css">
.version-table {
	border-collapse: collapse;
}

.version-table td {
	border: 1px solid black;
	padding: 2px;
}

.version-table th {
	font-weight: bold;
	padding: 2px;
	border: 1px solid black;
}
</style>
<?php

/**
 * Shows the PHP version requirement for this Jackal installation 
 */

//  __________________________________________________
// / Parse URI                                        \

@( ($type = $URI["type"]) || ($type = $URI[0]) );
@( ($component = $URI["component"]) || ($component = $URI[1]) );

// \__________________________________________________/


if(!$type) {
	Jackal::call("UI/title/PHP Version Requirements");
	echo "<p>These are the PHP version requirements of all the components of this Jackal installation</p>";
	
	$data = $this->_versionData("jackal");
	$versions = array();
	
	foreach($data as $file=>$versionData) {
		foreach($versionData as $call=>$number) {
			if(!$number) continue;
			$versions[$number][] = "$call - $file";
		}
	}
	
	uksort($versions, "strnatcasecmp");
	
	echo "<table class='version-table'>";
	
	foreach($versions as $version=>$reason) {
		echo "<tr>
				<th rowspan=".count($reason).">PHP $version</th>";
		echo "<td>".implode("</td></tr><tr><td>", $reason)."</td>";
		echo "</tr>";
	}
	
	echo "</table>";
}

