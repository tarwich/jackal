<?php

/**
 * The main index page for the admin
 * 
 * The purpose of this page is to show the admin.
 * 
 * @return void
 */

// Load jQuery
js("resources/jquery-1.10.2.min.js");
// Load the admin.js
js("admin.js");
// Ensure url.js is available
js("url.js");

echo "
	<table>
		<tr>
			<td>
				".Jackal::returnCall("Admin/sidebar")."
			</td>
			<td class='Admin-content'>
				".Jackal::returnCall("Admin/showSection")."
			</td>
		</tr>
	</table>";
