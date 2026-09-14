<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_searchTest extends \Codeception\Test\Unit
	{
		/** @var bool */
		private $multibyteBefore;

		/** @var array */
		private $globalsBefore;

		protected function _before()
		{
			require_once(e_HANDLER . 'search_class.php');

			$this->multibyteBefore = e107::getParser()->ustrlen('é') === 1;
			$this->globalsBefore = array();

			foreach($this->searchGlobals() as $name)
			{
				$this->globalsBefore[$name] = isset($GLOBALS[$name]) ? $GLOBALS[$name] : null;
			}
		}

		protected function _after()
		{
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
		}

		/**
		 * The page-scope globals {@see e_search::parsesearch()} reads instead of taking arguments.
		 */
		private function searchGlobals()
		{
			return array('query', 'search_prefs', 'pre_title', 'search_chars', 'search_res', 'result_flag');
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

			self::assertSame('Before alert(1) after', $this->toExcerptText('Before <script>alert(1)</script> after'),
				'toHTML() splits a script block out only when its opening tag carries an attribute, so an attribute-less one leaves its source standing as prose, as the old cleanup did.');
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
