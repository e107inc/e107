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

/**
 * The header store {@see Request} and {@see Response} share, keyed by lower-cased name.
 *
 * @internal Only Request and Response use it; it changes as they need.
 */
trait HeaderLinesTrait
{
	/** @var array lower-cased header name => value */
	private $headers = array();

	/**
	 * @param string $name case-insensitive
	 * @return string empty when the message does not carry the header
	 */
	public function getHeaderLine($name)
	{
		$name = strtolower((string) $name);

		return isset($this->headers[$name]) ? $this->headers[$name] : '';
	}

	/**
	 * @param array $headers header name => value
	 */
	private function storeHeaders(array $headers)
	{
		foreach($headers as $name => $value)
		{
			$this->headers[strtolower((string) $name)] = (string) $value;
		}
	}
}
