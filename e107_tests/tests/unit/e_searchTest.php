<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_searchTest extends \Test\Unit
	{
		/** @var array */
		private $installedBefore;

		/** @var bool */
		private $multibyteBefore;

		protected function _before()
		{
			require_once(e_HANDLER . 'search_class.php');

			// Kept in memory only: nothing here is saved, and _after puts the
			// original list back before any other test can read it.
			$this->installedBefore = e107::getConfig()->get('plug_installed');
			$this->multibyteBefore = e107::getParser()->ustrlen('é') === 1;
		}

		protected function _after()
		{
			e107::getConfig()->set('plug_installed', $this->installedBefore);
			e107::getParser()->setMultibyte($this->multibyteBefore);
			unset($GLOBALS['search_chars']);
		}

		public function testGetCommentHandlerPath()
		{
			self::assertSame(e_HANDLER . 'search/comments_news.php',
				e_search::getCommentHandlerPath('news', array('id' => 0, 'dir' => 'core', 'class' => '0')));

			self::assertSame(e_PLUGIN . 'poll/search/search_comments.php',
				e_search::getCommentHandlerPath('poll', array('id' => 4, 'dir' => 'poll', 'class' => '0')));

			// A malformed entry with no directory is treated as a core handler,
			// which is what the pref writers have always assumed.
			self::assertSame(e_HANDLER . 'search/comments_page.php',
				e_search::getCommentHandlerPath('page', array('id' => 'page', 'class' => '0')));
		}

		/**
		 * @see https://github.com/e107inc/e107/issues/5267
		 */
		public function testIsCommentHandlerAvailableFollowsPluginDirectory()
		{
			$handler = array('id' => 4, 'dir' => 'poll', 'class' => '0');

			e107::getConfig()->removePref('plug_installed/poll');
			self::assertFalse(e_search::isCommentHandlerAvailable('poll', $handler),
				'A handler in an uninstalled plugin is not usable.');

			e107::getConfig()->setPref('plug_installed/poll', '1.0');
			self::assertTrue(e_search::isCommentHandlerAvailable('poll', $handler),
				'A handler in an installed plugin is usable.');
		}

		/**
		 * The entry e107 ships for the download plugin claims 'core' as its
		 * directory, so the plugin it belongs to is named by the key alone.
		 *
		 * @see https://github.com/e107inc/e107/issues/2003
		 */
		public function testIsCommentHandlerAvailableFollowsKeyForCoreEntries()
		{
			$handler = array('id' => 2, 'dir' => 'core', 'class' => '0');

			e107::getConfig()->removePref('plug_installed/download');
			self::assertFalse(e_search::isCommentHandlerAvailable('download', $handler),
				'A core-flagged handler provided by an uninstalled plugin is not usable.');

			e107::getConfig()->setPref('plug_installed/download', '1.0');
			self::assertTrue(e_search::isCommentHandlerAvailable('download', $handler),
				'A core-flagged handler provided by an installed plugin is usable.');
		}

		/**
		 * news, page and user declare installRequired="false", so their
		 * handlers must never depend on the plug_installed pref.
		 */
		public function testIsCommentHandlerAvailableKeepsHandlersThatNeedNoInstall()
		{
			foreach(array('news', 'page', 'user') as $key)
			{
				e107::getConfig()->removePref('plug_installed/' . $key);

				self::assertTrue(e_search::isCommentHandlerAvailable($key, array('dir' => 'core', 'class' => '0')),
					$key . ' does not require installation, so its handler must stay available.');
			}
		}

		/**
		 * Handlers of plugins that were deleted rather than uninstalled have
		 * no plugin data to check, so the caller's readability check decides.
		 */
		public function testIsCommentHandlerAvailableToleratesMissingPlugin()
		{
			$handler = array('id' => 5, 'dir' => 'content', 'class' => '0');

			self::assertTrue(e_search::isCommentHandlerAvailable('content', $handler));
			self::assertFalse(is_readable(e_search::getCommentHandlerPath('content', $handler)),
				'The 0.7-era content plugin should not be on disk.');
		}

		public function testIsCommentHandlerAvailableToleratesMalformedEntries()
		{
			self::assertTrue(e_search::isCommentHandlerAvailable('news', array()),
				'A handler with no directory falls back to core.');
			self::assertTrue(e_search::isCommentHandlerAvailable('', array('dir' => '')),
				'A handler with nothing to check must not be silently dropped.');
		}

		/**
		 * The crop sizes its window against the text it is about to cut, so an offset left behind by the title or by an earlier row cannot move it.
		 *
		 * @see https://github.com/e107inc/e107/issues/6318
		 */
		public function testCropWithoutKeywordMatchIgnoresTheOffsetFromAnotherField()
		{
			$GLOBALS['search_chars'] = 60;
			$text = str_repeat('lorem ipsum dolor sit amet ', 8);

			$search = new e_search();
			$search->text = $text;
			$search->query = 'zebra';
			$search->pos = 120;

			$search->parsesearch_crop();

			self::assertSame(substr($text, 0, 60) . '...', $search->text,
				'A summary the keyword never matched crops from its start.');
		}

		/**
		 * An empty keyword, which the search box makes out of a bare '+', leaves the crop with nothing to centre on.
		 *
		 * @see https://github.com/e107inc/e107/issues/6318
		 */
		public function testCropTreatsAnEmptyKeywordAsNoMatch()
		{
			$GLOBALS['search_chars'] = 60;
			$text = str_repeat('lorem ipsum dolor sit amet ', 8);

			$search = new e_search();
			$search->text = $text;
			$search->query = '';
			$search->pos = 120;

			$search->parsesearch_crop();

			self::assertSame(substr($text, 0, 60) . '...', $search->text,
				'An empty keyword crops the summary from its start.');
		}

		/**
		 * The limit counts characters, so a short summary in a multibyte language is not cut and does not get the ellipsis that says it was.
		 *
		 * @see https://github.com/e107inc/e107/issues/6318
		 */
		public function testCropLeavesAShortMultibyteSummaryWhole()
		{
			$GLOBALS['search_chars'] = 60;
			e107::getParser()->setMultibyte(true);
			$text = str_repeat('привет ', 8);

			$search = new e_search();
			$search->text = $text;
			$search->query = 'zebra';
			$search->pos = 0;

			$search->parsesearch_crop();

			self::assertSame($text, $search->text,
				'56 characters in 104 bytes is under a 60 character limit.');
		}

		/**
		 * A keyword longer than two thirds of the limit cannot be centred, so the window starts at the first character and does not pretend otherwise.
		 *
		 * @see https://github.com/e107inc/e107/issues/6318
		 */
		public function testCropOmitsTheLeadingEllipsisWhenTheWindowStartsAtTheHead()
		{
			$GLOBALS['search_chars'] = 60;
			$text = str_repeat('a', 15) . str_repeat('b', 45) . str_repeat('c', 105);

			$search = new e_search();
			$search->text = $text;
			$search->query = str_repeat('b', 45);
			$search->pos = 15;

			$search->parsesearch_crop();

			self::assertSame(substr($text, 0, 60) . '...', $search->text,
				'A window that starts at the first character keeps the text before it.');
		}

		/**
		 * A crop that measures its own offset still centres the window on a keyword the text does carry.
		 *
		 * @see https://github.com/e107inc/e107/issues/6318
		 */
		public function testCropCentresTheWindowOnTheKeywordItFinds()
		{
			$GLOBALS['search_chars'] = 60;
			$text = str_repeat('lorem ipsum ', 8) . 'needle' . str_repeat(' dolor sit', 8);

			$search = new e_search();
			$search->text = $text;
			$search->query = 'needle';
			$search->pos = 0;

			$search->parsesearch_crop();

			self::assertSame('...' . substr($text, 76, 60) . '...', $search->text,
				'A keyword the summary carries keeps its window.');
		}
	}
