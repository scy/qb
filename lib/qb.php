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
function __autoload($class) {
	// Remove suspicious characters.
	$class = preg_replace('/[^a-zA-Z0-9]/', '', $class);
	// Include the class, die if that fails.
	require_once("lib/$class.php");
	return (true);
}

?>
