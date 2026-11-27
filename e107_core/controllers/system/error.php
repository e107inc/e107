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
		$this->answerWithRoute('system/error/forbidden');
	}

	/**
	 * Error 404.
	 */
	public function actionNotfound()
	{
		$this->answerWithRoute('system/error/notfound');
	}

	/**
	 * Error 500.
	 *
	 * @deprecated v2.4.0 Avoid in new code and migrate existing call sites when refactoring: the page is the route system/error/servererror, which {@see \e107\Routing\RouteTable::named()} takes from the table e107_core/route_table.php returns; this action remains supported and tested, with no removal planned.
	 */
	public function actionServererror()
	{
		$this->answerWithRoute('system/error/servererror');
	}

	/**
	 * @param string $name a route of the core route table that answers with a themed page
	 * @return void
	 */
	private function answerWithRoute($name)
	{
		$routeTable = require(e_CORE.'route_table.php');
		$answer = $routeTable->named($name)->createHandler()->handle(\e107\Http\Request::fromGlobals($_SERVER));
		$page = $answer->getPage();

		http_response_code($answer->getStatusCode());
		$this->getResponse()->setTitle($page->getCaption())->setRenderMod($page->getMode())->setBody($page->render());
	}

}
