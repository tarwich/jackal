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
//Load Google Font - This is likely temporary, in the event that Charles gets his way and we include his remote fonts.
css("http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800");
//Load Default Stylesheet
css("styles.css");

?><? 
	// This is the Header, Obviously 
	?>
	<style type='text/css' class='FIXME'>
		table.content {
			border-collapse: collapse;
			border-spacing: 0;
		}
		
		td.sidebar {
			width       : 180px;
			border      : 1px solid #000;
			padding-left: 65px;
			padding-top : 0px;
		}
	</style>
	<header>
		<h1 class="littleHeaderTitle">Practice Reports</h1>
		<a href="#" class="userLink">User</a>
	</header>
	<h1 class="pageTitle">Settings</h1>
	<table class='content'>
		<tr>
			<td>
				<?php
				// This is the Sidebar 
				Jackal::call("Admin/sidebar"); ?>
			</td>
			<td>
<?
				// This is the Content
				?>
				<div class="Admin-content">
					<?php echo Jackal::returnCall("Admin/showSection"); ?>
				</div>
			</td>
		</tr>
	</table>