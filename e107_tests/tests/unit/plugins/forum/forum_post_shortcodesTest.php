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
 * Quoting a post fills the reply box with the post as its author wrote it,
 * bbcode included, inside a [quote] block naming the author. A [hide] block
 * goes into the box sealed, unless the quoter may see it.
 */
class forum_post_shortcodesTest extends \Test\Unit
{
	use \Test\HideGlobals;

	protected function _before()
	{
		e107::lan('forum', 'front', true);

		require_once(e_PLUGIN.'forum/forum_class.php');
		require_once(e_PLUGIN.'forum/shortcodes/batch/post_shortcodes.php');
	}

	public function testQuotingAPostKeepsItsBbcodeInsideAQuoteBlock()
	{
		self::assertStringContainsString("[quote=admin]\nA [b]plain[/b] reply to quote\n[/quote]",
			$this->quoteBox('A [b]plain[/b] reply to quote'));
	}

	public function testAQuoteNamesAnAuthorWhoseNameHoldsAPercentSign()
	{
		self::assertStringContainsString("[quote=100%s]\nA reply\n[/quote]", $this->quoteBox('A reply', false, '100%s'));
	}

	public function testAPostStoredAsHtmlIsQuotedAsItsSource()
	{
		self::assertStringContainsString("[quote=admin]\n&lt;p&gt;An &lt;b&gt;HTML&lt;/b&gt; reply&lt;/p&gt;\n[/quote]",
			$this->quoteBox('[html]<p>An <b>HTML</b> reply</p>[/html]'));
	}

	public function testHiddenBlocksAreSealedAndTheRestIsQuotedAsWritten()
	{
		$box = $this->quoteBox('Before [hide]FIRST[/hide] between [b]bold[/b] [hide=x]SECOND[/hide] after');

		self::assertSame(1, preg_match('#\[quote=admin\]\nBefore \[hide\]([\w.-]+)\[/hide\] between \[b\]bold\[/b\] \[hide\]([\w.-]+)\[/hide\] after\n\[/quote\]#', $box, $sealed), $box);
		self::assertSame('FIRST', $this->forum(true)->unsealHidden($sealed[1]));
		self::assertSame('SECOND', $this->forum(true)->unsealHidden($sealed[2]));
	}

	public function testABlockTooLongToSealIsLeftOut()
	{
		self::assertStringContainsString("[quote=admin]\nBefore  after\n[/quote]", $this->quoteBox('Before [hide]' . str_repeat('a', 7000) . '[/hide] after'));
	}

	public function testAQuoterWhoMaySeeAHiddenBlockQuotesItAsWritten()
	{
		self::assertStringContainsString("[quote=admin]\nBefore [hide=x]SECRET[/hide] after\n[/quote]",
			$this->quoteBox('Before [hide=x]SECRET[/hide] after', true));
	}

	/**
	 * @dataProvider hiddenBlocks
	 * @param string $entry
	 */
	public function testHiddenContentIsNotQuoted($entry)
	{
		$box = $this->quoteBox($entry);

		self::assertStringContainsString('[quote=admin]', $box);
		self::assertStringContainsString('Before', $box);
		self::assertStringNotContainsString('SECRET', $box);
	}

	/**
	 * @return array
	 */
	public function hiddenBlocks()
	{
		return array(
			'plain'                      => array('Before [hide]SECRET[/hide] after'),
			'upper case'                 => array('Before [HIDE]SECRET[/HIDE] after'),
			'mixed case and a parameter' => array('Before [Hide=foo]SECRET[/hIDE] after'),
			'numbered, across lines'     => array("Before\n[hide2]\nSECRET\n[/hide2]\nafter"),
			'a longer word'              => array('Before [hideme]SECRET[/hide] after'),
			'nested'                     => array('Before [hide][hide]a[/hide] SECRET[/hide] after'),
			'inside a table'             => array('Before [table][tr][td][hide]SECRET[/hide][/td][/tr][/table] after'),
			'stored as HTML'             => array('[html]<p>Before [hide]SECRET[/hide] after</p>[/html]'),
			'a code block never closed'  => array('Before [hide]SECRET [code]x[/hide] after'),
		);
	}

	/**
	 * Nothing the topic page hides is quoted, for posts put together at random from hide tags spelled every way the parser reads them.
	 */
	public function testNothingTheTopicPageHidesIsQuoted()
	{
		$tp = e107::getParser();
		$pieces = array('[html]', '[/html]', '[b][/b]', '[i]', '[/i]', '[code]', '[/code]');
		$containers = array(array('', ''), array('[html]<p>', '</p>[/html]'), array('', ' [html]<p>c</p>[/html]'), array('[table]', '[/table]'), array('[hidex]', '[/hide]'), array('[hide]o ', ' [/hide]'));
		$spell = function ($close) use ($pieces)
		{
			$tag = '[' . ($close ? '/' : '');

			foreach(array('h', 'i', 'd', 'e') as $letter)
			{
				$tag .= (mt_rand(0, 4) === 0 ? $pieces[mt_rand(0, count($pieces) - 1)] : '') . (mt_rand(0, 3) === 0 ? strtoupper($letter) : $letter);
			}

			$suffixes = array('', '', '2', '=a', ' x', 'me', '02');

			return $tag . $suffixes[mt_rand(0, count($suffixes) - 1)] . ']';
		};

		mt_srand(6573);
		$this->holdHideGlobals();

		try
		{
			for($i = 0; $i < 120; $i++)
			{
				$container = $containers[$i % count($containers)];
				$post = $container[0] . 'Before ' . $spell(false) . ' SECRET ' . $spell(true) . ' after' . $container[1];

				$this->allowHiddenContent(false);
				$hidden = $tp->toHTML($post, true);
				$this->allowHiddenContent(true);
				$shown = $tp->toHTML($post, true);
				$this->allowHiddenContent(false);

				if((strpos($hidden, 'SECRET') === false) && (strpos($shown, 'SECRET') !== false))
				{
					self::assertStringNotContainsString('SECRET', $this->quoteBox($post), $post);
				}
			}
		}
		finally
		{
			mt_srand();
			$this->restoreHideGlobals();
		}
	}

	/**
	 * @param bool $mayReadHidden
	 * @return e107forum
	 */
	private function forum($mayReadHidden)
	{
		return $this->make('e107forum', array('mayReadHidden' => $mayReadHidden, 'prefs' => e107::getPlugConfig('forum')));
	}

	/**
	 * @param string $entry
	 * @param bool   $mayReadHidden whether the quoter may see the post's [hide] blocks
	 * @param string $author
	 * @return string
	 */
	private function quoteBox($entry, $mayReadHidden = false, $author = 'admin')
	{
		$sc = new plugin_forum_post_shortcodes();
		$sc->setScVar('forum', $this->forum($mayReadHidden));
		$sc->setVars(array(
			'action'         => 'quote',
			'post_entry'     => $entry,
			'post_thread'    => 7,
			'post_id'        => 11,
			'user_name'      => $author,
			'post_user_anon' => '',
		));

		return $sc->sc_forum_post_textarea();
	}
}
