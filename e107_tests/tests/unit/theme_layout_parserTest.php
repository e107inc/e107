<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2026 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

	class theme_layout_parserTest extends \Codeception\Test\Unit
	{
		/** @var e_theme_layout_parser */
		protected $parser;

		protected function _before()
		{
			require_once(e_HANDLER."theme_layout_parser.php");
			$this->parser = new e_theme_layout_parser();
		}

		private function parse($body, $theme = null)
		{
			return $this->parser->parse("<?php\n".$body, $theme);
		}

		public function testUnassignedVariablesAreNull()
		{
			$result = $this->parse("\$SOMETHINGELSE = 'x';");

			$this->assertSame(
				array('HEADER' => null, 'FOOTER' => null, 'CUSTOMHEADER' => null, 'CUSTOMFOOTER' => null, 'LAYOUT' => null),
				$result
			);
		}

		public function testSingleQuotedString()
		{
			$result = $this->parse("\$HEADER = 'a{MENU=1}b';");
			$this->assertSame('a{MENU=1}b', $result['HEADER']);
		}

		public function testSingleQuotedEscapes()
		{
			$result = $this->parse('$HEADER = \'it\\\'s a back\\\\slash\';');
			$this->assertSame("it's a back\\slash", $result['HEADER']);
		}

		public function testDoubleQuotedEscapes()
		{
			$result = $this->parse('$FOOTER = "line\\none\\ttab \\"quoted\\" \\$literal";');
			$this->assertSame("line\none\ttab \"quoted\" \$literal", $result['FOOTER']);
		}

		public function testDoubleQuotedInterpolationIsDropped()
		{
			$result = $this->parse('$HEADER = "before $somevar after";');
			$this->assertSame('before  after', $result['HEADER']);
		}

		public function testHeredoc()
		{
			$body = "\$LAYOUT['home'] = <<<TMPL\n<div>{MENU=1}</div>\nTMPL;\n";
			$result = $this->parse($body);
			$this->assertSame(array('home' => "<div>{MENU=1}</div>"), $result['LAYOUT']);
		}

		public function testNowdocKeepsBackslashes()
		{
			$body = "\$HEADER = <<<'TMPL'\nkeep\\nthis\nTMPL;\n";
			$result = $this->parse($body);
			$this->assertSame('keep\\nthis', $result['HEADER']);
		}

		public function testEmptyArrayThenElementAssignment()
		{
			$result = $this->parse("\$HEADER = array();\n\$HEADER['default'] = 'first';\n\$HEADER['home'] = 'second';");
			$this->assertSame(array('default' => 'first', 'home' => 'second'), $result['HEADER']);
		}

		public function testShortArrayLiteralWithKeys()
		{
			$result = $this->parse("\$CUSTOMHEADER = ['HOME' => 'a', 'FULL' => 'b'];");
			$this->assertSame(array('HOME' => 'a', 'FULL' => 'b'), $result['CUSTOMHEADER']);
		}

		public function testLongArrayLiteralWithTrailingComma()
		{
			$result = $this->parse("\$CUSTOMFOOTER = array('HOME' => 'a', 'FULL' => 'b',);");
			$this->assertSame(array('HOME' => 'a', 'FULL' => 'b'), $result['CUSTOMFOOTER']);
		}

		public function testConcatenationWithDefinedConstant()
		{
			if(!defined('THEME_LAYOUT_PARSER_TEST'))
			{
				define('THEME_LAYOUT_PARSER_TEST', 'middle');
			}

			$result = $this->parse("\$HEADER = 'a'.THEME_LAYOUT_PARSER_TEST.'b';");
			$this->assertSame('amiddleb', $result['HEADER']);
		}

		public function testUndefinedConstantFoldsToEmptyString()
		{
			$result = $this->parse("\$HEADER = 'a'.LAN_NO_SUCH_CONSTANT_HERE.'b';");
			$this->assertSame('ab', $result['HEADER']);
		}

		public function testThemeConstantsResolveToTheThemeBeingRead()
		{
			$result = $this->parse("\$HEADER = THEME;", 'somelegacytheme');
			$this->assertSame(e_THEME.'somelegacytheme/', $result['HEADER']);
		}

		public function testConcatenatingAssignment()
		{
			$result = $this->parse("\$FOOTER = 'one';\n\$FOOTER .= 'two';");
			$this->assertSame('onetwo', $result['FOOTER']);
		}

		public function testUnfoldableExpressionKeepsItsSource()
		{
			$result = $this->parse("\$HEADER = str_repeat('{MENU=1}', 2);");
			$this->assertSame("str_repeat('{MENU=1}', 2)", $result['HEADER']);
		}

		public function testUnfoldableElementAssignmentKeepsItsKey()
		{
			$result = $this->parse("\$LAYOUT['home'] = someHelper('{MENU=3}');");
			$this->assertSame(array('home' => "someHelper('{MENU=3}')"), $result['LAYOUT']);
		}

		public function testAssignmentInsideAFunctionIsIgnored()
		{
			$body = "function somethemefn()\n{\n\t\$HEADER = 'inner';\n}\n\$HEADER = 'outer';";
			$result = $this->parse($body);
			$this->assertSame('outer', $result['HEADER']);
		}

		public function testAssignmentInsideAClassIsIgnored()
		{
			$body = "class some_theme\n{\n\tpublic function render()\n\t{\n\t\t\$FOOTER = 'inner';\n\t}\n}\n";
			$result = $this->parse($body);
			$this->assertNull($result['FOOTER']);
		}

		public function testReadingTheVariableIsNotAnAssignment()
		{
			$result = $this->parse("\$other = \$HEADER . 'x';");
			$this->assertNull($result['HEADER']);
		}

		public function testConditionalAssignmentIsCollected()
		{
			$body = "if(defined('SOMETHING_UNDEFINED'))\n{\n\t\$HEADER = 'conditional';\n}";
			$result = $this->parse($body);
			$this->assertSame('conditional', $result['HEADER']);
		}

		public function testNothingIsExecuted()
		{
			$result = $this->parse("\$HEADER = 'x';\ndefine('THEME_LAYOUT_PARSER_MUST_NOT_RUN', true);");

			$this->assertSame('x', $result['HEADER']);
			$this->assertFalse(defined('THEME_LAYOUT_PARSER_MUST_NOT_RUN'));
		}
	}
