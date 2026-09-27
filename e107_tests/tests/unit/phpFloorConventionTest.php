<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * Nothing this branch ships calls a function newer than the PHP it supports.
 */
class phpFloorConventionTest extends \Codeception\Test\Unit
{
	/** The oldest PHP a site running this branch may be on. */
	const FLOOR = '5.6';

	const RULE = 'A call to a function the floor lacks is a fatal on the oldest supported PHP, and nothing before the site runs reports it: php -l parses it, and every host new enough to run the suite answers it.';

	/**
	 * The constants naming a tree e107 ships, which is the reach of this rule.
	 *
	 * e_FILE, e_MEDIA and e_SYSTEM are what an installed site writes into rather than what it ships, and
	 * anything vendored is left to whatever downgrades it, so a dependency that reaches past the floor is out
	 * of sight here until it is brought in.
	 */
	private static $treeConstants = array('e_ADMIN', 'e_CORE', 'e_HANDLER', 'e_IMAGE', 'e_LANGUAGEDIR',
		'e_PLUGIN', 'e_THEME', 'e_WEB');

	/**
	 * Functions younger than the floor, against the release that added each one.
	 *
	 * The list is written by hand, so it holds down what core has reached for before rather than everything it could:
	 * a name absent from it passes. random_bytes() and random_int() are absent on purpose, because
	 * paragonie/random_compat declares both and the vendor autoloader's file list loads it on every boot.
	 */
	private static $tooNew = array(
		'intdiv'                        => '7.0',
		'preg_replace_callback_array'   => '7.0',
		'error_clear_last'              => '7.0',
		'is_iterable'                   => '7.1',
		'pcntl_async_signals'           => '7.1',
		'spl_object_id'                 => '7.2',
		'stream_isatty'                 => '7.2',
		'mb_chr'                        => '7.2',
		'mb_ord'                        => '7.2',
		'array_key_first'               => '7.3',
		'array_key_last'                => '7.3',
		'is_countable'                  => '7.3',
		'hrtime'                        => '7.3',
		'mb_str_split'                  => '7.4',
		'password_algos'                => '7.4',
		'get_mangled_object_vars'       => '7.4',
		'str_contains'                  => '8.0',
		'str_starts_with'               => '8.0',
		'str_ends_with'                 => '8.0',
		'fdiv'                          => '8.0',
		'get_debug_type'                => '8.0',
		'preg_last_error_msg'           => '8.0',
		'get_resource_id'               => '8.0',
		'array_is_list'                 => '8.1',
		'enum_exists'                   => '8.1',
		'fsync'                         => '8.1',
		'fdatasync'                     => '8.1',
		'mysqli_execute_query'          => '8.2',
		'ini_parse_quantity'            => '8.2',
		'memory_reset_peak_usage'       => '8.2',
		'json_validate'                 => '8.3',
		'mb_str_pad'                    => '8.3',
		'array_find'                    => '8.4',
		'array_find_key'                => '8.4',
		'array_any'                     => '8.4',
		'array_all'                     => '8.4',
		'mb_trim'                       => '8.4',
		'http_get_last_response_headers' => '8.4',
		'array_first'                   => '8.5',
		'array_last'                    => '8.5',
	);

	public function testNoShippedFileCallsAFunctionThePhpFloorLacks()
	{
		$offenders = array();

		foreach($this->sources() as $file)
		{
			foreach(self::floorBreaks(file_get_contents($file)) as $break)
			{
				list($line, $call) = $break;
				$offenders[] = substr($file, strlen(e_ROOT)).':'.$line.' '.$call.'() is PHP '.self::$tooNew[$call];
			}
		}

		sort($offenders);

		self::assertSame(array(), $offenders,
			"These shipped files call a function newer than PHP ".self::FLOOR.".\n".self::RULE."\n"
			."Write the call in a form the floor has: substr_compare() for str_ends_with(), end() with key() for\n"
			."array_key_last(), and so on."
		);
	}

	/** The scan is the whole of the rule's reach, so a tree it never enters is a rule that quietly stops there. */
	public function testTheScanReachesEveryTreeThatShipsPhp()
	{
		$scanned = $this->sources();
		$reached = array();

		foreach($scanned as $file)
		{
			$reached[strstr(substr($file, strlen(e_ROOT)).'/', '/', true)] = true;
		}

		$handlers = e_ROOT.basename(rtrim(e_HANDLER, '/')).'/';

		self::assertGreaterThan(500, count($scanned), 'the scan found almost nothing, so the assertion above is vacuous');
		self::assertContains($handlers.'login.php', $scanned);
		self::assertContains($handlers.'plugin_class.php', $scanned);
		self::assertSame(array(), array_diff(self::trees(), array_keys($reached)),
			'a tree e107 ships carried no PHP into the scan, so nothing in it is held to the floor. '.self::RULE);
	}

	public function testTheScanReadsCodeAndNotStringsOrComments()
	{
		$source = <<<'PHP'
<?php
class Sample
{
	public function host($tree)
	{
		$last = array_key_last($tree);
		$first = \array_key_first($tree);
		$size = intdiv(is_countable($tree) ? 4 : 2, 2);
		$tree->str_contains('x');
		Sample::str_starts_with('x');
		$text = 'str_ends_with(';
		// fdiv(1, 2);
		/* mb_str_split('x'); */
		return $last.$first.$size.$text;
	}

	private function is_countable($thing)
	{
		return $thing;
	}
}
PHP;

		self::assertSame(
			array(array(6, 'array_key_last'), array(7, 'array_key_first'), array(8, 'intdiv'), array(8, 'is_countable')),
			self::floorBreaks($source), 'two calls on one line are two offenders, and only a call is one');
	}

	/**
	 * @return string[] absolute paths of every file the rule reaches
	 */
	private function sources()
	{
		return \Test\Tree::shippedPhpFiles(self::trees());
	}

	/**
	 * @return string[] the directory each shipped tree is installed under, which an install may have renamed
	 */
	private static function trees()
	{
		$found = array();

		foreach(self::$treeConstants as $constant)
		{
			$found[] = basename(rtrim(constant($constant), '/'));
		}

		return $found;
	}

	/**
	 * @param string $source
	 * @return array one array(line number, function name) per call to a function younger than the floor, in source order
	 */
	private static function floorBreaks($source)
	{
		$found = array();
		$tokens = token_get_all($source);
		$names = array_keys(self::$tooNew);

		foreach($tokens as $i => $token)
		{
			if(\Test\Tree::isFunctionCall($tokens, $i, $names))
			{
				$found[] = array($token[2], ltrim($token[1], '\\'));
			}
		}

		return $found;
	}
}
