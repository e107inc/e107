<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * Class for system error pages.
 */


// [e_LANGUAGEDIR]/[e_LANGUAGE]/lan_error.php
e107::lan('core', 'error');


/**
 * Class error_page.
 */
class error_page
{

	/**
	 * @var
	 */
	private $statusCode;

	/**
	 * Constructor.
	 *
	 * Use {@link getInstance()}, direct instantiating is not possible for
	 * signleton objects.
	 */
	public function __construct()
	{
	}

	/**
	 * Cloning is not allowed.
	 */
	private function __clone()
	{
	}

	/**
	 * @return void
	 */
	protected function _init()
	{
	}

	/**
	 * Delivers a "Bad Request" error page to the browser.
	 */
	public function deliverPageBadRequest()
	{
		header('HTTP/1.1 400 Bad Request', true, 400);

		$title = LAN_ERROR_35; // Error 400 - Bad Request
		$caption = LAN_ERROR_45;
		$content = LAN_ERROR_36 . '<br/>' . LAN_ERROR_3;

		$this->statusCode = 400;
		$this->renderPage($title, $caption, $content);
	}

	/**
	 * Delivers a "Authentication Failed" error page to the browser.
	 */
	public function deliverPageUnauthorized()
	{
		header('HTTP/1.1 401 Unauthorized', true, 401);

		$title = LAN_ERROR_1; // Error 401 - Authentication Failed
		$caption = LAN_ERROR_45;
		$content = LAN_ERROR_2 . '<br/>' . LAN_ERROR_3;

		$this->statusCode = 401;
		$this->renderPage($title, $caption, $content);
	}

	/**
	 * Delivers a "Access forbidden" error page to the browser.
	 */
	public function deliverPageForbidden()
	{
		header('HTTP/1.1 403 Forbidden', true, 403);

		$title = LAN_ERROR_4; // Error 403 - Access forbidden
		$caption = LAN_ERROR_45;
		$content = LAN_ERROR_5 . '<br/>' . LAN_ERROR_6 . '<br/><br/>' . LAN_ERROR_2;

		$this->statusCode = 403;
		$this->renderPage($title, $caption, $content);
	}

	/**
	 * Delivers a "Not Found" error page to the browser.
	 */
	public function deliverPageNotFound()
	{
		header('HTTP/1.1 404 Not Found', true, 404);

		$title = LAN_ERROR_7; // Error 404 - Document Not Found
		$caption = LAN_ERROR_45;
		$content = LAN_ERROR_21 . '<br/>' . LAN_ERROR_9;

		$this->statusCode = 404;
		$this->renderPage($title, $caption, $content);
	}

	/**
	 * Delivers a "Internal server error" error page to the browser.
	 */
	public function deliverPageInternalServerError()
	{
		header('HTTP/1.1 500 Internal Server Error', true, 500);

		$title = LAN_ERROR_10; // Error 500 - Internal server error
		$caption = LAN_ERROR_14;
		$content = LAN_ERROR_11 . '<br/>' . LAN_ERROR_12;

		$this->statusCode = 500;
		$this->renderPage($title, $caption, $content);
	}

	/**
	 * Delivers a "Unknown" error page to the browser.
	 */
	public function deliverPageUnknown()
	{
		header('HTTP/1.1 501 Not Implemented', true, 501);

		$errorQuery = htmlentities($_SERVER['QUERY_STRING']);

		$title = LAN_ERROR_13 . ' (' . $errorQuery . ')'; // Error - Unknown
		$caption = LAN_ERROR_14;
		$content = LAN_ERROR_15;

		$this->statusCode = 'DEFAULT'; // Use default template.
		$this->renderPage($title, $caption, $content);
	}

	/**
	 * Renders and delivers an error page to the browser.
	 *
	 * @param $title
	 *  Page title.
	 * @param $caption
	 *  Title for info panel.
	 * @param $content
	 *  Content for info panel.
	 */
	private function renderPage($title, $caption, $content)
	{
		$tp = e107::getParser();
		$tpl = e107::getCoreTemplate('error', $this->statusCode);
		$sc = e107::getScBatch('error');

		$sc->setVars(array(
			'title'    => LAN_ERROR_TITLE, // Oops!
			'subtitle' => $title,
			'caption'  => $caption,
			'content'  => $content,
		));

		$body = $tp->parseTemplate($tpl, true, $sc);
		e107::getRender()->tablerender('', $body);
	}

}
