<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Language;

/**
 * A language file read without running it yields the names, order and values PHP would define by running it.
 */
class PhraseFileTest extends \Test\Unit
{
	/** @var PhraseFile */
	private $reader;

	protected function _before()
	{
		$this->reader = new PhraseFile();
	}

	public function testDefinesComeBackInOrderWithTheirValues()
	{
		$read = $this->read("define('LAN_B', 'Bee');\ndefine(\"LAN_A\", \"Ay\");\ndefine('LAN_dl_1', 'Mixed case');");

		self::assertSame(array('LAN_B' => 'Bee', 'LAN_A' => 'Ay', 'LAN_dl_1' => 'Mixed case'), $read['phrases']);
		self::assertSame(array('LAN_B' => 'd', 'LAN_A' => 'd', 'LAN_dl_1' => 'd'), $read['how']);
		self::assertSame(array(), $read['unsupported']);
	}

	public function testTheFirstDefineOfANameWins()
	{
		$read = $this->read("define('LAN_401', 'Quote');\ndefine('LAN_2', 'Two');\ndefine('LAN_401', 'Jump');");

		self::assertSame(array('LAN_401' => 'Quote', 'LAN_2' => 'Two'), $read['phrases']);
	}

	public function testTheLastArrayEntryWinsInTheFirstPosition()
	{
		$read = $this->read("return [\n'LAN_401' => 'Quote',\n'LAN_2' => 'Two',\n'LAN_401' => 'Jump',\n];");

		self::assertSame(array('LAN_401' => 'Jump', 'LAN_2' => 'Two'), $read['phrases']);
		self::assertSame(array('LAN_401' => 'a', 'LAN_2' => 'a'), $read['how']);

		self::assertSame(array('LAN_X' => 'x'), $this->phrases("return array('LAN_X' => 'x');"));
	}

	public function testADefineWinsOverAnArrayEntry()
	{
		$read = $this->read("define('LAN_A', 'defined');\nreturn ['LAN_A' => 'listed', 'LAN_B' => 'listed'];");

		self::assertSame(array('LAN_A' => 'defined', 'LAN_B' => 'listed'), $read['phrases']);
		self::assertSame(array('LAN_A' => 'd', 'LAN_B' => 'a'), $read['how']);
	}

	public function testADefineUnderItsOwnGuardCountsAsGuarded()
	{
		$read = $this->read(
			"if (!defined(\"PAGE_NAME\")) { define(\"PAGE_NAME\", \"Page\"); }\n".
			"if(!defined('USER_ERR_01')) define('USER_ERR_01', 'Missing');\n".
			"define('LAN_AFTER', 'after');"
		);

		self::assertSame(array('PAGE_NAME' => 'g', 'USER_ERR_01' => 'g', 'LAN_AFTER' => 'd'), $read['how']);
	}

	public function testADefineUnderAnyOtherConditionIsConditional()
	{
		$read = $this->read(
			"if(\$x) { define('LAN_IF', 'if'); } else { define('LAN_ELSE', 'else'); }\n".
			"if(!defined('LAN_OTHER')) { define('LAN_GUARDED_ELSEWHERE', 'g'); }\n".
			"if(\$y) define('LAN_BRACELESS', 'b');\n".
			"if(\$z) {} else if(!defined('LAN_ELSEIF')) define('LAN_ELSEIF', 'e');\n".
			"if(!defined('LAN_NESTED')) { if(\$w) { define('LAN_NESTED', 'n'); } }\n".
			"define('LAN_TOP', 'top');"
		);

		self::assertSame(array(
			'LAN_IF'                => 'c',
			'LAN_ELSE'              => 'c',
			'LAN_GUARDED_ELSEWHERE' => 'c',
			'LAN_BRACELESS'         => 'c',
			'LAN_ELSEIF'            => 'c',
			'LAN_NESTED'            => 'c',
			'LAN_TOP'               => 'd',
		), $read['how']);
	}

	public function testOnlyCallsOfTheGlobalDefineCount()
	{
		$phrases = $this->phrases(
			"/* define('LAN_BLOCK', 'x'); */\n".
			"// define('LAN_LINE', 'x');\n".
			"# define('LAN_HASH', 'x');\n".
			"function f() { define('LAN_FUNCTION', 'x'); }\n".
			"\$g = function() { define('LAN_CLOSURE', 'x'); };\n".
			"class C { function m() { define('LAN_METHOD', 'x'); } }\n".
			"\$o->define('LAN_MEMBER', 'x');\n".
			"Foo::define('LAN_STATIC', 'x');\n".
			"Foo\\define('LAN_NAMESPACED', 'x');\n".
			"\\define('LAN_QUALIFIED', 'q');\n".
			"DEFINE('LAN_UPPER', 'u');"
		);

		self::assertSame(array('LAN_QUALIFIED' => 'q', 'LAN_UPPER' => 'u'), $phrases);
	}

	public function testReadingStopsWhereTheFileWould()
	{
		$phrases = $this->phrases(
			"if (!defined(\"e107_INIT\")) { exit; }\n".
			"if(\$x) { return; }\n".
			"define('LAN_RUNS', 'runs');\n".
			"exit;\n".
			"define('LAN_NEVER', 'never');"
		);

		self::assertSame(array('LAN_RUNS' => 'runs'), $phrases);

		$phrases = $this->phrases("setlocale(LC_ALL, 'en_GB.UTF-8');\nreturn ['LAN_A' => 'a'];\ndefine('LAN_AFTER', 'after');");

		self::assertSame(array('LAN_A' => 'a'), $phrases);
	}

	public function testADefineThatMayNotRunIsConditional()
	{
		$read = $this->read(
			"\$x && define('LAN_AND', 'a');\n".
			"false or define('LAN_OR', 'o');\n".
			"\$y ? define('LAN_TERNARY', 't') : null;\n".
			"\$z ? null : define('LAN_ELSE_ARM', 'e');\n".
			"\$f = fn() => define('LAN_ARROW', 'f');\n".
			"if(\$x): define('LAN_ALT_1', '1'); define('LAN_ALT_2', '2'); elseif(\$y): define('LAN_ALT_3', '3'); else: define('LAN_ALT_4', '4'); endif;\n".
			"foreach(\$a as \$b): define('LAN_FOREACH', 'f'); endforeach;\n".
			"if(\$x) { \$v = 1; } else { define('LAN_BRACED_ELSE', 'b'); }\n".
			"define('LAN_TOP', 'top');"
		);

		self::assertSame(array(
			'LAN_AND'         => 'c',
			'LAN_OR'          => 'c',
			'LAN_TERNARY'     => 'c',
			'LAN_ELSE_ARM'    => 'c',
			'LAN_ARROW'       => 'c',
			'LAN_ALT_1'       => 'c',
			'LAN_ALT_2'       => 'c',
			'LAN_ALT_3'       => 'c',
			'LAN_ALT_4'       => 'c',
			'LAN_FOREACH'     => 'c',
			'LAN_BRACED_ELSE' => 'c',
			'LAN_TOP'         => 'd',
		), $read['how']);
	}

	public function testWhatFollowsAReturnOrExitThatMayNotRunIsConditional()
	{
		$read = $this->read("defined('e107_INIT') or exit;\ndefine('LAN_AFTER_GUARD', 'x');");
		self::assertSame(array('LAN_AFTER_GUARD' => 'c'), $read['how']);

		$read = $this->read("if(\$x) return;\ndefine('LAN_AFTER_RETURN', 'x');");
		self::assertSame(array('LAN_AFTER_RETURN' => 'c'), $read['how']);

		$read = $this->read("if(\$x) return ['LAN_A' => 'a'];\nreturn ['LAN_B' => 'b'];");
		self::assertSame(array('LAN_B' => 'b'), $read['phrases']);
		self::assertSame(array('LAN_B' => 'c'), $read['how']);
	}

	public function testAConstantStaysAPartAndTheLiteralsStayThemselves()
	{
		$phrases = $this->phrases(
			"define('LAN_PATH', 'Upload to '.e_PLUGIN.' and '.e_ADMIN.'menus.php');\n".
			"define('LAN_ALONE', SITENAME);\n".
			"define('LAN_JOINED', 'a' . 'b');\n".
			"define('SHOW_HELP', TRUE);\n".
			"define('SHOW_NOT', false);\n".
			"define('LAN_NUMBER', 12);"
		);

		self::assertSame(array(
			'LAN_PATH'   => array('Upload to ', array('e_PLUGIN'), ' and ', array('e_ADMIN'), 'menus.php'),
			'LAN_ALONE'  => array(array('SITENAME')),
			'LAN_JOINED' => 'ab',
			'SHOW_HELP'  => true,
			'SHOW_NOT'   => false,
			'LAN_NUMBER' => 12,
		), $phrases);
	}

	public function testEscapesAreTheOnesPhpApplies()
	{
		$phrases = $this->phrases(
			"define('LAN_N', \"one\\ntwo\");\n".
			"define('LAN_Q', \"say \\\"hi\\\"\");\n".
			"define('LAN_B', \"back\\\\\\\\n\");\n".
			"define('LAN_D', \"%1\\\$s and \$\");\n".
			"define('LAN_S', 'it\\'s \\\\ \\n');\n".
			"define('LAN_M', \"two\nlines\");"
		);

		self::assertSame(array(
			'LAN_N' => "one\ntwo",
			'LAN_Q' => 'say "hi"',
			'LAN_B' => 'back\\\\n',
			'LAN_D' => '%1$s and $',
			'LAN_S' => "it's \\ \\n",
			'LAN_M' => "two\nlines",
		), $phrases);
	}

	public function testAValueThatCannotBeReadIsItsOwnName()
	{
		$read = $this->read(
			"define('LAN_VAR', \"Hello \$name\");\n".
			"define('LAN_CALL', sprintf('%s', 'x'));\n".
			"define('LAN_ARRAY', array('x'));\n".
			"define('LAN_FINE', 'fine');"
		);

		self::assertSame(array('LAN_VAR' => 'LAN_VAR', 'LAN_CALL' => 'LAN_CALL', 'LAN_ARRAY' => 'LAN_ARRAY', 'LAN_FINE' => 'fine'), $read['phrases']);
		self::assertSame(array('LAN_VAR' => 'LAN_VAR', 'LAN_CALL' => 'LAN_CALL', 'LAN_ARRAY' => 'LAN_ARRAY'), $read['unsupported']);
	}

	public function testANameThatIsNotALiteralIsSkipped()
	{
		self::assertSame(array('LAN_KEPT' => 'k'), $this->phrases("define(\$name, 'x');\ndefine('LAN_'.\$n, 'y');\ndefine('LAN_KEPT', 'k');"));
	}

	public function testAnArrayEntryThatCannotBeReadLosesOnlyItself()
	{
		$read = $this->read("return [\n'LAN_A' => 'a',\n'LAN_B' => strtoupper('b'),\n'LAN_C' => ['nested', 'array'],\n'LAN_D' => 'd',\n];");

		self::assertSame(array('LAN_A' => 'a', 'LAN_B' => 'LAN_B', 'LAN_C' => 'LAN_C', 'LAN_D' => 'd'), $read['phrases']);
	}

	public function testNothingToReadIsEmpty()
	{
		self::assertSame(array('phrases' => array(), 'how' => array(), 'unsupported' => array()), $this->reader->read(''));
		self::assertSame(array(), $this->phrases('// nothing here'));
	}

	/**
	 * @dataProvider shippedEnglishFiles
	 * @param string $file path under the app root
	 */
	public function testEveryShippedEnglishFileReadsAsPhpDefinesIt($file)
	{
		$path = APP_PATH.'/'.$file;
		$read = $this->reader->read(file_get_contents($path));

		self::assertSame(array(), $read['unsupported'], 'every shipped phrase is read without running the file');

		$placeholders = array();
		$expected = array();

		foreach($read['phrases'] as $name => $value)
		{
			if(is_array($value))
			{
				$resolved = '';

				foreach($value as $part)
				{
					if(is_array($part))
					{
						$placeholders[$part[0]] = "\0".$part[0]."\0";
						$part = $placeholders[$part[0]];
					}

					$resolved .= $part;
				}

				$value = $resolved;
			}

			$expected[] = array($name, $value);
		}

		$child = "error_reporting(0); define('e107_INIT', true); ";
		$child .= "foreach(".var_export($placeholders, true)." as \$n => \$v) { define(\$n, \$v); } ";
		$child .= "\$before = get_defined_constants(true); \$before = isset(\$before['user']) ? \$before['user'] : array(); ";
		$child .= "\$ret = include ".var_export($path, true)."; ";
		$child .= "if(is_array(\$ret)) { foreach(\$ret as \$k => \$v) { if(!defined(\$k)) { define(\$k, \$v); } } } ";
		$child .= "\$after = get_defined_constants(true); \$out = array(); ";
		$child .= "foreach(\$after['user'] as \$k => \$v) { if(!array_key_exists(\$k, \$before)) { \$out[] = array(\$k, \$v); } } ";
		$child .= "echo base64_encode(serialize(\$out));";

		list($output, $status) = $this->runInCli($child, '-d xdebug.mode=off -d display_errors=0');

		self::assertSame(0, $status, implode("\n", $output));
		self::assertSame(unserialize(base64_decode(implode("", $output))), $expected);
	}

	/**
	 * @return array
	 */
	public function shippedEnglishFiles()
	{
		$root = APP_PATH;
		$files = array();

		foreach(array('e107_languages/English', 'e107_plugins/*/languages', 'e107_themes/*/languages') as $pattern)
		{
			foreach(glob($root.'/'.$pattern, GLOB_ONLYDIR) as $dir)
			{
				$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));

				foreach($iterator as $file)
				{
					$relative = (string) substr($file->getPathname(), strlen($root) + 1);

					if(substr($relative, -4) !== '.php' || strpos($relative, '/help/') !== false
						|| (strpos($relative, '/English/') === false && strpos(basename($relative), 'English') !== 0))
					{
						continue;
					}

					$files[$relative] = array($relative);
				}
			}
		}

		ksort($files);

		return $files;
	}

	/**
	 * @param string $body source after the opening tag
	 * @return array
	 */
	private function read($body)
	{
		return $this->reader->read("<?php\n".$body."\n");
	}

	/**
	 * @param string $body
	 * @return array
	 */
	private function phrases($body)
	{
		$read = $this->read($body);

		return $read['phrases'];
	}
}
