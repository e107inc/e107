<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * System error controller.
 */


/**
 * Class core_system_error_controller.
 */
class core_system_error_controller extends eController
{

	/**
	 * Pre-action callback, fired only if dispatch status is still true
	 * and action method is found.
	 */
	public function preAction()
	{
		e107::coreLan('error');
	}

	/**
	 * Alias for "Error 403".
	 */
	public function action403()
	{
		$this->_forward('forbidden');
	}

	/**
	 * Alias for "Error 404".
	 */
	public function action404()
	{
		$this->_forward('notfound');
	}

	/**
	 * Error 403.
	 */
	public function actionForbidden()
	{
		e107::getError()->render(403);
	}

	/**
	 * Error 404.
	 */
	public function actionNotfound()
	{
		e107::getError()->render(404);
	}

}
