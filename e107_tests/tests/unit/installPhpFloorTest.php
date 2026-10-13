<?php
/**
 * Fails on any PHP the suite runs on that install.php would refuse to install e107 on.
 */
class installPhpFloorTest extends \Test\Unit
{
	public function testTheInstallerAcceptsThisPhp()
	{
		$floor = $this->installerFloor(file_get_contents(APP_PATH . '/install.php'));

		$this->assertNotNull($floor, 'install.php no longer defines MIN_PHP_VERSION.');
		$this->assertTrue(version_compare(PHP_VERSION, $floor, '>='),
			'install.php refuses PHP ' . PHP_VERSION . ' because its MIN_PHP_VERSION is ' . $floor . ', yet the shipped tree is built and tested for it.');
	}

	/**
	 * @dataProvider sources
	 * @param string $source
	 * @param string|null $expected
	 */
	public function testTheFloorIsReadFromTokensNotText($source, $expected)
	{
		$this->assertSame($expected, $this->installerFloor("<?php\n" . $source));
	}

	public function sources()
	{
		return array(
			'single quotes' => array("define('MIN_PHP_VERSION', '5.6');", '5.6'),
			'double quotes and spacing' => array("define ( \"MIN_PHP_VERSION\" ,\t\"7.4\" );", '7.4'),
			'another constant first' => array("define('MIN_MYSQL_VERSION', '4.1.2');\ndefine('MIN_PHP_VERSION', '8.0');", '8.0'),
			'a define in a comment' => array("// define('MIN_PHP_VERSION', '9.9');\ndefine('MIN_PHP_VERSION', '5.6');", '5.6'),
			'a define in a string' => array("\$s = \"define('MIN_PHP_VERSION', '9.9');\";", null),
			'no define' => array("echo MIN_PHP_VERSION;", null),
		);
	}

	/**
	 * @param string $source
	 * @return string|null the version the source defines MIN_PHP_VERSION as
	 */
	private function installerFloor($source)
	{
		$tokens = token_get_all($source);

		foreach ($tokens as $i => $token)
		{
			if (\Test\Tokens::id($token) !== T_STRING || strcasecmp($token[1], 'define') !== 0)
			{
				continue;
			}

			$call = array();

			for ($j = $i; count($call) < 4 && ($j = \Test\Tokens::neighbourIndex($tokens, $j, 1)) !== null;)
			{
				$call[] = $tokens[$j];
			}

			if (count($call) === 4 && $call[0] === '(' && $call[2] === ','
				&& \Test\Tokens::id($call[1]) === T_CONSTANT_ENCAPSED_STRING && (string) substr($call[1][1], 1, -1) === 'MIN_PHP_VERSION'
				&& \Test\Tokens::id($call[3]) === T_CONSTANT_ENCAPSED_STRING)
			{
				return (string) substr($call[3][1], 1, -1);
			}
		}

		return null;
	}
}
