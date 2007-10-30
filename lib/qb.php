<?php

/* Copyright 2007 Tim Weber <scy-proj-qb@scytale.name>

   This file is part of qb <http://scytale.name/proj/qb/>.
   qb is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License,
   version 3, as published by the Free Software Foundation.

   qb is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program.  If not, see <http://www.gnu.org/licenses/>.
   */



define('QB_VERSION', '0.5alpha');

/**
 * Class autoloader.
 *
 * @param string $class The class that's currently missing.
 * @return bool True if the class could be loaded. Else execution stops anyway.
 */
function qb_autoload($class) {
	assert(is_string($class));
	// Make sure all exceptions are loaded, they live together in a single file.
	require_once(QB_LIBDIR . '/qbException.php');
	// If the class exists now, it was an exception and everything's fine.
	if (class_exists($class, false))
		return (true);
	$class = preg_replace('/[^a-zA-Z0-9]/', '', $class);
	// Include the class, die if that fails.
	require_once(QB_LIBDIR . "/$class.php");
	return (true);
}

/**
 * Define a constant, if it isn't already defined.
 */
function qb_define($name, $value) {
	assert(is_string($name));
	assert(is_scalar($value) || is_null($value));
	if (!defined($name))
		define($name, $value);
}

/**
 * Test the installation.
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
}

/**
 * Enable debug mode.
 */
function qb_debug($switch) {
	if ($switch) {
		error_reporting(E_ALL);
		foreach (array(ASSERT_ACTIVE, ASSERT_WARNING, ASSERT_BAIL) as $opt)
			if (assert_options($opt, 1) === false)
				die("QB DEBUG ERROR: Could not set assert option $opt!\n");
	}
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
