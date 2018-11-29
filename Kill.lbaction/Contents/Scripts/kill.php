#!/usr/bin/php
<?php

// Use a copy of the original argv:

list($path, $pid) = $argv;

$return = exec(sprintf('kill -9 %s', $pid));
print json_encode([]);
