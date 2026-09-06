<?php
/**
 * Fails the suite on a wait for a duration anywhere in the test tree (#6262).
 */
class noBlindWaitsTest extends \Test\Unit
{
	const SANCTIONED = 'tests/_support/Test/Poll.php';

	/** @var array functions that sleep for a duration */
	private static $functions = array('sleep', 'usleep', 'time_nanosleep', 'time_sleep_until');

	/** @var array methods that sleep for a duration, whatever the object */
	private static $methods = array('wait');

	/** @var array directories under tests/ that hold no test code */
	private static $skippedDirectories = array('_data', '_generated', '_output');

	public function testTheTestTreeWaitsOnConditionsNotDurations()
	{
		$findings = array();

		foreach (\Test\Tree::phpFiles('tests', self::$skippedDirectories) as $file)
		{
			$path = \Test\Tree::relativePath($file);

			if ($path === self::SANCTIONED)
			{
				continue;
			}

			foreach ($this->blindWaitsIn(file_get_contents($file)) as $finding)
			{
				$findings[] = $path . ':' . $finding;
			}
		}

		$this->assertSame(array(), $findings,
			"These calls wait for a duration, which is too short on a slow runner and too long everywhere else.\n"
			. "Wait on the condition instead: \\Test\\Poll::until() with a callback that reports success,\n"
			. "or WebDriver's waitForElement(), waitForText() and waitForJS() for what a browser can see.");
	}

	/**
	 * @dataProvider sources
	 * @param string $source
	 * @param array $expected
	 */
	public function testTheDetectorReadsTokensNotText($source, array $expected)
	{
		$this->assertSame($expected, $this->blindWaitsIn("<?php\n" . $source));
	}

	public function sources()
	{
		return array(
			'a plain sleep' => array('sleep(1);', array('2 sleep()')),
			'a fully qualified sleep' => array('\usleep(5);', array('2 usleep()')),
			'a sleep relative to the current namespace' => array('namespace\sleep(1);', array('2 sleep()')),
			'the nanosecond spelling' => array('time_nanosleep(0, 5);', array('2 time_nanosleep()')),
			'a sleep until a timestamp' => array('time_sleep_until(microtime(true) + 1);', array('2 time_sleep_until()')),
			'the actor asked to wait' => array('$I->wait(3);', array('2 wait()')),
			'a static wait' => array('Clock::wait(3);', array('2 wait()')),
			'a wait with spacing around the arrow' => array("\$I\n\t->wait( 3 );", array('3 wait()')),
			'one finding per call' => array("sleep(1);\nusleep(1);", array('2 sleep()', '3 usleep()')),
			'a sleep in a comment' => array('// sleep(1)', array()),
			'a sleep in a string' => array("\$sql = 'SELECT sleep(5)';", array()),
			'a function declared with the name' => array('function sleep($seconds) {}', array()),
			'a function in another namespace' => array('Clock\sleep(1);', array()),
			'a method whose name starts the same way' => array('$this->waitForTheDueWindow();', array()),
			'a property called wait' => array('$this->wait = 3;', array()),
			'a php-webdriver wait that polls a condition' => array('$driver->wait(10)->until($ready);', array()),
			'a wait whose arguments hold parentheses, then a poll' => array('$driver->wait(max(1, $t))->until($ready);', array()),
		);
	}

	/**
	 * @param string $source
	 * @return array one "<line> <name>()" per call that waits for a duration
	 */
	private function blindWaitsIn($source)
	{
		$found = array();
		$tokens = token_get_all($source);
		$count = count($tokens);

		for ($i = 0; $i < $count; $i++)
		{
			$name = $this->sleepingCallee($tokens, $i);

			if ($name !== null)
			{
				$found[] = $tokens[$i][2] . ' ' . $name . '()';
			}
		}

		return $found;
	}

	/**
	 * @param array $tokens
	 * @param int $i
	 * @return string|null the name $tokens[$i] calls, when the call is one that sleeps
	 */
	private function sleepingCallee(array $tokens, $i)
	{
		if (!is_array($tokens[$i]) || !in_array($tokens[$i][0], $this->nameTokens(), true))
		{
			return null;
		}

		$open = $this->neighbourIndex($tokens, $i, 1);

		if ($open === null || $tokens[$open] !== '(')
		{
			return null;
		}

		$parts = explode('\\', $tokens[$i][1]);
		$name = strtolower(end($parts));
		$before = $this->neighbour($tokens, $i, -1);
		$beforeId = is_array($before) ? $before[0] : $before;

		if (in_array($beforeId, array(T_OBJECT_OPERATOR, T_DOUBLE_COLON), true))
		{
			return in_array($name, self::$methods, true) && !$this->isFollowedByUntil($tokens, $open) ? $name : null;
		}

		if (in_array($beforeId, array(T_FUNCTION, T_NEW), true) || $this->isInAnotherNamespace($tokens, $i))
		{
			return null;
		}

		return in_array($name, self::$functions, true) ? $name : null;
	}

	/**
	 * @param array $tokens
	 * @param int $open index of the call's opening parenthesis
	 * @return bool whether the call's result is asked to poll, as php-webdriver's wait()->until() does
	 */
	private function isFollowedByUntil(array $tokens, $open)
	{
		$depth = 0;

		for ($j = $open; isset($tokens[$j]); $j++)
		{
			if ($tokens[$j] === '(')
			{
				$depth++;
			}
			elseif ($tokens[$j] === ')' && --$depth === 0)
			{
				$arrow = $this->neighbour($tokens, $j, 1);
				$method = $this->neighbour($tokens, $j + 1, 1);

				return is_array($arrow) && $arrow[0] === T_OBJECT_OPERATOR
					&& is_array($method) && $method[0] === T_STRING && strtolower($method[1]) === 'until';
			}
		}

		return false;
	}

	/**
	 * @param array $tokens
	 * @param int $i
	 * @return bool whether the name at $tokens[$i] belongs to a namespace of its own
	 */
	private function isInAnotherNamespace(array $tokens, $i)
	{
		if (defined('T_NAME_QUALIFIED') && $tokens[$i][0] === T_NAME_QUALIFIED)
		{
			return true;
		}

		$separator = $this->neighbourIndex($tokens, $i, -1);

		if ($separator === null || $tokens[$separator][0] !== T_NS_SEPARATOR)
		{
			return false;
		}

		$qualifier = $this->neighbour($tokens, $separator, -1);

		return is_array($qualifier) && $qualifier[0] === T_STRING;
	}

	/**
	 * @return array the token ids a called name can carry on this PHP
	 */
	private function nameTokens()
	{
		$ids = array(T_STRING);

		if (defined('T_NAME_FULLY_QUALIFIED'))
		{
			$ids[] = T_NAME_FULLY_QUALIFIED;
			$ids[] = T_NAME_QUALIFIED;
			$ids[] = T_NAME_RELATIVE;
		}

		return $ids;
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
