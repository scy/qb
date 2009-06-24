<?php

/* Copyright 2007 Tim Weber <scy-proj-qb@scytale.name>

   This file is part of qb <http://scytale.name/proj/qb/>.

   See the LICENSE file for legal stuff.
   */



/**
 * Helper class for URL parsing, path mapping and things like that.
 */
class qbURL {

	/** Stores the base directory. */
	protected static $baseDir = null;
	
	/** Stores the handler URL path. */
	protected static $handler = null;

	/** Stores the base URL path. */
	protected static $basePath = null;
	
	/** Return the base directory.
	 *
	 * The base directory is the physical, topmost directory we are allowed
	 * to serve files from. This is usually the directory where the calling
	 * script is installed.
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
	 * Automatically translates links and strips trailing slashes.
	 */
	public static function setBaseDir($dir) {
		// Resolve the supplied directory.
		$base = realpath($dir);
		// If it doesn't exist, throw exception.
		if ($base === false)
			throw new qbFileNotFoundException($dir);
		// Set and return it.
		return (self::$baseDir = $base);
	}
	
	/**
	 * Return the handler's URL path.
	 *
	 * Starts with a slash, but does not end with one.
	 * The handler path is often the same as the base path: If you are using
	 * .htaccess magic to hide the actual filename of the calling script. On
	 * some servers or in some situations this is not possible or desired, and
	 * that's when you use URLs like /blog/qb.php/2008/01/some-article (note
	 * the "qb.php" in that path. The path including the .php file name is the
	 * handler then ("/blog/qb.php"), while the base path would end after the
	 * including directory, in this case "/blog".
	 */
	public static function getHandler() {
		// If none is defined, try to figure it out automatically.
		if (self::$handler === null) {
			// SCRIPT_NAME containes the alias name if aliased from outside, and
			// the virtual filename if not.
			$base = $_SERVER['SCRIPT_NAME'];
			// If there's a dot in the basename, it's most likely a PHP script:
			if (strpos(basename($base), '.') !== false) {
				// If we have been called without that script name, assume that
				// it's not mandatory.
				if (!qbString::startsWith($_SERVER['SCRIPT_NAME'], QB_URIPATH, false))
					$base = dirname($base);
			}
			return (self::setHandler($base));
		}
		return (self::$handler);
	}
	
	/**
	 * Set the handler's URL path.
	 *
	 * Automatically adds a leading slash and removes trailing ones.
	 */
	public static function setHandler($path) {
		// If set to false or null, use auto-detection.
		if (($path === false) || ($path === null)) {
			self::$handler = null;
			return (self::getHandler());
		}
		assert(is_string($path));
		return (self::$handler = '/' . trim($path, '/'));
	}
	
	/**
	 * Return the URL base path.
	 *
	 * Starts with a slash, but does not end with one.
	 * The URL base is the part of the request path that will always be there,
	 * for "real" files (like CSS) as well as virtual, files. qb might not be
	 * used to manage a whole host, but only a sub directory, and this is its
	 * name.
	 */
	public static function getBasePath() {
		// If none is defined, try to derive it from the handler.
		if (self::$basePath === null) {
			$base = self::getHandler();
			// If there's a dot in its basename, it's most likely a PHP script:
			if (strpos(basename($base), '.') !== false) {
				// Remove it then.
				$base = dirname($base);
			}
			return (self::setBasePath($base));
		}
		return (self::$basePath);
	}
	
	/**
	 * Set the URL base path.
	 *
	 * Automatically adds a leading slash and removes trailing ones.
	 */
	public static function setBasePath($path) {
		// If set to false or null, use auto-detection.
		if (($path === false) || ($path === null)) {
			self::$basePath = null;
			return (self::getBasePath());
		}
		assert(is_string($path));
		return (self::$basePath = '/' . trim($path, '/'));
	}
	
	/**
	 * Return the virtual filename.
	 *
	 * Starts with a slash, but does not end with one.
	 * This is the virtual file the client requested, ie. if qb manages the
	 * /blog/ directory and the URL http://example.com/blog/foo/bar is called,
	 * the virtual filename is /foo/bar.
	 */
	public static function getVFile() {
		$path = QB_URIPATH;
		// PATH_INFO is set for Apache's "Alias" directive, it has precedence.
		if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] != '')
			$path = $_SERVER['PATH_INFO'];
		else if (qbString::startsWith(self::getHandler(), $path))
			$path = substr($path, strlen(self::getHandler()));
		// Cut off the query string, normalize slashes, be done!
		return ('/' . trim(preg_replace('/\?.+$/', '', $path), '/'));
	}
	
}



?>
