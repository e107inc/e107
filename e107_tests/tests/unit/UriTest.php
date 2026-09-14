<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Floor coverage for the vendored, downgraded guzzlehttp/psr7 Uri.
 *
 * psr7 2.x has Uri implement \JsonSerializable with a declared return type,
 * and the downgrade strips that type so the file parses under PHP 5.6.
 * Stripping it leaves the method violating the interface's tentative return
 * type, which PHP 8.1 and above report at class-link time unless
 * #[\ReturnTypeWillChange] says the omission was meant. The suite prints a
 * deprecation without failing on it, and parsing the tree under PHP 5.6 has no
 * opinion on an attribute, so the marker is asserted from the source here: a
 * re-vendor rewrites this file and drops the attribute, and this is the only
 * thing that would notice.
 */
class UriTest extends \Codeception\Test\Unit
{
	protected function _before()
	{
		if (!class_exists('GuzzleHttp\Psr7\Uri'))
		{
			$this->markTestSkipped('guzzlehttp/psr7 is not autoloadable');
		}
	}

	public function testAUriSerialisesToItsStringForm()
	{
		$uri = new \GuzzleHttp\Psr7\Uri('http://example.com/x');

		$this->assertSame('http://example.com/x', (string) $uri);
		$this->assertSame('"http:\/\/example.com\/x"', json_encode($uri));
	}

	public function testJsonSerializeKeepsItsReturnTypeMarker()
	{
		$class = new \ReflectionClass('GuzzleHttp\Psr7\Uri');
		$declaration = $this->declarationPreceding($class->getFileName(), 'jsonSerialize');

		$this->assertNotSame('', $declaration, 'jsonSerialize() was not found in ' . $class->getFileName());
		$this->assertStringContainsString('ReturnTypeWillChange', $declaration);
	}

	/**
	 * Source text between the end of the previous member and the named method, attributes included.
	 */
	private function declarationPreceding($path, $methodName)
	{
		$preceding = '';
		foreach (token_get_all(file_get_contents($path)) as $token)
		{
			$text = is_array($token) ? $token[1] : $token;
			if ($text === '}')
			{
				$preceding = '';
			}
			$preceding .= $text;
			if (is_array($token) && $token[0] === T_STRING && $token[1] === $methodName)
			{
				return $preceding;
			}
		}

		return '';
	}
}
