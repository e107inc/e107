<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

class e_parse_toDBWithoutScriptTest extends \Test\Unit
{
	const POSTED = '<p class="lead" onclick="q418()">Big <b>sale</b> &amp; "more" at $5</p><script>q418()</script>'
		.'<img src="x" onerror="q418()">[php]echo 1;[/php]';

	/**
	 * Content is stored as {@see e_parse::toDB()} stores it for an author who may post HTML but not script, PHP
	 * included, whatever the current user may post, and after a save that kept script.
	 */
	public function testContentIsStoredAsAnAuthorWhoMayPostHtmlButNotScript()
	{
		$fresh = new e_parse();
		$expected = $this->withPostClasses(e_UC_PUBLIC, e_UC_NOBODY, function () use ($fresh)
		{
			return $fresh->toDB(self::POSTED);
		}, e_UC_NOBODY);

		$this->assertStringContainsString('<b>sale</b>', $expected);
		$this->assertStringContainsString('&#036;5', $expected);
		$this->assertStringContainsString('&#91;php]', $expected);

		foreach(array('onclick', 'onerror', '<script') as $script)
		{
			$this->assertStringNotContainsString($script, $expected);
		}

		$tp = e107::getParser();
		$levels = array(
			'script and PHP' => array(e_UC_PUBLIC, e_UC_PUBLIC, e_UC_PUBLIC),
			'HTML'           => array(e_UC_PUBLIC, e_UC_NOBODY, e_UC_NOBODY),
			'text'           => array(e_UC_NOBODY, e_UC_NOBODY, e_UC_NOBODY),
		);

		foreach($levels as $level => $classes)
		{
			$stored = $this->withPostClasses($classes[0], $classes[1], function () use ($tp)
			{
				$tp->toDB(self::POSTED);

				return array($tp->toDBWithoutScript(self::POSTED), $tp->toDBWithoutScript(array('a' => self::POSTED)));
			}, $classes[2]);

			$this->assertSame(array($expected, array('a' => $expected)), $stored, 'a current user posting '.$level);
		}

		$this->assertSame(5, $tp->toDBWithoutScript(5));
		$this->assertSame('0', $tp->toDBWithoutScript('0'));
	}

	/**
	 * Converting an administrator's view of a stored script tells them nothing about their own script access.
	 */
	public function testAnAdministratorIsNotWarnedOfScriptTheyDidNotPost()
	{
		$this->assertTrue(deftrue('ADMIN'), 'the unit suite runs as an administrator');
		$messages = e107::getMessage();
		$messages->reset('warning');

		$stored = $this->withPostClasses(e_UC_PUBLIC, e_UC_NOBODY, function ()
		{
			return e107::getParser()->toDBWithoutScript('<p>Sale</p><script>q418()</script>');
		}, e_UC_NOBODY);

		$this->assertSame('<p>Sale</p>', $stored);
		$this->assertEmpty($messages->get('warning', 'default', true));
	}
}
