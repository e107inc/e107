<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * System error pages.
 */

define("ERR_PAGE_ACTIVE", 'error');

//We need minimal mod.
$_E107 = array(
	'no_forceuserupdate',
	'no_online',
	'no_prunetmp',
);

require_once("class2.php");

// Start session if required.
if(!session_id())
{
	session_start();
}

// Include language file.
e107::coreLan('error');


/**
 * Class error_front.
 */
class error_front
{

	/**
	 * @var
	 */
	private $errorNumber;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		if(is_numeric(e_QUERY))
		{
			$this->errorNumber = intval(e_QUERY);
		}

		$this->renderErrorPage();
	}

	/**
	 * Renders the error page.
	 */
	public function renderErrorPage()
	{
		switch($this->errorNumber)
		{
			case 400:
				header('HTTP/1.1 400 Bad Request');

				$subtitle = LAN_ERROR_35; // Error 400 - Bad Request
				$caption = LAN_ERROR_45;
				$content = LAN_ERROR_36 . '<br/>' . LAN_ERROR_3;
				break;

			case 401:
				header('HTTP/1.1 401 Unauthorized');

				$subtitle = LAN_ERROR_1; // Error 401 - Authentication Failed
				$caption = LAN_ERROR_45;
				$content = LAN_ERROR_2 . '<br/>' . LAN_ERROR_3;
				break;

			case 403:
				header('HTTP/1.1 403 Forbidden');

				$subtitle = LAN_ERROR_4; // Error 403 - Access forbidden
				$caption = LAN_ERROR_45;
				$content = LAN_ERROR_5 . '<br/>' . LAN_ERROR_6 . '<br/><br/>' . LAN_ERROR_2;
				break;

			case 404:
				header('HTTP/1.1 404 Not Found');

				$subtitle = LAN_ERROR_7; // Error 404 - Document Not Found
				$caption = LAN_ERROR_45;
				$content = LAN_ERROR_21 . '<br/>' . LAN_ERROR_9;

				$errFrom = isset($_SESSION['e107_http_referer']) ? $_SESSION['e107_http_referer'] : $_SERVER['HTTP_REFERER'];

				if(strlen($errFrom))
				{
					$content .= '<br/>';
					$content .= '<br/>';
					$content .= LAN_ERROR_23 . ' <a href="' . $errFrom . '" rel="external">' . $errFrom . '</a> ';
					$content .= LAN_ERROR_24;
				}

				break;

			case 500:
				header('HTTP/1.1 500 Internal Server Error');

				$subtitle = LAN_ERROR_10; // Error 500 - Internal server error
				$caption = LAN_ERROR_14;
				$content = LAN_ERROR_11 . '<br/>' . LAN_ERROR_12;
				break;

			case 999:
				if(!defset('E107_DEBUG_LEVEL', false))
				{
					e107::redirect();
				}

				$this->errorNumber = 'DEFAULT'; // Use default template.

				$subtitle = LAN_ERROR_33;
				$caption = LAN_ERROR_14;
				$content = '<pre>' . print_r($_SERVER) . print_r($_REQUEST) . '</pre>';
				break;

			default:
				$this->errorNumber = 'DEFAULT'; // Use default template.
				$errorQuery = htmlentities($_SERVER['QUERY_STRING']);

				$subtitle = LAN_ERROR_13 . ' (' . $errorQuery . ')'; // Error - Unknown
				$caption = LAN_ERROR_14;
				$content = LAN_ERROR_15;
				break;
		}

		$tp = e107::getParser();
		$tpl = e107::getCoreTemplate('error', $this->errorNumber);
		$sc = e107::getScBatch('error');

		$sc->setVars(array(
			'title'    => LAN_ERROR_TITLE,
			'subtitle' => $subtitle,
			'caption'  => $caption,
			'content'  => $content,
		));

		$body = $tp->parseTemplate($tpl, true, $sc);
		e107::getRender()->tablerender('', $body);
	}

}


require_once(HEADERF);
new error_front();
require_once(FOOTERF);
