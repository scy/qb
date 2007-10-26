<?php

/* * *  [[ WARNING: This code is not yet complete, no not use it! ]]  * * */

if (php_sapi_name() != 'cli')
	die("This needs to be run from the command line!\n");

// Where to find the config file.
$conf = 'lib/qb-0.2.conf.php';

// Allow $conf override as command line parameter.
if ($_SERVER['argc'] > 1)
	$conf = $_SERVER['argv'][1];

// Load config values.
require_once($conf);

// Check if QB_SRC is a valid directory.
if (!is_dir(QB_SRC))
	die("Source directory '" . QB_SRC . "' does not exist or is no directory.\n");



?>
