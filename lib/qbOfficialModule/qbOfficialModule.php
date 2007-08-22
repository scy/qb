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
 * Abstract base class for qb modules in the official distribution.
 *
 * Currently this does nothing but set the author to Tim and the URL to the
 * qb website.
 */

abstract class qbOfficialModule extends qbModule {

	public function __construct() {
		parent::__construct();
		$this->setModuleURL('http://scytale.name/proj/qb/');
		$this->setModuleAuthor('Tim Weber <scy-proj-qb@scytale.name>');
	}

}

?>
