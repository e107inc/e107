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
 * @see https://github.com/e107inc/e107/issues/6490
 */
class comment_menuTest extends \Test\Unit
{
	/** Marker proving the subprocess got past booting e107. */
	const BOOTED = 'E107-BOOTED';

	/** Precedes the JSON the subprocess reports once the configuration screen has finished. */
	const RESULT = 'E107-RESULT:';

	/** @var e_render */
	private $render;

	/** @var mixed */
	private $caption;

	/** @var mixed what the menu handed to {@see e_render::tablerender()} as its caption */
	private $heading;

	protected function _before()
	{
		$this->render = e107::getRender();
		$this->caption = e107::getConfig('menu')->get('comment_caption');

		e107::setRegistry('core/e107/singleton/e_render', $this->make('e_render', array(
			'tablerender' => function($caption)
			{
				$this->heading = $caption;
			},
		)));
	}

	protected function _after()
	{
		e107::setRegistry('core/e107/singleton/e_render', $this->render);
		e107::getConfig('menu')->set('comment_caption', $this->caption);
	}

	public function testSavingOneLanguageKeepsTheCaptionsOfTheOthers()
	{
		$result = $this->saveCaption(array('NotTheAdminLanguage' => 'Kept'), 'Posted');

		$this->assertEquals(array('NotTheAdminLanguage' => 'Kept', $result['language'] => 'Posted'), $result['caption']);
	}

	public function testSavingALanguageReplacesItsOwnCaptionEvenWhenNested()
	{
		$result = $this->saveCaption(array(e_LANGUAGE => array(e_LANGUAGE => 'Old'), 'NotTheAdminLanguage' => 'Kept'), 'Posted');

		$this->assertEquals(array('NotTheAdminLanguage' => 'Kept', $result['language'] => 'Posted'), $result['caption']);
	}

	public function testSavingOverTheShippedCaptionStoresThePostedLanguage()
	{
		$result = $this->saveCaption('Latest Comments', 'Posted');

		$this->assertSame(array($result['language'] => 'Posted'), $result['caption']);
	}

	public function testAVisitorWhoseLanguageHasNoCaptionGetsTheDefaultHeading()
	{
		$this->assertSame(LAN_COMMENTS, $this->headingFor(array('NotTheVisitorLanguage' => 'Other')));
	}

	public function testAVisitorWhoseLanguageHasAnEmptyCaptionGetsTheDefaultHeading()
	{
		$this->assertSame(LAN_COMMENTS, $this->headingFor(array(e_LANGUAGE => '', 'NotTheVisitorLanguage' => 'Other')));
	}

	public function testAVisitorWhoseLanguageHasACaptionGetsIt()
	{
		$this->assertSame('Mine', $this->headingFor(array(e_LANGUAGE => 'Mine', 'NotTheVisitorLanguage' => 'Other')));
	}

	public function testACaptionNestedByAnOlderSaveIsStillRead()
	{
		$this->assertSame('Nested', $this->headingFor(array(e_LANGUAGE => array(e_LANGUAGE => 'Nested'))));
	}

	public function testACaptionStoredWithoutLanguagesIsShownToEveryone()
	{
		$this->assertSame('Latest Comments', $this->headingFor('Latest Comments'));
	}

	/**
	 * Renders the comment menu with $caption stored and returns the heading it asked for.
	 *
	 * @param mixed $caption
	 * @return mixed
	 */
	private function headingFor($caption)
	{
		e107::getConfig('menu')->set('comment_caption', $caption);

		include(e_PLUGIN.'comment_menu/comment_menu.php');

		return $this->heading;
	}

	/**
	 * Submits the configuration screen in the admin language over a refused save, so nothing reaches storage.
	 *
	 * @param mixed $stored the caption preference before the save
	 * @param string $posted the caption typed into the field
	 * @return array the admin language and the caption preference the save left
	 */
	private function saveCaption($stored, $posted)
	{
		$php = "fwrite(STDERR, '".self::BOOTED."'); ";
		$php .= "\$screen = realpath('".addslashes(APP_PATH.'/e107_plugins/comment_menu/config.php')."'); ";
		$php .= "register_shutdown_function(function() { \$menu = e107::getConfig('menu'); fwrite(STDERR, '".self::RESULT."'.json_encode(array('language' => e_LANGUAGE, 'caption' => \$menu->get('comment_caption'))).PHP_EOL); }); ";
		$php .= "e107::getConfig('menu')->set('comment_caption', ".var_export($stored, true)."); ";
		$php .= "e107::getConfig('menu')->addValidationError('forced by comment_menuTest'); ";
		$php .= "\$_POST = array('comment_caption' => array(e_LANGUAGE => ".var_export($posted, true)."), 'comment_display' => '10', 'comment_characters' => '50', 'comment_postfix' => '...', 'update_menu' => 1); ";
		$php .= "require_once(\$screen); ";

		list($output) = $this->runInBootedCli($php);
		$lines = implode("\n", $output);

		$this->assertStringContainsString(self::BOOTED, $lines,
			"The subprocess did not get as far as booting e107, so nothing below can be trusted.\n".$lines);

		foreach($output as $line)
		{
			$parts = explode(self::RESULT, $line, 2);
			if(count($parts) === 2)
			{
				return json_decode($parts[1], true);
			}
		}

		$this->fail("The configuration screen never finished, so what it saved is unknown.\n".$lines);
	}
}
