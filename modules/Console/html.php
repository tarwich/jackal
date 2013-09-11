<?php

// Make sure UI is available
returnCall("UI/button/discard");

echo "
	<div class='toggle-console-button'></div>
	<div class='console-body'>
		<div class='output'>
		</div>
		<div class='input'>
			<input type='text' name='console-input' />
			<button>Go</button>
		</div>
	</div>
";