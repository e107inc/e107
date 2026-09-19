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

		/** @var array */
		private $globalsBefore;

		/** @var string */
		private $probeTable;

		/** @var bool */
		private $probeBuilt = false;

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

			if($this->newsSeeded)
			{
				$this->runInBootedCli(self::removeNewsItem());
			}
		}

		/**
		 * The page-scope globals {@see e_search::parsesearch()} reads instead of taking arguments.
		 */
		private function searchGlobals()
		{
			return array('query', 'search_prefs', 'pre_title', 'pre_title_alt', 'search_chars', 'search_res',
				'result_flag');
		}

		/**
		 * Two rows of known text, indexed per column because MATCH() needs an index over exactly its own column list.
		 * The second row carries only what an unescaped metacharacter would reach.
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
				. '` (probe_id, probe_title, probe_summary) VALUES (1, :title, :summary), (2, :title2, :summary2)',
				array(
					'title'    => 'Release wibble#wobble notes for wibble- builds',
					'summary'  => 'Upgrade notes for wibble.wobble and for wibbleXwobble. See #wobble on its own.'
						. ' Filed as wobble(12) by O&#039;Brien.',
					'title2'   => 'Second entry, about wibbleXwobble',
					'summary2' => 'No dot and no bracket anywhere in this one.',
				));
		}

		/**
		 * Searches the probe rows the way a search handler does, returning the rendered result list.
		 * $where and $order stand in for what a handler's own where() and order declare.
		 */
		private function searchProbe($searchQuery, $mysqlSort, $boundary, $where = '', $order = array())
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
				'nothing found', $where, $order);

			return $ps['text'];
		}

		/**
		 * The only result keys {@see e_search::parsesearch()} renders; a key outside this set reaches no page.
		 *
		 * @return array
		 */
		private static function renderedResultKeys()
		{
			return array('omit_result', 'pre_title', 'title', 'link', 'pre_summary', 'summary', 'detail', 'post_summary');
		}

		/**
		 * The statement the search connection last sent, with its bound values appended,
		 * because a prepared statement carries the pattern outside the SQL text.
		 */
		private function lastSearchStatement()
		{
			$last = e107::getDb('search')->getLastQuery();

			if(!is_array($last))
			{
				return (string) $last;
			}

			$bound = array();

			foreach($last['BIND'] as $parameter)
			{
				$bound[] = is_array($parameter) ? $parameter['value'] : $parameter;
			}

			return $last['PREPARE'] . ' -- ' . implode(' ', $bound);
		}

		/**
		 * Stands in for a plugin's search compile function; {@see e_search::parsesearch()} calls it per row.
		 */
		public function searchProbeResult($row)
		{
			$res = array_fill_keys(self::renderedResultKeys(), '');

			$res['link'] = 'index.php';
			$res['title'] = $row['probe_title'];
			$res['summary'] = $row['probe_summary'];

			return $res;
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

		/**
		 * @see https://github.com/e107inc/e107/issues/6330
		 */
		public function testWordBoundarySearchAsksForAPatternEveryServerAccepts()
		{
			e107::getDb('search')->resetLastError();
			$this->searchProbe('builds', 0, 1);
			$emitted = $this->lastSearchStatement();

			self::assertSame('', e107::getDb('search')->getLastErrorText(),
				'A pattern the server refuses costs every row, not just the boundary.');
			self::assertStringContainsString('REGEXP', $emitted,
				'The PHP sort method is the branch that sends a pattern to the server.');
			self::assertStringNotContainsString('[[:<:]]', $emitted,
				'MySQL 8.0.4 dropped the word markers and refuses the whole pattern.');
			self::assertStringNotContainsString('[[:>:]]', $emitted,
				'MySQL 8.0.4 dropped the word markers and refuses the whole pattern.');
		}

		/**
		 * The row itself has to come back, which is the half the highlighter cannot answer for.
		 *
		 * @see https://github.com/e107inc/e107/issues/6330
		 */
		public function testWordBoundarySearchFindsAKeywordThatEndsOnPunctuation()
		{
			self::assertStringContainsString('<mark>wibble-</mark>', $this->searchProbe('wibble-', 0, 1),
				'A word boundary asks about the neighbouring character, not about the keyword.');
		}

		/**
		 * @see https://github.com/e107inc/e107/issues/6357
		 */
		public function testSearchDropsAKeywordThatIsOnlyAnOperator()
		{
			self::assertSame('nothing found', $this->searchProbe('+ zzzzabsent', 0, 1),
				'A stray operator must not become an empty keyword that every row satisfies.');
			self::assertStringContainsString('<mark>builds</mark>', $this->searchProbe('+ builds', 0, 1),
				'Dropping the operator leaves the rest of the query to be searched.');
		}

		/**
		 * @see https://github.com/e107inc/e107/issues/6330
		 */
		public function testWordBoundarySearchStillMatchesWholeWordsOnly()
		{
			self::assertStringContainsString('<mark>builds</mark>', $this->searchProbe('builds', 0, 1),
				'A whole word is still found with the boundary preference on.');
			self::assertSame('nothing found', $this->searchProbe('build', 0, 1),
				'A fragment of a longer word is still refused with the boundary preference on.');
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

		/**
		 * page_search declares its own constructor and does not chain to this one,
		 * so the keyword arrays have to stand without it.
		 *
		 * @see https://github.com/e107inc/e107/issues/6359
		 */
		public function testKeywordArraysStandWithoutTheConstructor()
		{
			$property = new ReflectionProperty('e_search', 'keywords');
			$property->setAccessible(true);

			self::assertSame(
				array('split' => array(), 'wildcard' => array(), 'boolean' => array(),
					'match' => array(), 'exact' => array()),
				$property->getValue((new ReflectionClass('e_search'))->newInstanceWithoutConstructor()),
				'A search area whose own constructor does not chain still has every keyword array.'
			);
		}

		/**
		 * An unbalanced bracket is a pattern the server refuses outright, and a
		 * refused pattern costs every row of the area rather than one keyword.
		 *
		 * @see https://github.com/e107inc/e107/issues/6313
		 */
		public function testPhpSortSearchFindsAKeywordCarryingARegexpMetacharacter()
		{
			e107::getDb('search')->resetLastError();
			$text = $this->searchProbe('wobble(12', 0, 1);

			self::assertSame('', e107::getDb('search')->getLastErrorText(),
				'A keyword is text to search for, not a pattern for the server to compile.');
			self::assertStringContainsString('Release wibble#wobble', $text,
				'The row holding the keyword has to come back.');
		}

		/**
		 * The half that is invisible rather than empty: a metacharacter raises no
		 * error and quietly widens the search.
		 *
		 * @see https://github.com/e107inc/e107/issues/6313
		 */
		public function testPhpSortSearchDoesNotLetAMetacharacterMatchAnything()
		{
			$text = $this->searchProbe('wibble.wobble', 0, 0);

			self::assertStringContainsString('Release wibble#wobble', $text,
				'The row holding the literal text still matches.');
			self::assertStringNotContainsString('Second entry', $text,
				'A dot the reader typed matches a dot, not any character.');
		}

		/**
		 * Stored text carries the entities toDB() writes, so the keyword the
		 * server is asked to match has to be the one that went through toDB() too.
		 *
		 * @see https://github.com/e107inc/e107/issues/6313
		 */
		public function testPhpSortSearchFindsAKeywordCarryingAnApostrophe()
		{
			self::assertStringContainsString('Release wibble#wobble', $this->searchProbe("O'Brien", 0, 1),
				'An apostrophe in the search box still matches the text it was stored as.');
		}

		/**
		 * @see https://github.com/e107inc/e107/issues/6359
		 */
		public function testPhpSortSearchWithNothingLeftToAskAsksNothing()
		{
			e107::getDb('search')->resetLastError();

			self::assertSame('nothing found', $this->searchProbe('the and', 0, 1),
				'A query of nothing but stopwords finds nothing.');
			self::assertSame('', e107::getDb('search')->getLastErrorText(),
				'With no keywords left there is no statement to send, so there is no error to raise.');
		}

		/**
		 * @see https://github.com/e107inc/e107/issues/6359
		 */
		public function testPhpSortSearchExcludesAMinusKeyword()
		{
			self::assertStringContainsString('Release wibble#wobble', $this->searchProbe('builds -zzzzabsent', 0, 1),
				'Excluding a word no row holds leaves the rest of the query alone.');
			self::assertSame('nothing found', $this->searchProbe('builds -wibble', 0, 1),
				'Excluding a word the row holds drops the row.');
			self::assertSame('nothing found', $this->searchProbe('builds -wibb*', 0, 1),
				'A wildcard on an excluded keyword is read, not lost with the entry the branch removed.');
		}

		/**
		 * The operator shapes decide how the keyword groups are joined, which is
		 * where a rebuilt clause would silently return different rows.
		 *
		 * @see https://github.com/e107inc/e107/issues/6313
		 */
		public function testPhpSortSearchJoinsKeywordGroupsAsItAlwaysHas()
		{
			self::assertStringContainsString('Release wibble#wobble', $this->searchProbe('builds notes', 0, 1),
				'Two keywords the row holds match it.');
			self::assertStringContainsString('Release wibble#wobble', $this->searchProbe('builds zzzzabsent', 0, 1),
				'A second keyword widens the search rather than narrowing it.');
			self::assertSame('nothing found', $this->searchProbe('+builds +zzzzabsent', 0, 1),
				'Two required keywords both have to match.');
			self::assertStringContainsString('Release wibble#wobble', $this->searchProbe('+builds zzzzabsent', 0, 1),
				'A required keyword ends the clause, so an ordinary keyword after it is not asked for.');
		}

		/**
		 * What a search handler declares around the keywords: its own WHERE
		 * fragment, which ends in AND, and its own ordering.
		 *
		 * @see https://github.com/e107inc/e107/issues/6313
		 */
		public function testPhpSortSearchKeepsTheHandlersOwnWhereAndOrder()
		{
			self::assertStringContainsString('Release wibble#wobble',
				$this->searchProbe('builds', 0, 1, 'probe_id = 1 AND ', array('probe_id' => 'DESC')),
				'A handler fragment that keeps the row leaves it found.');
			self::assertSame('nothing found',
				$this->searchProbe('builds', 0, 1, 'probe_id = 2 AND ', array('probe_id' => 'DESC')),
				'A handler fragment that excludes the row is still applied.');
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
					'page_metadscr' => 'A meta description',
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
		 * The column names an addon's own query selects, as they reach its compile().
		 *
		 * @param string $plugin
		 * @return array
		 */
		private function searchAddonColumns($plugin)
		{
			$config = $this->searchAddon($plugin)->config();
			$columns = array();

			foreach($config['return_fields'] as $field)
			{
				$parts = explode('.', $field);
				$columns[] = end($parts);
			}

			return $columns;
		}

		/**
		 * Compiles one result the way a live search does, from the columns the addon's own query selects.
		 *
		 * @param string $plugin
		 * @param array $row
		 * @return array
		 */
		private function compileQueriedRow($plugin, $row)
		{
			$queried = array_intersect_key($row, array_flip($this->searchAddonColumns($plugin)));

			return $this->compileSearchAddon($plugin, $queried);
		}

		/**
		 * The row a compile() gets is the addon's own return_fields and nothing else, so a fixture may not invent one.
		 */
		public function testEverySearchAddonFixtureUsesOnlyReturnedColumns()
		{
			e107::coreLan('search');

			foreach($this->searchAddonRows() as $plugin => $row)
			{
				$invented = array_values(array_diff(array_keys($row), $this->searchAddonColumns($plugin)));

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

		/**
		 * A key the results page never reads renders nothing, so an addon that sets one is describing output it does not have.
		 *
		 * @see https://github.com/e107inc/e107/issues/6326
		 */
		public function testNoSearchAddonReturnsAKeyTheResultsPageIgnores()
		{
			e107::coreLan('search');

			foreach($this->searchAddonRows() as $plugin => $row)
			{
				$res = $this->compileSearchAddon($plugin, $row);
				$ignored = array_values(array_diff(array_keys($res), self::renderedResultKeys()));

				self::assertSame(array(), $ignored,
					$plugin.' returns a key the results page never reads, so whatever it holds reaches no page.');
			}
		}

		/**
		 * The forum search selects no thread_parent, so a row carrying one anyway must still be titled from the thread name beside it.
		 *
		 * @see https://github.com/e107inc/e107/issues/6326
		 */
		public function testForumResultIsTitledWithTheThreadTheRowBelongsTo()
		{
			e107::coreLan('search');

			$rows = $this->searchAddonRows();
			$res = $this->compileSearchAddon('forum', $rows['forum'] + array('thread_parent' => 1));

			self::assertStringEndsWith(' | '.$rows['forum']['thread_name'], $res['title'],
				'A forum result must be titled with the thread its post belongs to, including on a row that carries a thread_parent.');
		}

		/**
		 * The author's own meta description is what a custom page result is summarised from.
		 *
		 * @see https://github.com/e107inc/e107/issues/6421
		 */
		public function testCustomPageSummaryComesFromItsMetaDescription()
		{
			e107::coreLan('search');

			$rows = $this->searchAddonRows();
			$res = $this->compileQueriedRow('page', $rows['page']);

			self::assertSame($rows['page']['page_metadscr'], $res['summary'],
				'A custom page with a meta description must be summarised from it rather than from its body.');
		}

		/**
		 * @see https://github.com/e107inc/e107/issues/6421
		 */
		public function testCustomPageWithoutAMetaDescriptionSummarisesFromTheBody()
		{
			e107::coreLan('search');

			$rows = $this->searchAddonRows();
			$row = $rows['page'];
			$row['page_metadscr'] = '';
			$res = $this->compileQueriedRow('page', $row);

			self::assertSame($rows['page']['page_text'], $res['summary'],
				'A custom page that left its meta description empty must still be summarised from its body.');
		}
	}
