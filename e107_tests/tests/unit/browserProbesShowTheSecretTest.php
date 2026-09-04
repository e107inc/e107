<?php
/**
 * Fails the suite on a browser-driven fixture that boots e107 and is loaded without the run's secret.
 */
class browserProbesShowTheSecretTest extends \Test\Unit
{
	const SUITE = 'tests/webdriver';

	public function testEveryGuardedWebdriverProbeIsLoadedWithTheSecret()
	{
		$findings = array();

		foreach (\Test\Tree::phpFiles(self::SUITE) as $file)
		{
			if ($this->reservesTheGuardWithoutShowingTheSecret(file_get_contents($file)))
			{
				$findings[] = \Test\Tree::relativePath($file);
			}
		}

		$this->assertSame(array(), $findings,
			"These files write a fixture that carries " . \Helper\ProbeGuard::MARKER . " and never call \\Helper\\ProbeGuard::query().\n"
			. "A browser cannot send the " . \Helper\ProbeGuard::HEADER . " header, so the fixture answers "
			. \Helper\ProbeGuard::REFUSAL . " unless its URL carries '?' . \\Helper\\ProbeGuard::query().");
	}

	/**
	 * @dataProvider sources
	 * @param string $source
	 * @param bool $expected
	 */
	public function testTheDetectorReadsTokensNotText($source, $expected)
	{
		$this->assertSame($expected, $this->reservesTheGuardWithoutShowingTheSecret("<?php\n" . $source));
	}

	public function sources()
	{
		$marker = \Helper\ProbeGuard::MARKER;

		return array(
			'a fixture with the marker and no query' => array("\$src = '{$marker}';", true),
			'a fixture with the marker in a heredoc and no query' => array("\$src = <<<'PHP'\n{$marker}\nPHP;\n", true),
			'the marker and a fully qualified query' => array("\$src = '{$marker}'; \$I->amOnPage('/p.php?' . \\Helper\\ProbeGuard::query());", false),
			'the marker and an imported query' => array("\$src = '{$marker}'; \$I->amOnPage('/p.php?' . ProbeGuard::query());", false),
			'the marker and a query with spacing around the operator' => array("\$src = '{$marker}'; ProbeGuard :: query();", false),
			'the marker and a differently named static' => array("\$src = '{$marker}'; ProbeGuard::secret();", true),
			'the marker only in a comment' => array("// {$marker}", false),
			'no marker at all' => array("\$I->amOnPage('/p.php');", false),
		);
	}

	/**
	 * @param string $source
	 * @return bool whether a string in $source carries the guard's marker while no code in it calls ProbeGuard::query()
	 */
	private function reservesTheGuardWithoutShowingTheSecret($source)
	{
		$reserved = false;
		$shown = false;
		$tokens = token_get_all($source);
		$count = count($tokens);

		for ($i = 0; $i < $count; $i++)
		{
			if (!is_array($tokens[$i]))
			{
				continue;
			}

			if (in_array($tokens[$i][0], array(T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE), true)
				&& strpos($tokens[$i][1], \Helper\ProbeGuard::MARKER) !== false)
			{
				$reserved = true;
			}

			if ($tokens[$i][0] === T_STRING && $tokens[$i][1] === 'query' && $this->isCalledOnProbeGuard($tokens, $i))
			{
				$shown = true;
			}
		}

		return $reserved && !$shown;
	}

	/**
	 * @param array $tokens
	 * @param int $i index of a name token
	 * @return bool whether $tokens[$i] is the static member of ProbeGuard
	 */
	private function isCalledOnProbeGuard(array $tokens, $i)
	{
		$separator = $this->neighbourIndex($tokens, $i, -1);

		if ($separator === null || !is_array($tokens[$separator]) || $tokens[$separator][0] !== T_DOUBLE_COLON)
		{
			return false;
		}

		$class = $this->neighbour($tokens, $separator, -1);

		if (!is_array($class))
		{
			return false;
		}

		$parts = explode('\\', $class[1]);

		return end($parts) === 'ProbeGuard';
	}

	/**
	 * @param array $tokens
	 * @param int $i
	 * @param int $direction 1 for the next token, -1 for the previous
	 * @return array|string|null the nearest token that is not whitespace or a comment
	 */
	private function neighbour(array $tokens, $i, $direction)
	{
		$index = $this->neighbourIndex($tokens, $i, $direction);

		return $index === null ? null : $tokens[$index];
	}

	/**
	 * @param array $tokens
	 * @param int $i
	 * @param int $direction
	 * @return int|null
	 */
	private function neighbourIndex(array $tokens, $i, $direction)
	{
		for ($j = $i + $direction; isset($tokens[$j]); $j += $direction)
		{
			if (!is_array($tokens[$j]) || !in_array($tokens[$j][0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true))
			{
				return $j;
			}
		}

		return null;
	}
}
