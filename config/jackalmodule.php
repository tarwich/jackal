<?php

// JackalModule Settings
Jackal::putSettings("
	# Tell the users module to allow access to global resources
	users:
		acl:
			IP * ACCESS JackalModule/resources ALLOW
");
