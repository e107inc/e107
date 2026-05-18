<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2024 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * Covers e107::isAllowedHost(), the private helper that decides whether
 * `$_SERVER['HTTP_HOST']` is acceptable given the configured siteurl host.
 *
 * Backs the Host-Header Injection killswitch in e107::set_urls_deferred()
 * (GHSA-7pmw-jwvr-cq2x).
 */
class e107HostValidationTest extends \Codeception\Test\Unit
{
	/** @var e107 */
	private $e107;

	/** @var ReflectionMethod */
	private $isAllowedHost;

	protected function _before()
	{
		try
		{
			$this->e107 = e107::getInstance();
		}
		catch(Exception $e)
		{
			self::fail("Couldn't load e107 object");
		}

		$reflection = new ReflectionClass($this->e107);
		$this->isAllowedHost = $reflection->getMethod('isAllowedHost');
		$this->isAllowedHost->setAccessible(true);
	}

	/**
	 * @dataProvider hostMatchProvider
	 */
	public function testIsAllowedHost($allowedHosts, $httpHost, $expected, $scenario)
	{
		$result = $this->isAllowedHost->invoke($this->e107, $allowedHosts, $httpHost);
		$this->assertSame($expected, $result, "Failed scenario: $scenario");
	}

	public function hostMatchProvider()
	{
		return array(
			'exact match'                                    => array('example.com', 'example.com', true, 'exact match'),
			'exact match, www apex'                          => array('www.example.com', 'www.example.com', true, 'exact match, www apex'),
			'subdomain of configured'                        => array('example.com', 'sub.example.com', true, 'subdomain of configured'),
			'apex configured, www visit'                     => array('example.com', 'www.example.com', true, 'apex configured, www visit'),
			'www configured, apex visit'                     => array('www.example.com', 'example.com', true, 'www configured, apex visit'),
			'deep subdomain'                                 => array('example.com', 'a.b.c.example.com', true, 'deep subdomain'),
			'unrelated host'                                 => array('example.com', 'unrelated.org', false, 'unrelated host'),
			'suffix-only collision (no dot boundary)'        => array('example.com', 'fakeexample.com', false, 'suffix-only collision (no dot boundary)'),
			'suffix-only collision with hyphen'              => array('example.com', 'evil-example.com', false, 'suffix-only collision with hyphen'),
			'empty http host'                                => array('example.com', '', false, 'empty http host'),
			'list with match'                                => array(array('a.com', 'b.com'), 'b.com', true, 'list with match'),
			'list without match'                             => array(array('a.com', 'b.com'), 'c.com', false, 'list without match'),
			'list with empty entry then match'               => array(array('', 'example.com'), 'example.com', true, 'list with empty entry then match'),
			'empty allowed list'                             => array(array(), 'example.com', false, 'empty allowed list'),
			'empty allowed string'                           => array('', 'example.com', false, 'empty allowed string'),
			'list with subdomain match'                      => array(array('a.com', 'example.com'), 'sub.example.com', true, 'list with subdomain match'),
			'www visit against bare list entry'              => array(array('example.com'), 'www.example.com', true, 'www visit against bare list entry'),
			'apex visit against www list entry'              => array(array('www.example.com'), 'example.com', true, 'apex visit against www list entry'),
			'port on visited host'                           => array('localhost', 'localhost:8080', true, 'port on visited host'),
			'port on visited host, www'                      => array('example.com', 'www.example.com:8443', true, 'port on visited host, www'),
			'port on visited subdomain'                      => array('example.com', 'sub.example.com:8080', true, 'port on visited subdomain'),
			'port on allowed host'                           => array('example.com:80', 'example.com', true, 'port on allowed host'),
			'port on both sides'                             => array('example.com:80', 'example.com:8080', true, 'port on both sides'),
			'mismatched host with port still rejected'       => array('example.com', 'evil.com:8080', false, 'mismatched host with port still rejected'),
			'case-insensitive exact match'                   => array('Example.COM', 'example.com', true, 'case-insensitive exact match'),
			'case-insensitive www visit'                     => array('example.com', 'WWW.EXAMPLE.COM', true, 'case-insensitive www visit'),
		);
	}
}
