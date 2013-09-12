<?php

function jackal__handleError($errno, $errstr, $errfile, $errline, $errcontext) {
	if(!($errno&error_reporting())) return true;
	
	// Write the error to the error log
	error_log("$errstr $errfile:$errline");
	
	// If display errors is off, then don't show the error
	if(!ini_get("display_errors")) return false;
	
	$backtrace = debug_backtrace();
	$omitPath = dirname(dirname(dirname(__FILE__)));

    // If the program was run from the CLI
    if(Jackal::flag("CLI")) {
        // Load the ANSI library
        $ANSI = Jackal::loadLibrary("ANSI");
        // Write the error message, formatted for terminal
        echo "\n{$ANSI->RED}ERROR: $errstr {$ANSI->RESET}@ {$ANSI->WHITE}$errfile{$ANSI->RESET}:{$ANSI->WHITE}$errline\n";
        // Variable to hold the length of the longest call in the backtrace
        $maxCallLength = 0;
        // Variable to hold the length of the longest basename in the backtrace
        $maxBasenameLength = 0;
        // Variable to hold the length of the longest line number in the backtrace
        $maxLineLength = 0;
        // Array to hold the backtrace data
        $printBacktrace = array();

        // Get the elements from the backtrace that we need to print and get the max lengths for each "field"
        foreach($backtrace as $sequence) {
            if(false) {
                if(in_array(@$sequence["function"], array("trigger_error", "user_error"))) {
                    $sequence["file"] = $errfile;
                    $sequence["line"] = $errline;
                }
            }

            $sequence["file"] = str_replace($omitPath, "", @$sequence["file"]);
            $basename = basename($sequence["file"]);
            $call = ltrim(@"$sequence[class].$sequence[function]", ".");
            $line = rtrim(@"$sequence[line]", " :");
            // Add the elements of the sequence that we will print later to our printBacktrace array
            $printBacktrace[] = array(
                "call"     => "$call",
                "basename" => "$basename",
                "line"     => "$line"
            );
            // Update max width for the call
            $maxCallLength = max(strlen($call), $maxCallLength);
            // Update max width for the basename
            $maxBasenameLength = max(strlen($basename), $maxBasenameLength);
            // Update max width for the line
            $maxLineLength = max(strlen($line), $maxLineLength);
        }

        // Print our backtrace
        foreach($printBacktrace as $backtraceItem) {
            // Only print this item if both basename and line have values
            if(($backtraceItem['basename']) && ($backtraceItem['line']))
                printf("  --> {$ANSI->RESET}%s{$ANSI->WHITE}%s{$ANSI->RESET}:{$ANSI->WHITE}%s\n",
                    str_pad($backtraceItem['call'    ], $maxCallLength     + 1, " "),
                    str_pad($backtraceItem['basename'], $maxBasenameLength    , " "),
                    str_pad($backtraceItem['line'    ], $maxLineLength        , " ")
                );
        }

        return true;
    }

    // Otherwise, we need to format the error for a browser
    else {
        // Update the status of printing the error styles
        if(!@$GLOBALS["jackal-data"]["error-styles-printed"]) {
            $GLOBALS["jackal-data"]["error-styles-printed"] = true;

            ?>
<style type='text/css'>

div, a, h2, h3, span {
	margin: 0;
	padding: 0;
	border: 0;
	outline: 0;
	font-weight: inherit;
	font-style: inherit;
	font-size: 100%;
	font-family: inherit;
	vertical-align: baseline;
	color: inherit;
	}

div.jackal-error {
	background: #cccccc;
	background: linear-gradient(-90deg, #ffffff, #ebebeb) repeat-x;
	background: -moz-linear-gradient(-90deg, #ffffff, #ebebeb) repeat-x;
	background: -webkit-linear-gradient(-90deg, #ffffff, #ebebeb) repeat-x;
	background: -ms-linear-gradient(-90deg, #ffffff, #ebebeb) repeat-x; /* IE10 */
	background: -o-linear-gradient(-90deg, #ffffff, #ebebeb) repeat-x; /* Opera */
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ebebeb', endColorstr='#ffffff'); /* IE6 & 7 */
	-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr='#ebebeb', endColorstr='#ffffff')"; /* IE8+ */
	height: 400px;
	border: 5px solid #dcb7b7;
	border-radius: 10px;
	margin: 1px 0px;
	height: 44px;
	font-family: Courier New, Courier, Helvetica;
	padding: 0px;
}
div.jackal-error a {
	text-decoration: none !important;
}
div.jackal-error div.error-container {
	padding: 0px;
	position: relative;
	height: 40px;
	border: 1px solid #cecece;
	border-radius: 5px;
	margin: 1px;
}
div.jackal-error .error-icon {
	position: absolute;
	top: 5px;
	left: 5px;
	display: block;
	width: 30px;
	height: 30px;
	border-radius: 15px;
	font-size: 24px;
	color: #ffffff;
	line-height: 33px;
	text-align: center;
	font-weight: bold;
	background: #cc0000;
	background: linear-gradient(-90deg, #ce0808, #9d0707) repeat-x;
	background: -moz-linear-gradient(-90deg, #ce0808, #9d0707) repeat-x;
	background: -webkit-linear-gradient(-90deg, #ce0808, #9d0707) repeat-x;
	background: -ms-linear-gradient(-90deg, #ce0808, #9d0707) repeat-x; /* IE10 */
	background: -o-linear-gradient(-90deg, #ce0808, #9d0707) repeat-x; /* Opera */
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ce0808', endColorstr='#9d0707'); /* IE6 & 7 */
	-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr='#ce0808', endColorstr='#9d0707')"; /* IE8+ */
}
div.jackal-error div.error-title {
	padding: 6px 30px;
}
div.jackal-error h2 {
	padding: 0px 10px;
	font-size: 12px;
	line-height: 14px;
	height: 14px;
	font-weight: bold;
	color: #cc0000;
	text-decoration: none;
	background: transparent !important;

	/* make text unselectable */
	-moz-user-select: -moz-none;
	-khtml-user-select: none;
   	-webkit-user-select: none;
   	-o-user-select: none;
   	user-select: none;
}
div.jackal-error h3 {
	padding: 0px 10px;
	font-size: 12px;
	line-height: 14px;
	color: #333333;
	text-decoration: none;
	background: transparent !important;

	/* make text unselectable */
	-moz-user-select: -moz-none;
	-khtml-user-select: none;
   	-webkit-user-select: none;
   	-o-user-select: none;
   	user-select: none;
}
div.jackal-error a.error-target div.error-backtrace h3 i {
	padding: 0px 0px 0px 5px;
}
div.jackal-error div.error-backtrace {
	display: none;
	border-width: 0px 1px 1px 1px;
	border-color: #cecece;
	border-style: solid;
	background: #ececec;
	width: 100%;
	position: relative;
	top: 5px;
	cursor: default !important;
}
div.jackal-error a.error-target:focus .error-backtrace,
div.jackal-error a.error-target:active .error-backtrace {
	display: block;
	border-radius: 0px 0px 10px 10px;
	box-shadow: 3px 3px 3px rgba(0,0,0,.35);
	z-index: 9999;
}
div.jackal-error div.error-backtrace table {
	width: 100%;
}
div.jackal-error div.error-backtrace div {
	padding: 0px 10px 5px 10px;
}
div.jackal-error div.error-backtrace table tr td {
	color: #000000;
	font-weight: bold;
	font-size: 12px;
	padding: 5px 30px 5px 0px;
	white-space: nowrap;
	border-top: 1px solid #cecece;
}
div.jackal-error div.error-backtrace table tr td:last-child {
	width: 100%;
}
div.jackal-error div.error-backtrace table tr td i {
	color: #666666;
	padding: 0px 0px 0px 5px;
}

</style>
            <?php
        }

        echo "
            <div class='jackal-error'>
                <a class='error-target' href='http://inabilitytosupportfocusattributesucksballs.com' onclick='this.focus(); return false;' onmousedown='this.focus(); return false;'>
                    <div class='error-container'>
                        <span class='error-icon'>!</span>
                        <div class='error-title'>
                            <h2>$errstr</h2>
                            <h3>".(basename($errfile))."<i>:$errline</i></h3>
                            <div class='error-backtrace'>
                                <div>
                                    <table> ";

        // Remove ME
        //array_shift($backtrace);
        // Start the iterator
        $i = 0;

        foreach($backtrace as $sequence) {
            if(false)
            if(in_array(@$sequence["function"], array("trigger_error", "user_error"))) {
                $sequence["file"] = $errfile;
                $sequence["line"] = $errline;
            }

            $sequence["file"] = str_replace($omitPath, "", @$sequence["file"]);
            $i++;
            $l = ($i+1)%2;
            $basename = basename($sequence["file"]);
            $call = ltrim(@"$sequence[class].$sequence[function]", ".");
            $line = rtrim(@":$sequence[line]", " :");

            echo @"
                                        <tr class='r$l i$i'>
                                            <td>$basename<i>$line</i></td>
                                            <td>$call</td>
                                        </tr>";
        }

        echo "
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div> ";

        return true;
    }
}

set_error_handler("jackal__handleError");
