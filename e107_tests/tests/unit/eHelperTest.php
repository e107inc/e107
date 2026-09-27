<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class eHelperTest extends \Test\Unit
	{
		/** @var eHelper */
		protected $hp;

		protected function _before()
		{
			try
			{
				$this->hp = $this->make('eHelper');
			}
			catch (Exception $e)
			{
				$this->fail("Couldn't load eHelper object");
			}

		}

		public function testTitle2sefFromPlainText()
		{
			$actual = $this->hp->title2sef('Plain text test');
			$expected = 'plain-text-test';

			$this->assertEquals($expected, $actual);
		}

		public function testTitle2sefFromPlainTextStripSpecialChars()
		{
			$actual = $this->hp->title2sef('Plain text test with special chars !()+*+#"\'\\');
			$expected = 'plain-text-test-with-special-chars';

			$this->assertEquals($expected, $actual);
		}

		public function testTitle2sefFromBbcodeText()
		{
			$actual = $this->hp->title2sef('BBCode [b]text[/b] test [img]logo.png[/img]');
			$expected = 'bbcode-text-test';

			$this->assertEquals($expected, $actual);
		}

		public function testTitle2sefFromHtmlText()
		{
			$actual = $this->hp->title2sef('HTML <b>text</b> test <img src="logo.png" />');
			$expected = 'html-text-test';

			$this->assertEquals($expected, $actual);
		}

		public function testBuildAttrRaisesNoDeprecation()
		{
			$deprecations = array();
			set_error_handler(function ($severity, $message) use (&$deprecations)
			{
				$deprecations[] = $message;
				return true;
			}, E_DEPRECATED);

			try
			{
				$attr = eHelper::buildAttr(array('class' => 'bbcode', 'style' => 'width:50%', 0 => 'x'));
			}
			finally
			{
				restore_error_handler();
			}

			$this->assertSame(array(), $deprecations);
			$this->assertSame('class=bbcode&style=width%3A50%25&0=x', $attr);
		}

	}
