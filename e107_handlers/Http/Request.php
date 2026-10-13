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
 * The parts of an HTTP request that a route and its handler read.
 */
final class Request
{
	/** @var string */
	private $method;

	/** @var string */
	private $queryString;

	/** @var array lower-cased header name => value */
	private $headers = array();

	/**
	 * @param string $method
	 * @param string $queryString
	 * @param array $headers header name => value
	 */
	public function __construct($method, $queryString, array $headers = array())
	{
		$this->method = strtoupper((string) $method);
		$this->queryString = (string) $queryString;

		foreach($headers as $name => $value)
		{
			$this->headers[strtolower((string) $name)] = (string) $value;
		}
	}

	/**
	 * Cookies and credentials are not request headers here; the session layer owns them.
	 *
	 * @param array $server normally $_SERVER
	 * @return Request
	 */
	public static function fromGlobals(array $server)
	{
		$headers = array();
		$excluded = array('HTTP_COOKIE', 'HTTP_AUTHORIZATION');

		foreach($server as $key => $value)
		{
			if(strpos((string) $key, 'HTTP_') === 0 && is_scalar($value) && !in_array($key, $excluded, true))
			{
				$headers[str_replace('_', '-', (string) substr((string) $key, 5))] = $value;
			}
		}

		return new self(
			isset($server['REQUEST_METHOD']) ? $server['REQUEST_METHOD'] : 'GET',
			isset($server['QUERY_STRING']) ? $server['QUERY_STRING'] : '',
			$headers
		);
	}

	/**
	 * @return string upper case
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @return string
	 */
	public function getQueryString()
	{
		return $this->queryString;
	}

	/**
	 * @param string $name case-insensitive
	 * @return string empty when the request did not carry the header
	 */
	public function getHeaderLine($name)
	{
		$name = strtolower((string) $name);

		return isset($this->headers[$name]) ? $this->headers[$name] : '';
	}

	/**
	 * @param string $name
	 * @return bool whether the query string is the bare word $name, alone or followed by parameters of its own
	 */
	public function hasFlag($name)
	{
		$name = (string) $name;

		return $name !== '' && ($this->queryString === $name || strpos($this->queryString, $name.'&') === 0);
	}
}
