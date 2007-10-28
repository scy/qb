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

	/** Stores the URL base path. */
	protected static $URLBase = null;

	/**
	 * Find the common base of URL and current directory.
	 */
	protected static function matchPaths() {
		$cwd = trim(QB_REQDIR, ' /');
		$url = '/' . trim(QB_URIPATH, ' /');
		var_dump($cwd); var_dump($url);
		while (true) {
			// Get position of last slash.
			$cwdslash = strrpos($cwd, '/');
			$urlslash = strrpos($url, '/');
			// Break if there's no slash left in any.
			if (($cwdslash === false) || ($urlslash === false))
				break;
			// Break if the last parts don't match.
			if (substr($cwd, $cwdslash) != substr($url, $urlslash))
				break;
			// Cut off the last part.
			$cwd = substr($cwd, 0, $cwdslash);
			$url = substr($url, 0, $urlslash);
		}
		return (array($cwd, $url));
	}

	/**
	 * Get the base path of the request URL.
	 */
	public static function getURLBase() {
		// If not yet analyzed or set, analyze it.
		if (self::$URLBase === null) {
			list(, self::$URLBase) = self::matchPaths();
		}
		return (self::$URLBase);
	}

}



?>
