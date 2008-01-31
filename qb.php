<?php

// Access the 0.5 goodness.
require('lib/qb.php');

// Debug output. Comment this in to see whether URL auto-parsing works for you.
// echo("<!--\n\tBase dir: " . qbURL::getBaseDir() . "\n\tHandler: " . qbURL::getHandler() . "\n\tBase path: " . qbURL::getBasePath() . "\n\tVFile: " . qbURL::getVFile() . "\n\tRequest URI: " . $_SERVER['REQUEST_URI'] . "\n-->\n");

// Runs the 0.2 qb.
require('lib/qb-0.2.php');

?>
