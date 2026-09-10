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
		/** Fixed so a compiled result can be compared against an expected string. */
		const FIXTURE_DATESTAMP = 1674995700;

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

		/**
		 * One fabricated row per shipped e_search addon, keyed by plugin directory.
		 *
		 * @return array
		 */
		private function searchAddonRows()
		{
			$datestamp = self::FIXTURE_DATESTAMP;

			return array(
				'_blank' => array(
					'blank_id' => 1,
					'blank_nick' => '1.Ahsanul',
					'blank_message' => 'A message',
					'blank_datestamp' => $datestamp,
				),
				'chatbox_menu' => array(
					'cb_id' => 1,
					'cb_nick' => '1.Ahsanul',
					'cb_message' => 'A message',
					'cb_datestamp' => $datestamp,
				),
				'download' => array(
					'download_id' => 1,
					'download_sef' => 'a-download',
					'download_name' => 'A download',
					'download_author' => 'Ahsanul',
					'download_description' => 'A description',
					'download_category_id' => 1,
					'download_category_sef' => 'a-category',
					'download_category_name' => 'A category',
					'download_datestamp' => $datestamp,
				),
				'faqs' => array(
					'faq_id' => 1,
					'faq_info_id' => 1,
					'faq_info_title' => 'A category',
					'faq_info_sef' => 'a-category',
					'faq_question' => 'A question',
					'faq_answer' => 'An answer',
					'faq_datestamp' => $datestamp,
				),
				'forum' => array(
					'thread_id' => 1,
					'thread_name' => 'A thread',
					'thread_datestamp' => $datestamp,
					'forum_id' => 1,
					'forum_sef' => 'a-forum',
					'forum_name' => 'A forum',
					'user_id' => 1,
					'user_name' => 'Ahsanul',
					'post_id' => 1,
					'post_entry' => 'A post',
				),
				'news' => array(
					'news_id' => 1,
					'news_sef' => 'a-news-item',
					'news_title' => 'A news item',
					'news_body' => 'A news body',
					'news_extended' => '',
					'category_name' => 'A category',
					'news_datestamp' => $datestamp,
				),
				'page' => array(
					'page_id' => 1,
					'page_sef' => 'a-page',
					'page_title' => 'A page',
					'page_text' => 'A page body',
					'page_chapter' => 0,
					'menu_image' => '',
					'page_datestamp' => $datestamp,
				),
				'user' => array(
					'user_id' => 1,
					'user_name' => 'Ahsanul',
					'user_signature' => 'A signature',
					'user_join' => $datestamp,
				),
			);
		}

		/**
		 * Instantiates an addon the way search.php does, by naming its class after its directory.
		 *
		 * @param string $plugin
		 * @return e_search
		 */
		private function searchAddon($plugin)
		{
			e107::plugLan($plugin, 'global', true);
			require_once(e_PLUGIN.$plugin.'/e_search.php');

			$className = $plugin.'_search';
			$addon = new $className();
			$addon->setParams(array());

			return $addon;
		}

		/**
		 * Compiles one result through an addon.
		 *
		 * @param string $plugin
		 * @param array $row
		 * @return array
		 */
		private function compileSearchAddon($plugin, $row)
		{
			$addon = $this->searchAddon($plugin);

			return $addon->compile($row);
		}

		/**
		 * The row a compile() gets is the addon's own return_fields and nothing else, so a fixture may not invent one.
		 */
		public function testEverySearchAddonFixtureUsesOnlyReturnedColumns()
		{
			e107::coreLan('search');

			foreach($this->searchAddonRows() as $plugin => $row)
			{
				$config = $this->searchAddon($plugin)->config();
				$returned = array();

				foreach($config['return_fields'] as $field)
				{
					$parts = explode('.', $field);
					$returned[] = end($parts);
				}

				$invented = array_values(array_diff(array_keys($row), $returned));

				self::assertSame(array(), $invented,
					$plugin.' is handed a column its own query never selects, so whatever this row proves is fiction.');
			}
		}

		/**
		 * The addons below are enumerated by hand, so an addon nobody added a row for must fail rather than go unchecked.
		 */
		public function testEverySearchAddonHasAFixtureRow()
		{
			$shipped = array();

			foreach(glob(e_PLUGIN.'*/e_search.php') as $path)
			{
				$shipped[] = basename(dirname($path));
			}

			$covered = array_keys($this->searchAddonRows());
			sort($shipped);
			sort($covered);

			self::assertSame($shipped, $covered,
				'Every shipped e_search addon needs a row in searchAddonRows(), including the _blank scaffold that plugin authors copy.');
		}

		/**
		 * search_class.php joins pre_title straight onto the title, so the separator can only come from the addon.
		 *
		 * @see https://github.com/e107inc/e107/issues/6298
		 */
		public function testSearchAddonPreTitleEndsWithASeparator()
		{
			e107::coreLan('search');

			$emptyForThisRow = array('forum', 'page');

			foreach($this->searchAddonRows() as $plugin => $row)
			{
				$res = $this->compileSearchAddon($plugin, $row);
				$preTitle = $res['pre_title'];

				if(in_array($plugin, $emptyForThisRow))
				{
					self::assertSame('', $preTitle,
						$plugin.' has no pre_title for a row like this one, and anything appearing here would need its own separator.');
					continue;
				}

				self::assertStringEndsWith(' ', $preTitle,
					$plugin.' ends its pre_title with "'.$preTitle.'", which runs straight into the result title.');
			}
		}

		/**
		 * A search addon builds $res['detail'] itself and {DETAILS} renders it verbatim.
		 *
		 * @see https://github.com/e107inc/e107/issues/6298
		 */
		public function testDatedDetailSeparatesLabelFromDate()
		{
			e107::coreLan('search');

			$rows = $this->searchAddonRows();
			$expected = LAN_SEARCH_3.' '.e107::getParser()->toDate(self::FIXTURE_DATESTAMP, 'long');

			foreach(array('news', 'page') as $plugin)
			{
				$res = $this->compileSearchAddon($plugin, $rows[$plugin]);

				self::assertSame($expected, $res['detail'],
					'The '.$plugin.' result detail must put a space between the label and the date.');
			}
		}

		/**
		 * @see https://github.com/e107inc/e107/issues/6298
		 */
		public function testForumDetailSeparatesLabelAuthorAndDate()
		{
			e107::coreLan('search');

			$rows = $this->searchAddonRows();
			$res = $this->compileSearchAddon('forum', $rows['forum']);

			self::assertStringStartsWith(LAN_SEARCH_7.' ', $res['detail'],
				'The forum result detail must put a space between the label and the author.');
			self::assertStringContainsString(' '.LAN_SEARCH_8.' ', $res['detail'],
				'The forum result detail must space the date label away from the author and the date.');
		}
	}
