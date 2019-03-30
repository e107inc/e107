<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * System index controller
 *
 * $URL$
 * $Id$
*/
class core_system_index_controller extends eController
{
	/**
	 * Redirect to site Index
	 */
	public function actionIndex()
	{
		$this->_redirect('/', false, 301);
	}
}
