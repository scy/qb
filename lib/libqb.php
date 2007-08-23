<?php

// Class autoloader.
function __autoload($class) {
	// Remove suspicious characters.
	$class = preg_replace('/[^a-zA-Z0-9]/', '', $class);
	// Include the class, die if that fails.
	require_once("lib/$class/$class.php");
}

?>