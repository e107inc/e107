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

		/** @var bool whether a case has put the news item below into the database */
		private $newsSeeded = false;

		/** Matched by the seeded news item alone, and long enough to clear the minimum query length. */
		const SEARCH_QUERY = 'zorblattonium';

		const NEWS_TITLE = 'Zorblattonium supplies';

		/** The seeded title as the renderer emits it, with the matched keyword marked up. */
		const HIGHLIGHTED_TITLE = '<mark>Zorblattonium</mark> supplies';

		const CATEGORY_NAME = 'Rare metals';

		const CUSTOM_PREFIX = 'From the archive:';

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

			if($this->newsSeeded)
			{
				$this->runInBootedCli(self::removeNewsItem());
			}
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
		 * @see https://github.com/e107inc/e107/issues/6299
		 */
		public function testExcerptTextDropsBbcodePayloads()
		{
			$excerpt = $this->toExcerptText(
				"Those are examples: [img]{e_MEDIA_IMAGE}2023-01/e107_dashboard_2_.png[/img]\n"
				. "[img]{e_MEDIA_IMAGE}2023-01/e107_dashboard_3_.png[/img] And admin panel");

			self::assertSame('Those are examples: And admin panel', $excerpt,
				'The image payload, the path constant and the line break all go.');
		}

		/**
		 * Plenty of bbcodes carry a payload in the opening tag, not only the nine {@see e_parse::isBBcode()} knows.
		 */
		public function testExcerptTextDropsPayloadsOfEveryBbcode()
		{
			self::assertSame('See click here please',
				$this->toExcerptText('See [url=https://example.com/p?token=abc123]click here[/url] please'));

			self::assertSame('Watch this now',
				$this->toExcerptText('Watch this [youtube]dQw4w9WgXcQ[/youtube] now'));

			self::assertSame('He said something quoted earlier',
				$this->toExcerptText('He said [quote]something quoted[/quote] earlier'));
		}

		/**
		 * A field cut to a byte count, as the faqs handler does, can end inside a bbcode.
		 */
		public function testExcerptTextOnAFieldCutMidBbcode()
		{
			self::assertSame('See click he',
				$this->toExcerptText('See [url=https://example.com/p?token=abc123]click he'),
				'The tag word and the parameter it carried both go, as they did before this change.');

			self::assertSame('Those are examples: {e_MEDIA_IMAGE}2023-01/e107_dashboard_2_.png',
				$this->toExcerptText('Those are examples: [img]{e_MEDIA_IMAGE}2023-01/e107_dashboard_2_.png'),
				'What the parser cannot render it cannot absorb, so the payload of a cut tag survives here as it did before. The cut is the defect, not the cleanup.');
		}

		public function testExcerptTextKeepsTheAuthorsLineBreaksAsSpaces()
		{
			self::assertSame('He said something quoted earlier today',
				$this->toExcerptText("He said\n[quote]something quoted[/quote]\nearlier today"),
				'A block on its own line keeps the words either side of it apart.');

			self::assertSame('First para. Second para.',
				$this->toExcerptText("<p>First para.</p>\n<p>Second para.</p>"));

			self::assertSame('A B', $this->toExcerptText('A<BR>B'),
				'A line break written in capitals is still a line break.');
		}

		public function testExcerptTextAddsNoSpaceOfItsOwn()
		{
			self::assertSame('see this, ok', $this->toExcerptText('see <a href="/a">this</a>, ok'),
				'Punctuation after a link stays against the word it follows.');

			self::assertSame('unbelievable stuff', $this->toExcerptText('un[b]believ[/b]able stuff'),
				'Emphasis inside a word does not split it.');

			self::assertSame('這是粗體字', $this->toExcerptText('這是<b>粗體</b>字'),
				'A script that does not space its words keeps its shape.');
		}

		public function testExcerptTextLeavesTagRemovalToStripTags()
		{
			self::assertSame('a b', $this->toExcerptText('a<br title="x>y">b'),
				'A quoted attribute holding a bare angle bracket leaves nothing of the tag behind.');
		}

		public function testExcerptTextKeepsProseEmoticonsAndBrackets()
		{
			self::assertSame('This is bold and this is a heading',
				$this->toExcerptText('This is [b]bold[/b] and this is a [h]heading[/h]'));

			self::assertSame('Plain prose is left alone.',
				$this->toExcerptText('Plain prose is left alone.'));

			self::assertSame('Smile :) now', $this->toExcerptText('Smile :) now'),
				'An emoticon is text an excerpt can show; its image is not.');

			self::assertSame('See [Fig. 2] for details', $this->toExcerptText('See [Fig. 2] for details'),
				'Bracketed prose that no bbcode could be keeps its brackets, where the old cleanup deleted it.');

			self::assertSame('As shown in and', $this->toExcerptText('As shown in [1] and [2]'),
				'A bracketed word on its own is shaped exactly like a tag, so it goes, as it did before.');
		}

		/**
		 * {@see e_parse::toHTML()} renders markup rather than removing it, and leaves an unterminated tag alone.
		 */
		public function testExcerptTextStripsWhatTheParserLeavesBehind()
		{
			self::assertSame('Broken', $this->toExcerptText('Broken <img src=x onerror=alert(1)'));

			self::assertSame('Before after', $this->toExcerptText('Before <script>alert(1)</script> after'));
		}

		public function testExcerptTextEncodesAnAmpersandForTheResultsPage()
		{
			self::assertSame('Fish &amp; Chips', $this->toExcerptText('Fish & Chips'));
		}

		private function toExcerptText($text)
		{
			$method = new ReflectionMethod('e_search', 'toExcerptText');
			$method->setAccessible(true);

			return $method->invoke(new e_search(''), $text);
		}

		/**
		 * @see https://github.com/e107inc/e107/issues/6327
		 */
		public function testTheCustomPrefixIsRenderedBeforeTheTitle()
		{
			$result = $this->renderSearchPage(2, self::CUSTOM_PREFIX);

			self::assertSame(self::CUSTOM_PREFIX . ' ' . self::HIGHLIGHTED_TITLE, self::headingOf($result['out']),
				"A handler set to its own prefix text renders that text, then one space, then the title.\n" . $result['out']);
			self::assertStringNotContainsString('pre_title_output', $result['out'],
				"The prefix a handler renders must never be left undefined.\n" . $result['out']);
			self::assertSame(0, $result['exit'], $result['out']);
		}

		public function testAnEmptyCustomPrefixLeavesTheTitleAlone()
		{
			$result = $this->renderSearchPage(2, '');

			self::assertSame(self::HIGHLIGHTED_TITLE, self::headingOf($result['out']),
				"An empty prefix text puts nothing before the title, not even a space.\n" . $result['out']);
			self::assertSame(0, $result['exit'], $result['out']);
		}

		public function testTheHandlerSuppliesItsOwnPrefixAndSeparator()
		{
			$result = $this->renderSearchPage(1, self::CUSTOM_PREFIX);

			self::assertSame(self::CATEGORY_NAME . ' | ' . self::HIGHLIGHTED_TITLE, self::headingOf($result['out']),
				"The news handler's own prefix is the category name and the separator it carries with it.\n" . $result['out']);
			self::assertSame(0, $result['exit'], $result['out']);
		}

		public function testADisabledPrefixRendersTheTitleOnly()
		{
			$result = $this->renderSearchPage(0, self::CUSTOM_PREFIX);

			self::assertSame(self::HIGHLIGHTED_TITLE, self::headingOf($result['out']),
				"A disabled prefix renders neither the handler's own text nor the site's.\n" . $result['out']);
			self::assertSame(0, $result['exit'], $result['out']);
		}

		/**
		 * Renders the search page for the news handler in a subprocess, over a news item seeded there.
		 *
		 * Word boundaries stay off so that the one regular expression path runs the same on MySQL 5.7 and 8.
		 *
		 * @param int $preTitle the pre_title pref: 0 none, 1 the handler's own, 2 the text in $preTitleAlt
		 * @param string $preTitleAlt the pre_title_alt pref
		 * @return array {out: string, exit: int}
		 */
		private function renderSearchPage($preTitle, $preTitleAlt)
		{
			$php = "chdir('" . addslashes(APP_PATH) . "'); ";
			$php .= "register_shutdown_function(function() { while(ob_get_level() > 0) { @ob_end_flush(); } }); ";
			$php .= self::seedNewsItem();
			$php .= "e107::getConfig()->setPref('e_search_list', array('news' => 'news')); ";
			$php .= "e107::getConfig('search')->setPref('plug_handlers/news', array('class' => '0', 'chars' => 150, 'results' => 10, "
				. "'pre_title' => " . (int) $preTitle . ", 'pre_title_alt' => '" . addslashes($preTitleAlt) . "', 'order' => 1)); ";
			$php .= "e107::getConfig('search')->setPref('mysql_sort', 0); ";
			$php .= "e107::getConfig('search')->setPref('boundary', 0); ";
			$php .= "\$_GET = array('q' => '" . self::SEARCH_QUERY . "', 't' => 'news', 'r' => 0); ";
			$php .= "require_once('" . addslashes(APP_PATH . '/search.php') . "'); ";

			$this->newsSeeded = true;
			list($output, $status) = $this->runInBootedCli($php);

			return array('out' => implode("\n", $output), 'exit' => $status);
		}

		/**
		 * PHP that seeds one categorised news item, which only {@see e_searchTest::removeNewsItem()} takes out again.
		 *
		 * @return string
		 */
		private static function seedNewsItem()
		{
			$category = "'" . self::CATEGORY_NAME . "'";
			$title = "'" . self::NEWS_TITLE . "'";

			$php = "e107::getDb()->createQueryBuilder()->insert('news_category')"
				. "->values(array('category_name' => " . $category . "))->execute(); ";
			$php .= "\$category = e107::getDb()->createQueryBuilder()->select('category_id')->from('news_category')"
				. "->where('category_name', " . $category . ")->fetchOne(); ";
			$php .= "e107::getDb()->createQueryBuilder()->insert('news')->values(array("
				. "'news_title' => " . $title . ", 'news_body' => '', 'news_extended' => '', 'news_summary' => '', "
				. "'news_meta_description' => '', 'news_thumbnail' => '', 'news_category' => \$category, "
				. "'news_datestamp' => time() - 60, 'news_start' => 0, 'news_end' => 0, 'news_class' => 0, "
				. "'news_render_type' => 0))->execute(); ";

			return $php;
		}

		/**
		 * PHP that takes the seeded news item back out, run from here because a subprocess killed on its timeout cleans nothing up.
		 *
		 * @return string
		 */
		private static function removeNewsItem()
		{
			return "e107::getDb()->createQueryBuilder()->delete('news')"
				. "->where('news_title', '" . self::NEWS_TITLE . "')->execute(); "
				. "e107::getDb()->createQueryBuilder()->delete('news_category')"
				. "->where('category_name', '" . self::CATEGORY_NAME . "')->execute(); ";
		}

		/**
		 * The text of the first result's link, which is the prefix and the title.
		 *
		 * @param string $html the rendered page
		 * @return string
		 */
		private static function headingOf($html)
		{
			$found = preg_match("~<h4><a class='title visit' href='[^']*'>(.*?)</a></h4>~s", $html, $matches);

			return $found ? $matches[1] : '';
		}
	}
