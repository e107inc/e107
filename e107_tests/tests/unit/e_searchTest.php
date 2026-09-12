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

		/** @var array */
		private $globalsBefore;

		/** @var string */
		private $probeTable;

		/** @var bool */
		private $probeBuilt = false;

		protected function _before()
		{
			require_once(e_HANDLER . 'search_class.php');

			// Kept in memory only: nothing here is saved, and _after puts the
			// original list back before any other test can read it.
			$this->installedBefore = e107::getConfig()->get('plug_installed');
			$this->multibyteBefore = e107::getParser()->ustrlen('é') === 1;

			$this->probeTable = MPREFIX . 'search_highlight_probe';
			$this->globalsBefore = array();

			foreach($this->searchGlobals() as $name)
			{
				$this->globalsBefore[$name] = isset($GLOBALS[$name]) ? $GLOBALS[$name] : null;
			}
		}

		protected function _after()
		{
			e107::getConfig()->set('plug_installed', $this->installedBefore);
			e107::getParser()->setMultibyte($this->multibyteBefore);

			foreach($this->globalsBefore as $name => $value)
			{
				if(is_null($value))
				{
					unset($GLOBALS[$name]);
					continue;
				}

				$GLOBALS[$name] = $value;
			}

			if($this->probeBuilt)
			{
				e107::getDb()->execute('DROP TABLE IF EXISTS `' . $this->probeTable . '`');
				$this->probeBuilt = false;
			}
		}

		/**
		 * The page-scope globals {@see e_search::parsesearch()} reads instead of taking arguments.
		 */
		private function searchGlobals()
		{
			return array('query', 'search_prefs', 'pre_title', 'search_chars', 'search_res', 'result_flag');
		}

		/**
		 * One row of known text, indexed per column because MATCH() needs an index over exactly its own column list.
		 */
		private function buildProbe()
		{
			$sql = e107::getDb();
			$sql->execute('DROP TABLE IF EXISTS `' . $this->probeTable . '`');
			$this->probeBuilt = true;
			$sql->execute('CREATE TABLE `' . $this->probeTable . '` ('
				. 'probe_id INT NOT NULL, probe_title VARCHAR(255) NOT NULL, probe_summary TEXT NOT NULL,'
				. ' FULLTEXT KEY probe_title (probe_title), FULLTEXT KEY probe_summary (probe_summary)'
				. ') ENGINE=MyISAM');
			$sql->execute('INSERT INTO `' . $this->probeTable
				. '` (probe_id, probe_title, probe_summary) VALUES (1, :title, :summary)',
				array(
					'title'   => 'Release wibble#wobble notes for wibble- builds',
					'summary' => 'Upgrade notes for wibble.wobble and for wibbleXwobble. See #wobble on its own.',
				));
		}

		/**
		 * Searches the probe row the way a search handler does, returning the rendered result list.
		 */
		private function searchProbe($searchQuery, $mysqlSort, $boundary)
		{
			global $query, $search_prefs, $pre_title, $search_chars, $search_res, $result_flag;

			$this->buildProbe();

			$query = $searchQuery;
			$search_prefs = array('mysql_sort' => $mysqlSort, 'boundary' => $boundary,
				'php_limit' => 10, 'relevance' => 0);
			$pre_title = 0;
			$search_chars = 200;
			$search_res = 10;
			$result_flag = 0;

			$search = new e_search($searchQuery);
			$ps = $search->parsesearch('search_highlight_probe', 'probe_id, probe_title, probe_summary',
				array('probe_title', 'probe_summary'), array(1.2, 0.6), array($this, 'searchProbeResult'),
				'nothing found', '', array());

			return $ps['text'];
		}

		/**
		 * Stands in for a plugin's search compile function; {@see e_search::parsesearch()} calls it per row.
		 */
		public function searchProbeResult($row)
		{
			return array(
				'link'         => 'index.php',
				'pre_title'    => '',
				'title'        => $row['probe_title'],
				'summary'      => $row['probe_summary'],
				'detail'       => '',
				'pre_summary'  => '',
				'post_summary' => '',
				'omit_result'  => false,
			);
		}

		/**
		 * @see https://github.com/e107inc/e107/issues/6311
		 */
		public function testHighlightingKeepsAResultMatchedByAKeywordThatIsNotAPattern()
		{
			self::assertStringContainsString('<mark>wibble#wobble</mark>',
				$this->searchProbe('wibble#wobble', 0, 0),
				'A keyword carrying the pattern delimiter must be highlighted, not blanked.');
		}

		/**
		 * The shipped default: MySQL sorting, and the word boundary the admin panel writes.
		 *
		 * @see https://github.com/e107inc/e107/issues/6311
		 */
		public function testHighlightingKeepsAResultWithWordBoundariesOn()
		{
			self::assertStringContainsString('<mark>wibble#wobble</mark>',
				$this->searchProbe('wibble#wobble', 1, 1),
				'A word boundary must not cost the highlight of a keyword that ends in punctuation.');
		}

		/**
		 * A keyword whose edge is not a word character is the case a word boundary cannot assert beside.
		 *
		 * @see https://github.com/e107inc/e107/issues/6311
		 */
		public function testHighlightingKeepsAKeywordThatEndsOnPunctuation()
		{
			self::assertStringContainsString('<mark>wibble-</mark>',
				$this->searchProbe('wibble-', 1, 1),
				'A word boundary asks about the neighbouring character, not about the keyword.');
		}

		/**
		 * The other edge, where the neighbour is the word character and the keyword's own edge is not.
		 *
		 * @see https://github.com/e107inc/e107/issues/6311
		 */
		public function testHighlightingKeepsAKeywordThatStartsOnPunctuation()
		{
			$text = $this->searchProbe('#wobble', 1, 1);

			self::assertStringContainsString('wibble<mark>#wobble</mark>', $text,
				'A keyword that starts on punctuation is marked where it follows a word character.');
			self::assertStringContainsString('See <mark>#wobble</mark>', $text,
				'The same keyword standing on its own is marked as well.');
		}

		/**
		 * A wildcard runs the mark to the end of the word, whatever the keyword's own last character is.
		 *
		 * @see https://github.com/e107inc/e107/issues/6311
		 */
		public function testHighlightingExtendsAWildcardToTheEndOfTheWord()
		{
			self::assertStringContainsString('<mark>wibble</mark>', $this->searchProbe('wibb*', 1, 1),
				'A wildcard keyword marks the whole of the word it matched.');
			self::assertStringContainsString('<mark>wibble#wobble</mark>', $this->searchProbe('wibble#*', 1, 1),
				'A keyword ending on punctuation keeps its wildcard.');
		}

		/**
		 * @see https://github.com/e107inc/e107/issues/6311
		 */
		public function testHighlightingMarksOnlyTheKeywordItself()
		{
			$text = $this->searchProbe('wibble.wobble', 0, 0);

			self::assertStringContainsString('<mark>wibble.wobble</mark>', $text,
				'The keyword the reader typed is what gets marked.');
			self::assertStringNotContainsString('<mark>wibbleXwobble</mark>', $text,
				'A dot in the keyword matches a dot, not any character.');
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
	}
