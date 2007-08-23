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
 * Parses the URL and contains information about the current request.
 */

class qbRequest extends qbOfficialModule {

	private static $instance  = null;
	private static $construct = false;

	private $rootpath = null;
	private $path     = null;

	/**
	 * This is kind of a singleton, please use {@link getInstance()} instead.
	 *
	 * However, since {@link qbOfficialModule::__construct() qbOfficialModule's
	 * constructor} is public, we can't set this one private, so it will throw a
	 * {@link qbSingletonException} if called directly.
	 */
	public function __construct() {
		// Throw an exception if not called via getInstance().
		if (!self::$construct)
			throw new qbSingletonException();
		phpinfo();
		// Set $rootpath to where we are apparently installed in.
		$this->rootpath = dirname($_SERVER['SCRIPT_NAME']);
		// If this doesn't end in a slash, make it so.
		if (substr($this->rootpath, -1) !== '/')
			$this->rootpath .= '/';
		// Does the request URL start with the same string? If not, we're in a
		// strange server setup. TODO: Build one and find out what to do.
		if (!qbTools::sameStart($this->rootpath, $_SERVER['REQUEST_URI']))
			throw new qbNotImplementedException(
				'SCRIPT_NAME and REQUEST_URI do not start the same');
		// Find out what we're actually supposed to display (ie. remove the root
		// path and leading and trailing slashes from anything that's left.
		$this->path = trim(substr($_SERVER['REQUEST_URI'], strlen($this->rootpath)), '/');
		var_dump($this->path);
	}

	/**
	 * Returns the single instance of this object.
	 * @return qbRequest
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			// Unlock the constructor, construct, lock again. Hackish, I know.
			self::$construct = true;
			self::$instance = new qbRequest();
			self::$construct = false;
		}
		return (self::$instance);
	}

}

?>
