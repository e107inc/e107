<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\User;

use e107\Http\Request;
use e107\Http\RequestHandlerInterface;
use e107\Http\Response;
use e107\Theme\Page;

/**
 * Asks a member who followed a logout link with no e-token whether to log out, instead of refusing.
 */
final class LogoutConfirmation implements RequestHandlerInterface
{
	/** The query-string word that asks for a logout, as in index.php?logout. */
	const FLAG = 'logout';

	/** @var bool */
	private $isMember;

	/** @var array */
	private $template;

	/** @var string */
	private $logoutUrl;

	/** @var string */
	private $token;

	/** @var string */
	private $homeUrl;

	/** @var callable */
	private $isOnSite;

	/** @var callable */
	private $parse;

	/** @var callable */
	private $frame;

	/**
	 * @param bool $isMember whether anyone is signed in
	 * @param array $template the 'confirm' entry of logout_template.php: 'caption' and 'body'
	 * @param string $logoutUrl the script that ends the session, without a query string
	 * @param string $token the session's e-token
	 * @param string $homeUrl
	 * @param callable $isOnSite given a URL, whether it stays on this site's trusted hosts
	 * @param callable $parse given template markup and an array of variables, returns HTML
	 * @param callable $frame given a caption, HTML and a render mode, returns the framed HTML
	 */
	public function __construct($isMember, array $template, $logoutUrl, $token, $homeUrl, callable $isOnSite, callable $parse, callable $frame)
	{
		$this->isMember = (bool) $isMember;
		$this->template = $template + array('caption' => '', 'body' => '');
		$this->logoutUrl = (string) $logoutUrl;
		$this->token = (string) $token;
		$this->homeUrl = (string) $homeUrl;
		$this->isOnSite = $isOnSite;
		$this->parse = $parse;
		$this->frame = $frame;
	}

	/**
	 * @param Request $request
	 * @return Response
	 */
	public function handle(Request $request)
	{
		if(!$this->isMember)
		{
			return Response::redirect($this->homeUrl);
		}

		$cancelUrl = $this->cancelUrl($request->getHeaderLine('Referer'));

		return Response::page(new Page($this->template['caption'], function () use ($cancelUrl)
		{
			return $this->render($cancelUrl);
		}));
	}

	/**
	 * @param string $cancelUrl
	 * @return string
	 */
	private function render($cancelUrl)
	{
		$body = call_user_func($this->parse, $this->template['body'], array(
			'LOGOUT_CANCEL_URL' => htmlspecialchars(str_replace(array('{', '}'), array('%7B', '%7D'), $cancelUrl), ENT_QUOTES, 'UTF-8'),
		));

		$form = "<form id='logout-confirm' method='post' action='".$this->action()."'>".$body."</form>";

		return call_user_func($this->frame, $this->template['caption'], $form, 'logout');
	}

	/**
	 * @return string
	 */
	private function action()
	{
		return htmlspecialchars($this->logoutUrl.'?'.self::FLAG.'&e-token='.rawurlencode($this->token), ENT_QUOTES, 'UTF-8');
	}

	/**
	 * @param string $referrer
	 * @return string
	 */
	private function cancelUrl($referrer)
	{
		if($referrer === '' || !call_user_func($this->isOnSite, $referrer))
		{
			return $this->homeUrl;
		}

		$query = parse_url($referrer, PHP_URL_QUERY);
		$leadsBackHere = is_string($query) && (new Request('GET', $query))->hasFlag(self::FLAG);

		return $leadsBackHere ? $this->homeUrl : $referrer;
	}
}
