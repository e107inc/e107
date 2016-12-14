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
		$response = $this->getResponse();
		$response->setRenderMod('error403');
		$response->addHeader('HTTP/1.0 403 Forbidden');

		$tp = e107::getParser();
		$tpl = e107::getCoreTemplate('error', '403');
		$sc = e107::getScBatch('error');

		$title = LAN_ERROR_TITLE;
		$subtitle = LAN_ERROR_4;
		$caption = LAN_ERROR_45;
		$content = LAN_ERROR_5 . '<br/>' . LAN_ERROR_6 . '<br/><br/>' . LAN_ERROR_2;

		$sc->setVars(array(
			'title'    => $title,
			'subtitle' => $subtitle,
			'caption'  => $caption,
			'content'  => $content,
		));

		$body = $tp->parseTemplate($tpl, true, $sc);
		$this->addBody($body);
	}

	/**
	 * Error 404.
	 */
	public function actionNotfound()
	{
		$response = $this->getResponse();
		$response->setRenderMod('error404');
		$response->addHeader('HTTP/1.0 404 Not Found');

		$tp = e107::getParser();
		$tpl = e107::getCoreTemplate('error', '404');
		$sc = e107::getScBatch('error');

		$title = LAN_ERROR_TITLE;
		$subtitle = LAN_ERROR_7;
		$caption = LAN_ERROR_45;
		$content = LAN_ERROR_21 . '<br/>' . LAN_ERROR_9;

		$sc->setVars(array(
			'title'    => $title,
			'subtitle' => $subtitle,
			'caption'  => $caption,
			'content'  => $content,
		));

		$body = $tp->parseTemplate($tpl, true, $sc);
		$this->addBody($body);
	}

}
