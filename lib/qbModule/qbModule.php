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
 * Base class for all of qb's modules.
 */

abstract class qbModule {

	private $moduleAuthor      = null;
	private $moduleDescription = null;
	private $moduleURL         = null;
	private $moduleVersion     = null;

	/**
	 * Return the author of this module.
	 * @return string
	 */
	public function getModuleAuthor() {
		return ($this->moduleAuthor);
	}

	/**
	 * Return the class name of this module.
	 * @return string
	 */
	public function getModuleName() {
		return (get_class($this));
	}

	/**
	 * Return a description of this module.
	 * @todo This should really parse the PHPdoc tag of the class.
	 * @return string
	 */
	public function getModuleDescription() {
		return ($this->moduleDescription);
	}

	/**
	 * Return a URL with more information about this module.
	 * @return string
	 */
	public function getModuleURL() {
		return ($this->moduleURL);
	}

	/**
	 * Return the version number of this module.
	 * @see setModuleVersion()
	 * @return int
	 */
	public function getModuleVersion() {
		return ($this->moduleVersion);
	}

	/**
	 * Set the author of this module.
	 * @param string $author The author's name and his/her e-mail address in
	 *                       angle brackets.
	 * @return string
	 */
	protected function setModuleAuthor($author) {
		assert(is_string($author));
		return ($this->moduleAuthor = $author);
	}

	/**
	 * Set the description of this module.
	 * @todo This information should be parsed from the PHPdoc.
	 * @param string $desc A description.
	 * @return string
	 */
	protected function setModuleDescription($desc) {
		assert(is_string($desc));
		return ($this->moduleDescription = $desc);
	}

	/**
	 * Set a URL with more information about this module.
	 * @param string $url Simply a URL.
	 * @return string
	 */
	protected function setModuleURL($url) {
		assert(is_string($url));
		return ($this->moduleURL = $url);
	}

	/**
	 * Set the version of this module.
	 *
	 * Note that the parameter is an integer. The purpose of all of this is to
	 * enable other modules to do checks like "is module foobar available in
	 * version X or higher?". In order to keep it simple, qb doesn't support
	 * fancy version schemes. If you release a new version of your module,
	 * increase this number by 1 (or more if you like), that's all.
	 *
	 * @param int $ver The version number.
	 * @return int
	 */
	protected function setModuleVersion($ver) {
		assert(is_int($ver));
		return ($this->moduleVersion = $ver);
	}

}

?>
