<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 *
 * e107_plugins/tinymce4/plugins/e107/parser.php:16-19 declares itself an admin
 * area before it boots the framework:
 *
 *   if(!defined('e_ADMIN_AREA')) { define('e_ADMIN_AREA', true); }
 *
 * It is a front-end request. The flag suppresses "logged in as" resolution
 * (user_model.php:2275), applies the admin permission-emulation overlay
 * (:2316), swaps the site language for adminlanguage inside quote.bb
 * (e107_class.php:4069), and stops the theme and override shortcode batches
 * being loaded at all (shortcode_handler.php:120). None of those is right for a
 * request whose whole job is to parse content for the front end.
 *
 * WHY THIS ASSERTS ON THE SOURCE. The declaration cannot be observed from
 * inside the unit suite: class2.php has already defined e_ADMIN_AREA by the
 * time any test runs, so parser.php's guarded define is a no-op there and
 * defined('e_ADMIN_AREA') answers true whatever parser.php says. Nor can it be
 * observed over HTTP on the shipped configuration: the acceptance install runs
 * bootstrap5 as the site theme and bootstrap3 as the admin theme, both of which
 * declare a bootstrap library version, so BOOTSTRAP is defined either way and
 * quote.bb renders identically; e_HTTP_STATIC is unset, so
 * e_parse_class.php:2629 takes the same branch either way; and only English is
 * installed, so the adminlanguage swap has nothing to swap to.
 *
 * That the flag has no observable effect on this install is the evidence that
 * removing it is safe. It is not evidence that it was removed, which is what
 * this test is for.
 *
 * @see \TinyMceParserGateCest::theParseOutputIsUnchangedByTheAdminAreaFlag
 *      for the byte-exact control on what the removal must not change.
 */
class parserAdminAreaTest extends \Codeception\Test\Unit
{
	const PARSER = 'tinymce4/plugins/e107/parser.php';

	public function testParserDoesNotDeclareItselfAnAdminArea()
	{
		$source = file_get_contents(e_PLUGIN.self::PARSER);

		$this->assertNotFalse($source, 'Could not read '.e_PLUGIN.self::PARSER);

		// assertSame on the match count rather than assertDoesNotMatchRegularExpression,
		// which prints the whole file on failure and buries the point.
		$this->assertSame(0, preg_match('/define\s*\(\s*[\'"]e_ADMIN_AREA[\'"]/', $source),
			'parser.php serves a front-end request and must not declare itself an admin area.');
	}

	/**
	 * That requiring parser.php under TINYMCE_UNIT_TEST still yields a usable
	 * class. NOT a control for the authorisation gate: parser.php:16 wraps both
	 * class2.php and the gate in the same !TINYMCE_UNIT_TEST condition, so a gate
	 * that refused every web request including a main administrator's would leave
	 * this test green.
	 *
	 * @see \TinyMceParserGateCest::anAdministratorIsStillParsed for the control on
	 *      the gate itself, which has to be an HTTP test for that reason.
	 */
	public function testTheParserClassIsStillUsableUnderTheUnitTestFlag()
	{
		@define('TINYMCE_UNIT_TEST', true);

		// parser.php echoes at file scope and calls setFontAwesome() on the shared
		// e_parse singleton. Both would otherwise persist for every later test in
		// this process, so swallow the output and put the version back: parser.php
		// sets it from the front-end theme, which is the value the singleton
		// already carries in this suite, so reasserting it is exact rather than
		// approximate.
		ob_start();
		require_once(e_PLUGIN.self::PARSER);
		ob_get_clean();
		e107::getParser()->setFontAwesome(e107::getTheme()->getFontAwesome());

		$this->assertTrue(class_exists('e107TinyMceParser'),
			'Requiring parser.php under TINYMCE_UNIT_TEST no longer defines the parser class.');

		$parser = $this->make('e107TinyMceParser');
		$parser->setHtmlClass(e_UC_PUBLIC);

		$this->assertSame("<strong class='bbcode bold bbcode-b'>bold</strong>",
			$parser->toHTML('[b]bold[/b]'));
	}
}
