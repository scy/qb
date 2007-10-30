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
 * Helper class for URL parsing, path mapping and things like that.
 */
class qbURL {

	/** Stores the base directory. */
	protected static $baseDir = null;
	
	/** Stores the base URL. */
	protected static $baseURL = null;

	/** Return the base directory.
	 *
	 * The base directory is the topmost directory we are allowed to serve
	 * files from. This is usually the directory where the calling script is
	 * installed.
	 */
	public static function getBaseDir() {
		// If none is defined, default to the request directory.
		if (self::$baseDir === null)
			return (self::setBaseDir(QB_REQDIR));
		return (self::$baseDir);
	}
	
	/**
	 * Set the base directory.
	 *
	 * Automatically adds a slash as last character.
	 */
	public static function setBaseDir($dir) {
		// Resolve the supplied directory.
		$base = realpath($dir);
		// If it doesn't exist, throw exception.
		if ($base === false)
			throw new qbFileNotFoundException($dir);
		// Set and return it.
		return (self::$baseDir = $base . '/');
	}
	
	/**
	 * Return the URL base.
	 */
	public static function getBaseURL() {
		// If none is defined, try to figure it out automatically.
		if (self::$baseURL === null) {
			// SCRIPT_NAME containes the alias name if aliased from outside, and
			// the virtual filename if not.
			$base = $_SERVER['SCRIPT_NAME'];
			return (self::setBaseURL($base));
		}
		return (self::$baseURL);
	}
	
	/**
	 * Set the URL base.
	 *
	 * The URL base is the part of the request path that will be stripped: qb
	 * might not be used to manage a whole host, but only a sub directory.
	 */
	public static function setBaseURL($path) {
		// If set to false or null, use auto-detection.
		if (($path === false) || ($path === null)) {
			self::$baseURL = null;
			return (self::getBaseURL());
		}
		assert(is_string($path));
		return (self::$baseURL = '/' . trim($path, '/'));
	}
	
	/**
	 * Return the virtual filename.
	 *
	 * This is the virtual file the client requested, ie. if qb manages the
	 * /blog/ directory and the URL http://example.com/blog/foo/bar is called,
	 * the virtual filename is /foo/bar.
	 */
	public static function getVFile() {
		$path = QB_URIPATH;
		if (qbString::startsWith(self::getBaseURL(), $path))
			$path = substr($path, strlen(self::getBaseURL()));
		return ('/' . trim($path, '/'));
	}
	
}



?>
