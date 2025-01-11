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
define('e_TOKEN_DISABLE', true);
require_once("class2.php");


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

		e107::getRender()->tablerender(LAN_ERROR,$this->renderErrorPage(), 'error_page_'.$this->errorNumber);
	}

	/**
	 * Renders the error page.
	 */
	public function renderErrorPage()
	{
		switch($this->errorNumber)
		{
			case 400:
				$body = e107::getError()->render(400);
				break;

			case 401:
				$body = e107::getError()->render(401);
				break;

			case 403:
				$body = e107::getError()->render(403);
				break;

			case 404:
				$body = e107::getError()->render(404);
				break;

			case 500:
				$body = e107::getError()->render(500);
				break;

			default:
				$body = e107::getError()->render('unknown');
				break;
		}

		return $body;
	}

}


require_once(HEADERF);
new error_front();
require_once(FOOTERF);
