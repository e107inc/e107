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
 * How a thread's name renders, and the thread-name shortcodes that only a theme's template reaches.
 */
class forumThreadNameTest extends \Codeception\Test\Unit
{
	use \Test\ForumRows;

	protected function _before()
	{
		e107::lan('forum', 'front', true);

		require_once(e_PLUGIN.'forum/forum_class.php');
	}

	protected function _after()
	{
		$this->dropForumRows();
	}

	public function testTheReplyFormCaptionPrintsAStoredNameAsText()
	{
		require_once(e_PLUGIN.'forum/shortcodes/batch/post_shortcodes.php');

		/** @var plugin_forum_post_shortcodes $sc */
		$sc = $this->make('plugin_forum_post_shortcodes');
		$sc->setVars(array(
			'action' => 'rp', 'thread_id' => 1, 'thread_name' => $this->storedMarkupName(), 'forum_sef' => 'a-forum',
		));

		$this->assertNamePrintedAsText($sc->sc_forum_post_caption());
	}

	public function testTheLastPostNamePrintsAStoredNameAsText()
	{
		global $forum;

		$this->haveForumTables();
		$category = $this->haveForum('e107help probe category');
		$forumId = $this->haveForum('e107help probe forum', $category);
		$thread = $this->haveForumThread($this->storedMarkupName(), $forumId);
		$this->haveForumPost('e107help probe post', $thread, $forumId);

		require_once(e_PLUGIN.'forum/shortcodes/batch/forum_shortcodes.php');

		/** @var forum_shortcodes $sc */
		$sc = $this->make('forum_shortcodes');
		$sc->setVars(array(
			'forum_lastpost_info' => time().'.'.$thread, 'forum_lastpost_user' => 1, 'forum_lastpost_user_anon' => '',
			'forum_sef' => 'e107help-probe-forum', 'user_name' => 'e107help probe',
		));

		$forumWas = $forum;
		$forum = new e107forum();

		try
		{
			$this->assertNamePrintedAsText($sc->sc_lastpost(array('type' => 'name')));
		}
		finally
		{
			$forum = $forumWas;
		}
	}

	public function testANamesInlineBbcodeRenders()
	{
		$html = e107forum::threadNameHtml('[b]bold[/b] [i]italic[/i] [u]underlined[/u] [color=red]red[/color] [center][b]centred[/b][/center]', e107::getParser(), e107::getBB());

		self::assertSame(1, preg_match("#^<strong class='bbcode bold[^']*'>bold</strong> <em class='bbcode italic[^']*'>italic</em> <span class='bbcode underline[^']*' style='text-decoration:underline'>underlined</span> <span class='[^']*' style='color:\\#FF0000'>red</span> <strong class='bbcode bold[^']*'>centred</strong>$#", $html), $html);
	}

	/**
	 * @dataProvider inlineBbcodeProvider
	 */
	public function testEachCodeOfTheInlineSetRendersInAName($name, $pattern)
	{
		$html = e107forum::threadNameHtml($name, e107::getParser(), e107::getBB());

		self::assertSame(1, preg_match($pattern, $html), $html);
	}

	public function inlineBbcodeProvider()
	{
		return array(
			'size'      => array('[size=20]a[/size]', "#^<span class='bbcode-size' style='font-size:20px'>a</span>$#"),
			'spoiler'   => array('[spoiler]a[/spoiler]', "#^<span class='bbcode-spoiler' [^>]*>a</span>$#"),
			'time'      => array('[time]1700000000[/time]', "#^<span class='bbcode-time'>[^<]*2023[^<]*</span>$#"),
			'br'        => array('line[br]break', '#^line<br />break$#'),
			'sanitised' => array('[sanitised]a[/sanitised]', '#^<span class="sanitised">[^<]*a[^<]*</span>$#'),
		);
	}

	/**
	 * @dataProvider strippedBbcodeProvider
	 */
	public function testANamesBbcodeOutsideTheInlineSetIsStripped($name, $text)
	{
		self::assertSame($text, e107forum::threadNameHtml($name, e107::getParser(), e107::getBB()));
	}

	public function strippedBbcodeProvider()
	{
		return array(
			'tables'            => array('[table][tbody][tr][th]a[/th][td]b[/td][/tr][/tbody][/table]', 'ab'),
			'markup and code'   => array('[html]a[/html] [php]b[/php] [code]c[/code] [scode]d[/scode]', 'a b c d'),
			'hidden content'    => array('[hide]a[/hide]', 'a'),
			'layout blocks'     => array('[center]a[/center] [left]b[/left] [right]c[/right] [justify]d[/justify] [block]e[/block] [p]f[/p] [h=2]g[/h] [alert]h[/alert]', 'a b c d e f g h'),
			'quotes and lists'  => array('[quote=someone]a[/quote] [blockquote]b[/blockquote] [list]c[/list]', 'a b c'),
			'form fields'       => array('[textarea]a[/textarea]', 'a'),
			'images and icons'  => array('[img]a.png[/img] [glyph]b[/glyph]', 'a.png b'),
			'video and embeds'  => array('[video]a[/video] [youtube]b[/youtube] [flash]c.swf[/flash] [stream]d.wmv[/stream]', 'a b c.swf d.wmv'),
			'file links'        => array('[file=a.zip]b[/file]', 'b'),
			'links'             => array('[link=https://example.com/a]a[/link] [url]https://example.com/b[/url] [email]c@example.com[/email]', 'a https://example.com/b c@example.com'),
		);
	}

	public function testAMentionOfAStrippedCodeThatNothingClosesPrintsAsTyped()
	{
		self::assertSame('Problem with [img] tags', e107forum::threadNameHtml('Problem with [img] tags', e107::getParser(), e107::getBB()));
		self::assertSame('Where did [/quote] come from?', e107forum::threadNameHtml('Where did [/quote] come from?', e107::getParser(), e107::getBB()));
	}

	public function testAPluginsBbcodeIsStrippedFromAName()
	{
		$this->getModule('\Helper\Unit')->writeAppFile('e107_plugins/forumthreadnamebbcode/fixture.bb', "return '<div class=\"forum-thread-name-fixture\">'.\$code_text.'</div>';");
		$this->getModule('\Helper\Unit')->writeAppFile('e107_plugins/forumthreadnamebbcode/_fixturesingle.bb', "return '<hr class=\"forum-thread-name-fixture\">';");
		$this->getModule('\Helper\Unit')->writeAppFile('e107_plugins/forumthreadnamebbcode/b.bb', "return '<div class=\"forum-thread-name-fixture\">'.\$code_text.'</div>';");

		$config = e107::getConfig();
		$listWas = $config->get('bbcode_list');
		$bbWas = e107::getBB();

		$config->set('bbcode_list', array('forumthreadnamebbcode' => array('fixture' => '0', '_fixturesingle' => '0', 'b' => '0')));
		e107::setRegistry('core/e107/singleton/e_bbcode', new e_bbcode());

		try
		{
			$tp = e107::getParser();

			self::assertSame('<div class="forum-thread-name-fixture">a</div> x <hr class="forum-thread-name-fixture"> y <div class="forum-thread-name-fixture">b</div>', $tp->toHTML('[fixture]a[/fixture] x [fixturesingle] y [b]b[/b]', true, 'USER_TITLE'));
			self::assertSame('a', e107forum::threadNameHtml('[fixture]a[/fixture]', $tp, e107::getBB()));
			self::assertSame('x  y', e107forum::threadNameHtml('x [fixturesingle] y', $tp, e107::getBB()));
			self::assertSame('a', e107forum::threadNameHtml('[_fixturesingle]a[/_fixturesingle]', $tp, e107::getBB()));
			self::assertSame('b', e107forum::threadNameHtml('[b]b[/b]', $tp, e107::getBB()));
		}
		finally
		{
			$config->set('bbcode_list', $listWas);
			e107::setRegistry('core/e107/singleton/e_bbcode', $bbWas);
		}
	}
}
