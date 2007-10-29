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



/**
 * Class autoloader.
 *
 * @param string $class The class that's currently missing.
 * @return bool True if the class could be loaded. Else execution stops anyway.
 */
function qb_autoload($class) {
	// Remove suspicious characters.
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
	assert(is_scalar($value));
	if (!defined($name))
		define($name, $value);
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
if (!function_exists('__autoload'))
	eval('function __autoload($class) { return (qb_autoload($class)); }');

// Load the default exceptions.
new qbException();



?>
