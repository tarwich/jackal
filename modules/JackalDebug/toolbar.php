<?php

return;

if(!Jackal::debugging()) return;

css("debug.css");
js("debug.js");

//
// Format the times for the profiler
//
$times = array();
$totalTime = $difference = 0;

foreach(JackalTimes::getTimes() as $time) {
	if(@$lastTime) {
		$difference=@$lastTime["finish"] 
		? $lastTime["finish"] - $lastTime["time"]
		: $time["time"] - $lastTime["time"];
		
		$times[] = array(
			"Marker" => $lastTime["name"],
			"Time (s,mmm.nn)" => number_format($difference*1000, 2),
			"Offset" => sprintf("%f", $lastTime["time"])
			);
	}
	$totalTime += $difference;
	$lastTime = $time;
}

$times[] = array(
	"Marker" => $lastTime["name"],
	"Time (s,mmm.nn)" => "n/a",
	"Offset" => sprintf("%f", $lastTime["time"])
	);

?>
<div id="jackalDebugToolbar" class="jSection-debug">
	<div class='debug-wrapper ui-data-list' style='display: none;'>
		<?php
		Jackal::call("UI/tabs", array(
			array( "html" => "Profiler", "tab" => "profiler" ),
			array( "html" => "Database", "tab" => "database" ),
			array( "html" => "Server",   "tab" => "server" ),
			array( "html" => "Request",  "tab" => "request" )
			));
		?>
		<div class="debug-tab profiler">
				<?php
				Jackal::call("UI/data-list", array(
					"headers" => array("Marker", "Time (s,mmm.nn)", "Offset"),
					"data" => $times
					));
				?>
				<div class='debug-note'>
					Time: <b><?php printf("%f", $totalTime); ?></b> seconds <br/>
					<i>Note: Time is displayed as second,milliseconds.nanoseconds</i><br/>
					<i>Note: The time for JackalDebug/toolbar is inaccurate as it is impractical to tell the time that this script took before it is complete</i>
				</div>
		</div>
		<div class="debug-tab database">
			<p>
			<?php 
			if(class_exists("QueryBuilder")) {
				//printArray(QueryBuilder::instance()->queries);
			}
			?>
			</p>
		</div>
		<div class="debug-tab server">
			<?php
			$server = array();

			foreach($_SERVER as $key=>$value) {
				$server[] = array($key, $value);
			}

			Jackal::call("UI/data-list", array(
					"headers" => array("Key", "Value"),
					"data" => $server
					));
			?>
		</div>
		<div class="debug-tab request">
			<?php
				$get = array();
				foreach($_GET as $name=>$value) $get[] = array($name, $value);
				$post = array();
				foreach($_POST as $name=>$value) $post[] = array($name, $value);
				$cookies = array();
				foreach($_COOKIE as $name=>$value) $cookies = array($name, $value);

				Jackal::call("UI/accordion", array(
					"GET" => $get,
					"POST" => $post,
					"COOKIES" => $cookies
				));
			?>
		</div>
	</div>
	<span class="debug-wrench">
		<?php Jackal::call("UI/Button/Debug"); ?>
	</span>
</div>
