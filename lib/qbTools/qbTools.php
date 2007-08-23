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
 * Some little tools and helpers.
 */

class qbTools {

	/**
	 * Check if two strings start with the same characters.
	 *
	 * The idea is to check whether the shorter string is fully included at the
	 * beginning of the longer string.
	 *
	 * @param string $a A string.
	 * @param string $b Another string.
	 * @return bool Whether the strings start with the same characters.
	 */
	static function sameStart($a, $b) {
		assert(is_string($a));
		assert(is_string($b));
		if (strlen($a) > strlen($b))
			return (substr($a, 0, strlen($b)) === $b);
		else
			return (substr($b, 0, strlen($a)) === $a);
	}

}

?>
