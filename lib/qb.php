<?php

/* Copyright 2007 Tim Weber <scy-proj-qb@scytale.name>

   This file is part of qb <http://scytale.name/proj/qb/>.

   See the LICENSE file for legal stuff.
   */



define('QB_VERSION', '0.5alpha');

/**
 * Class autoloader.
 * We don't stop script execution if a class could not be loaded. That way, more
 * than one autoloader can be chained: There can be only one __autoload()
 * function in a PHP script. qb checks whether there's already one defined, and
 * if not, redirects it to this function. So if you are embedding qb in a larger
 * project, you can write your own __autoload() that will call, among others,
 * qb_autoload(). If no autoloader could load the class, execution will stop
 * anyway.
 * @param string $class The class that's currently missing.
 * @return bool True if the class could be loaded, false if not.
 */
function qb_autoload($class) {
	assert(is_string($class));
	// Make sure all exceptions are loaded, they live together in a single file.
	require_once(QB_LIBDIR . '/qbException.php');
	// If the class exists now, it was an exception and everything's fine.
	if (class_exists($class, false))
		return (true);
	$class = preg_replace('/[^a-zA-Z0-9]/', '', $class);
	// Try to include the class.
	include_once(QB_LIBDIR . "/$class.php");
	// Return whether or not the class exists now.
	return (class_exists($class, false));
}

/**
 * Define a constant, if it isn't already defined.
 * This function behaves somehow like define(), except that it doesn't try to
 * define the specified constant if it already exists. That way you can override
 * even qb's internal constants by defining them before including qb.
 * @param string $name  The name of the constant.
 * @param scalar $value Its value, normal PHP constant value restrictions apply.
 * @return bool True if the constant was not defined before (and thus is defined
 *   now), false if it already was defined or if the definition did not succeed.
 */
function qb_define($name, $value) {
	assert(is_string($name));
	assert(is_scalar($value) || is_null($value));
	if (!defined($name))
		return (define($name, $value));
	return (false);
}

/**
 * Test the installation.
 * This function checks some of the basic functionality of qb (for example class
 * autoloading) and shows the value of some internal constants as well as other
 * useful information like qbURL return values. It can be used to debug your qb
 * installation.
 * @return bool True if the function was executed to the end.
 */
function qb_test() {
	header('Content-type: text/plain');
	echo("Hi. This is qb " . QB_VERSION . ", nice to meet you.\n");
	echo("If you can read this, you've included qb correctly and requested some tests.\n\n");
	echo("First of all, some basic settings and what they are set to:\n");
	echo("qb is installed in:   " . QB_LIBDIR .  "\n");
	echo("Request origin:       " . QB_REQDIR .  "\n");
	echo("Server document root: " . QB_DOCROOT . "\n");
	echo("Requested URI path:   " . QB_URIPATH . "\n\n");
	if (QB_OURAUTOLOAD) {
		echo("qb will take care of autoloading its own classes.\n");
		echo("If your application needs an __autoload() function, please define it\n");
		echo("before including qb and call qb_autoload(\$class) as a fallback.\n");
	} else {
		echo("You have defined your own class autoloader. If autoloading of qb's classes\n");
		echo("fails, make sure it is calling qb_autoload(\$class) as a fallback.\n");
	}
	echo("\nChecking class autoloading...\n");
	new qbException();
	echo("Class autoloading seems to be working.\n\n");
	echo("BaseDir is: " . qbURL::getBaseDir() . "\n");
	echo("BaseURL is: " . qbURL::getBaseURL() . "\n");
	echo("Thus, virtual file requested is: " . qbURL::getVFile() . "\n");
	echo("\nThis is the end of the automatic tests.\n");
	echo("Check out http://scytale.name/proj/qb/ if something doesn't work.\n");
	return (true);
}

/**
 * Enable debug mode.
 * This is nothing but a shorthand for setting PHP's error reporting to the most
 * verbose level, activating assertions and quit on assertion failure. You
 * should be able to enable this even on production systems, as it should not
 * report any warnings at all if everything is fine.
 * @param bool $switch Set to true to enable debugging, set to false to disable.
 * @return bool True.
 * @todo The parameter sucks, since "false" doesn't do anything and this should
 *   maybe be used to enable some kind of "verbose mode" as well.
 */
function qb_debug($switch) {
	if ($switch) {
		error_reporting(E_ALL);
		foreach (array(ASSERT_ACTIVE, ASSERT_WARNING, ASSERT_BAIL) as $opt)
			if (assert_options($opt, 1) === false)
				die("QB DEBUG ERROR: Could not set assert option $opt!\n");
	}
	return (true);
}

// If there's no information where qb is installed, defaults to "here".
qb_define('QB_LIBDIR', realpath(dirname(__FILE__)));

// Set the "request directory", ie. the cwd when the request occured.
// This should be where the file that includes qb is located.
qb_define('QB_REQDIR', realpath(getcwd()));

// Set the document root of the web server.
qb_define('QB_DOCROOT', realpath($_SERVER['DOCUMENT_ROOT']));

// Store the path compontent of the requested URL.
qb_define('QB_URIPATH', $_SERVER['REQUEST_URI']);

// If there is no class autoloader currently set, define ours.
if (!function_exists('__autoload')) {
	eval('function __autoload($class) { return (qb_autoload($class)); }');
	define('QB_OURAUTOLOAD', true);
} else {
	define('QB_OURAUTOLOAD', false);
}



?>
