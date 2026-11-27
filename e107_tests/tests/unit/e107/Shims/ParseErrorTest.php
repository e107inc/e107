<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @see https://github.com/e107inc/e107/issues/6816
 */

namespace e107\Shims;

class ParseErrorTest extends \Test\Unit
{
	const PARSES = "<?php\ndefine('LAN_TEST', 'a \"b\" c');\n";

	const BROKEN = "<?php\n\ndefine(\"LAN_TEST\", \"a \"b\" c\");\n";

	public function testCodePhpCanParseHasNoParseError()
	{
		$this->skipWhereNothingCanTell();

		$this->assertSame('', InternalShims::parse_error(self::PARSES));
		$this->assertSame('', \eShims::parse_error(self::PARSES));
	}

	public function testCodePhpCannotParseGivesTheErrorAndItsLine()
	{
		$this->skipWhereNothingCanTell();

		$error = InternalShims::parse_error(self::BROKEN);

		$this->assertStringContainsString('syntax error', $error);
		$this->assertStringEndsWith(' on line 3', $error);
	}

	public function testACompileErrorThePhpParserRaisesIsReportedToo()
	{
		if(!class_exists('CompileError'))
		{
			$this->markTestSkipped('PHP '.PHP_VERSION.' has no CompileError to catch.');
		}

		$error = InternalShims::parse_error("<?php\nclass e107_tests_parse_error_doubled { public public \$x; }\n");

		$this->assertNotEmpty($error);
		$this->assertStringEndsWith(' on line 2', $error);
	}

	public function testParsingRunsNothing()
	{
		$this->skipWhereNothingCanTell();

		$this->assertSame('', InternalShims::parse_error("<?php\nfunction e107_tests_parse_error_never_declared() {}\n"));
		$this->assertFalse(function_exists('e107_tests_parse_error_never_declared'));
	}

	public function testAPhpVersionWithoutTokenParseCannotTell()
	{
		if(defined('TOKEN_PARSE'))
		{
			$this->markTestSkipped('PHP '.PHP_VERSION.' has TOKEN_PARSE.');
		}

		$this->assertNull(InternalShims::parse_error(self::BROKEN));
		$this->assertNull(InternalShims::parse_error(self::PARSES));
	}

	private function skipWhereNothingCanTell()
	{
		if(!defined('TOKEN_PARSE'))
		{
			$this->markTestSkipped('PHP '.PHP_VERSION.' has no TOKEN_PARSE.');
		}
	}
}
