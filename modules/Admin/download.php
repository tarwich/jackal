<?php

Jackal::call("Template/disable");
header("Content-type: application/octet-stream");
header('Content-Disposition: attachment; filename="admin_.yaml"');

$file = Jackal::call("Session/read/admin/file");

echo $file;
