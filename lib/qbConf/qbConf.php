<?php

// Copyright (c) 2007 Tim Weber <scy-proj-qb@scytale.de>
// This file is part of qb <http://scytale.de/proj/qb/>, which in turn is free
// software licensed under the GNU General Public License.
// Please see the file "qb.php" or "LICENSE" for the complete terms.

/** Manage the configuration file.
 */
class qbConf {

	/** Holds the global settings. */
	private static $set = null;

	/** Holds the base reference for this instance. */
	private $ref = '';

	/** Constructor with optional base reference for settings. */
	public function __construct($ref = '') {
		assert(is_string($ref));
		$this->ref = $ref;
		// If $set is not yet initialized, do that.
		if (self::$set === null) {
			self::$set = array();
		}
		// Settings should now be initialized.
		assert(is_array(self::$set));
	}

	/** Load the configuration file. */
	public function initialize() {
		include_once('lib/config.php');
	}

	/** Retrieve a setting.
	 *  TODO: Right now, always returns the default.
	 */
	public function get($setting, $default = null) {
		return ($default);
	}

	/** Change a setting.
	 *  TODO: This function is not yet finished.
	 */
	public function set($alpha, $beta = null) {
		// If a setting is given as set('foo.bar', 123), transform that into
		// "canonical" array form.
		if (is_string($alpha)) {
			$alpha = array($alpha => $beta);
		}
		// $alpha should now really be an array.
		assert(is_array($alpha));
		foreach ($alpha as $k => $v) {
			$path = explode($k, '.');
			for ($i = count($path); $i > 1; $i--) {
				$v = array($path[$i] => $v);
			}

		}
	}

}

?>
