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
 * A handler that answers with a page whose body is one attribute of the request.
 */
final class AttributeEchoHandler implements RequestHandlerInterface
{
	/** @var string */
	private $name;

	/**
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

	public function handle(Request $request)
	{
		$value = (string) $request->getAttribute($this->name);

		return Response::page(new Page('', function () use ($value) { return $value; }));
	}
}
