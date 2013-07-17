<?php

Jackal::putSettings('
	users:
		protected-resources:
			Documentation/*: public
		acl:
			IP * ACCESS Documentation/* ALLOW
');

