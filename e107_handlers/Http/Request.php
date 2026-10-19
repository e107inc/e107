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
 * The parts of an HTTP request core reads; it grows only by immutable with*() methods named as in PSR-7, never by constructor arguments.
 */
final class Request
{
	use HeaderLinesTrait;

	/** @var string */
	private $method;

	/** @var string */
	private $queryString;

	/** @var array name => value */
	private $attributes = array();

	/**
	 * @param string $method
	 * @param string $queryString
	 * @param array $headers header name => value
	 */
	public function __construct($method, $queryString, array $headers = array())
	{
		$this->method = strtoupper((string) $method);
		$this->queryString = (string) $queryString;
		$this->storeHeaders($headers);
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
		$excluded = array('HTTP_COOKIE', 'HTTP_AUTHORIZATION', 'HTTP_PROXY_AUTHORIZATION');
		$unprefixed = array('CONTENT_TYPE', 'CONTENT_LENGTH');

		foreach($server as $key => $value)
		{
			$key = (string) $key;
			$name = preg_replace('/^HTTP_/', '', $key, 1, $prefixed);

			if(($prefixed || in_array($key, $unprefixed, true)) && is_scalar($value) && !in_array($key, $excluded, true))
			{
				$headers[str_replace('_', '-', $name)] = $value;
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
	 * @param string $word
	 * @return bool whether the query string is the bare word $word, alone or followed by parameters of its own
	 */
	public function hasLeadingQueryWord($word)
	{
		$word = (string) $word;

		return $word !== '' && ($this->queryString === $word || strpos($this->queryString, $word.'&') === 0);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return Request a copy carrying the attribute
	 */
	public function withAttribute($name, $value)
	{
		$request = clone $this;
		$request->attributes[(string) $name] = $value;

		return $request;
	}

	/**
	 * @param string $name
	 * @param mixed $default
	 * @return mixed what the route captured under $name, or $default
	 */
	public function getAttribute($name, $default = null)
	{
		$name = (string) $name;

		return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
	}
}
