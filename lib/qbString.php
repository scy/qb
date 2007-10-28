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
 * qb's string helper functions.
 */
class qbString {

	/**
	 * Return true if both strings start with the same characters.
	 */
	public static function sameStart($a, $b) {
		// $a should be the shorter one. If it isn't, make it.
		if (strlen($a) > strlen($b)) {
			$tmp = $b;
			$b = $a;
			$a = $tmp;
		}
		return (substr($b, 0, strlen($a)) === $a);
	}
	
}



?>
