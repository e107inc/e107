<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\System;

use e107\Http\Request;
use e107\Http\RequestHandlerInterface;
use e107\Http\Response;
use e107\Theme\Page;

/**
 * Answers every request with the themed page for one HTTP error status, in the error_page_<status> table style mode.
 */
final class ErrorPage implements RequestHandlerInterface
{
	/** @var int */
	private $statusCode;

	/** @var string */
	private $caption;

	/** @var callable */
	private $body;

	/**
	 * @param int $statusCode 400 to 599
	 * @param string $caption the page's title and the caption of its box
	 * @param callable $body returns the body's HTML for the status code it is passed
	 */
	public function __construct($statusCode, $caption, callable $body)
	{
		$this->statusCode = (int) $statusCode;
		$this->caption = (string) $caption;
		$this->body = $body;

		if($this->statusCode < 400 || $this->statusCode > 599)
		{
			throw new \InvalidArgumentException('An error page answers with a status between 400 and 599, not '.$this->statusCode.'.');
		}
	}

	public function handle(Request $request)
	{
		$body = $this->body;
		$statusCode = $this->statusCode;
		$page = new Page($this->caption, function () use ($body, $statusCode) { return call_user_func($body, $statusCode); });

		return Response::page($page->withCaption($this->caption, 'error_page_'.$statusCode), $statusCode);
	}
}
