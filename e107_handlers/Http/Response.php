<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Http;

use e107\Theme\Page;

/**
 * What a handler answers with: a themed page, or a redirect.
 */
final class Response
{
	use HeaderLinesTrait;

	/** @var int */
	private $statusCode;

	/** @var Page|null */
	private $page;

	/**
	 * @param int $statusCode
	 * @param Page|null $page
	 * @param array $headers header name => value
	 */
	private function __construct($statusCode, $page, array $headers)
	{
		$statusCode = (int) $statusCode;

		if($statusCode < 100 || $statusCode > 599)
		{
			throw new \InvalidArgumentException('An HTTP status code is between 100 and 599, not '.$statusCode.'.');
		}

		$this->statusCode = $statusCode;
		$this->page = $page;
		$this->storeHeaders($headers);
	}

	/**
	 * @param Page $page
	 * @param int $statusCode any status that carries a body and is not a redirect
	 * @return Response
	 */
	public static function page(Page $page, $statusCode = 200)
	{
		$response = new self($statusCode, $page, array());
		$bodiless = $response->statusCode < 200 || in_array($response->statusCode, array(204, 205, 304), true);

		if($bodiless || $response->isRedirect())
		{
			throw new \InvalidArgumentException('A themed page cannot be sent with the status '.$response->statusCode.'.');
		}

		return $response;
	}

	/**
	 * A redirect within this site; {@see redirection::go()} sends an off-site URL to the front page instead.
	 *
	 * @param string $url not empty
	 * @param int $statusCode 301, 302, 303, 307 or 308
	 * @return Response
	 */
	public static function redirect($url, $statusCode = 303)
	{
		$response = new self($statusCode, null, array('Location' => $url));

		if(!$response->isRedirect())
		{
			throw new \InvalidArgumentException('The status '.$response->statusCode.' is not a redirect.');
		}

		if($response->getHeaderLine('Location') === '')
		{
			throw new \InvalidArgumentException('A redirect needs a URL.');
		}

		return $response;
	}

	/**
	 * @return int
	 */
	public function getStatusCode()
	{
		return $this->statusCode;
	}

	/**
	 * @return bool whether the status is 301, 302, 303, 307 or 308
	 */
	public function isRedirect()
	{
		return in_array($this->statusCode, array(301, 302, 303, 307, 308), true);
	}

	/**
	 * @return Page|null null when the response is not a themed page
	 */
	public function getPage()
	{
		return $this->page;
	}
}
