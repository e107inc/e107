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
	private $template = 'DEFAULT';

	/**
	 * @var
	 */
	private $title;

	/**
	 * @var
	 */
	private $caption;

	/**
	 * @var
	 */
	private $content;

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
	 * Singleton is not required, we go for factory instead.
	 *
	 * @return error_page
	 */
	public static function getInstance()
	{
		return e107::getError();
	}

	/**
	 * Change title on the error page.
	 *
	 * @param $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * Change panel caption on the error page.
	 *
	 * @param $caption
	 */
	public function setCaption($caption)
	{
		$this->caption = $caption;
	}

	/**
	 * Change panel content on the error page.
	 *
	 * @param $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}

	/**
	 * Set a "Bad Request" error page.
	 */
	private function setPageBadRequest()
	{
		header('HTTP/1.1 400 Bad Request', true, 400);

		$this->template = 400;
		$this->title = LAN_ERROR_35;
		$this->caption = LAN_ERROR_45;
		$this->content = LAN_ERROR_36;
	}

	/**
	 * Set a "Authentication Failed" error page.
	 */
	private function setPageUnauthorized()
	{
		header('HTTP/1.1 401 Unauthorized', true, 401);

		$this->template = 401;
		$this->title = LAN_ERROR_1;
		$this->caption = LAN_ERROR_45;
		$this->content = LAN_ERROR_2 . '<br/>' . LAN_ERROR_3;
	}

	/**
	 * Set a "Access forbidden" error page.
	 */
	private function setPageForbidden()
	{
		header('HTTP/1.1 403 Forbidden', true, 403);

		$this->template = 403;
		$this->title = LAN_ERROR_4;
		$this->caption = LAN_ERROR_45;
		$this->content = LAN_ERROR_5 . '<br/>' . LAN_ERROR_6 . '<br/><br/>' . LAN_ERROR_2;
	}

	/**
	 * Set a "Not Found" error page.
	 */
	private function setPageNotFound()
	{
		header('HTTP/1.1 404 Not Found', true, 404);

		$this->template = 404;
		$this->title = LAN_ERROR_7;
		$this->caption = LAN_ERROR_45;
		$this->content = LAN_ERROR_21 . '<br/>' . LAN_ERROR_9;
	}

	/**
	 * Set a "Internal server error" error page.
	 */
	private function setPageInternalServerError()
	{
		header('HTTP/1.1 500 Internal Server Error', true, 500);

		$this->template = 500;
		$this->title = LAN_ERROR_10;
		$this->caption = LAN_ERROR_14;
		$this->content = LAN_ERROR_11 . '<br/>' . LAN_ERROR_12;
	}

	/**
	 * Set a "Unknown" error page.
	 */
	private function setPageUnknown()
	{
		header('HTTP/1.1 501 Not Implemented', true, 501);

		$errorQuery = htmlentities($_SERVER['QUERY_STRING']);

		$this->template = 'DEFAULT';
		$this->title = LAN_ERROR_13 . ' (' . $errorQuery . ')';
		$this->caption = LAN_ERROR_14;
		$this->content = LAN_ERROR_15;
	}

	/**
	 * Set error page.
	 *
	 * @param int $status_code
	 *  The HTTP status code to use for the error page, defaults to 404.
	 *  Status codes are defined in RFC 2616.
	 * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
	 */
	public function set($status_code = 404)
	{
		switch($status_code)
		{
			case 400:
				$this->setPageBadRequest();
				break;

			case 401:
				$this->setPageUnauthorized();
				break;

			case 403:
				$this->setPageForbidden();
				break;

			case 404:
				$this->setPageNotFound();
				break;

			case 500:
				$this->setPageInternalServerError();
				break;

			default:
				$this->setPageUnknown();
				break;
		}
	}

	/**
	 * Renders and delivers an error page to the browser.
	 *
	 * @param int $status_code
	 *  The HTTP status code to use for the error page, defaults to 404.
	 *  Status codes are defined in RFC 2616.
	 * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
	 */
	public function render($status_code = null)
	{
		if(!defined('ERR_PAGE_ACTIVE'))
		{
			define("ERR_PAGE_ACTIVE", true);
		}

		if($status_code)
		{
			$this->set($status_code);
		}

		$tp = e107::getParser();
		$tpl = e107::getCoreTemplate('error', $this->template);
		$sc = e107::getScBatch('error');

		$sc->setVars(array(
			'title'    => LAN_ERROR_TITLE, // Oops!
			'subtitle' => $this->title,
			'caption'  => $this->caption,
			'content'  => $this->content,
		));

		$body = $tp->parseTemplate($tpl, true, $sc);
		e107::getRender()->tablerender('', $body, 'error_page_'.$status_code);
	}

}
