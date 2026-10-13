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
	/** @var int */
	private $statusCode;

	/** @var Page|null */
	private $page;

	/** @var string|null */
	private $location;

	/**
	 * @param int $statusCode
	 * @param Page|null $page
	 * @param string|null $location
	 */
	private function __construct($statusCode, $page, $location)
	{
		$statusCode = (int) $statusCode;

		if($statusCode < 100 || $statusCode > 599)
		{
			throw new \InvalidArgumentException('An HTTP status code is between 100 and 599, not '.$statusCode.'.');
		}

		$this->statusCode = $statusCode;
		$this->page = $page;
		$this->location = $location;
	}

	/**
	 * @param Page $page
	 * @param int $statusCode
	 * @return Response
	 */
	public static function page(Page $page, $statusCode = 200)
	{
		return new self($statusCode, $page, null);
	}

	/**
	 * @param string $url
	 * @param int $statusCode
	 * @return Response
	 */
	public static function redirect($url, $statusCode = 303)
	{
		return new self($statusCode, null, (string) $url);
	}

	/**
	 * @return int
	 */
	public function getStatusCode()
	{
		return $this->statusCode;
	}

	/**
	 * @return Page|null null for a redirect
	 */
	public function getPage()
	{
		return $this->page;
	}

	/**
	 * @return string|null null for a page
	 */
	public function getLocation()
	{
		return $this->location;
	}
}
