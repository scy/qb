<?php

/* Copyright 2007 Tim Weber <scy-proj-qb@scytale.name>

   This file is part of qb <http://scytale.name/proj/qb/>.

   See the LICENSE file for legal stuff.
   */



/**
 * qb's string helper functions.
 */
class qbString {

	/**
	 * Return true if string $b starts with string $a.
	 */
	public static function startsWith($a, $b, $flip = true) {
		// $a should be the shorter one. If it isn't, make it.
		if ($flip && (strlen($a) > strlen($b)))
			list($a, $b) = array($b, $a);
		return (substr($b, 0, strlen($a)) === $a);
	}
	
}



?>
