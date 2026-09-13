<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2026 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 * Cover for the emote panel the BBCode toolbar drops down, which used to be
	 * built by a second copy of r_emote() whose anchors hand addtext() nothing to
	 * work back from (#6135).
	 */

	class bbcode_shortcodesTest extends \Test\Unit
	{
		/** @var bbcode_shortcodes */
		private $sc;

		/** @var array pref name => value as found, restored in _after() */
		private $savedPrefs = array();

		protected function _before()
		{
			$this->sc = e107::getScBatch('bbcode');

			foreach(array('comments_emoticons' => 1, 'smiley_activate' => 1, 'wysiwyg' => 0) as $name => $value)
			{
				$this->savedPrefs[$name] = e107::getConfig()->get($name);
				e107::getConfig()->set($name, $value);
			}
		}

		protected function _after()
		{
			foreach($this->savedPrefs as $name => $value)
			{
				e107::getConfig()->set($name, $value);
			}
		}

		public function testEmotePanelCarriesTheAnchorsThatResolveTheirOwnField()
		{
			$this->assertNotEmpty(e107::getEmote()->getList(),
				'precondition: the install has emoticons for the panel to hold');

			$panel = $this->sc->bb_emotes('bbcode-emotes-probe');

			$this->assertStringContainsString("class='addEmote'", $panel,
				'the toolbar drops down the panel r_emote() builds, whose anchors say which field the click belongs to');
			$this->assertStringNotContainsString('javascript:addtext(', $panel,
				'a javascript: anchor hands addtext() no element to work back from, which is what the second copy of the panel emitted');
		}
	}
