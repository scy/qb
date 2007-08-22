<?php

// Class autoloader.
function __autoload($class) {
	// Remove suspicious characters.
	$class = preg_replace('/[^a-zA-Z0-9]/', '', $class);
	// Include the class, die if that fails.
	require_once("lib/$class/$class.php");
}

// Runs the 0.2 qb.
function qb() {
	// Right now, we still need the old stuff.
	require('lib/qb-0.2.php');
}

?>
