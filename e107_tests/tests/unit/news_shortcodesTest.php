<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

class news_shortcodesTest extends \Codeception\Test\Unit
{
	const NEWS_ID = 64910;

	/** @var news_shortcodes */
	private $sc;

	/** @var mixed */
	private $savedCommentsIcon;

	/** @var int */
	private $commentId;

	protected function _before()
	{
		$this->savedCommentsIcon = e107::getConfig()->get('comments_icon');

		$this->commentId = e107::getDb()->insert('comments', array(
			'comment_item_id'     => self::NEWS_ID,
			'comment_type'        => '0',
			'comment_author_id'   => 1,
			'comment_author_name' => 'admin',
			'comment_datestamp'   => 1700000000,
			'comment_comment'     => 'first',
			'comment_blocked'     => 0,
		));

		require_once(e_CORE.'shortcodes/batch/news_shortcodes.php');
		$this->sc = new news_shortcodes();
	}

	protected function _after()
	{
		e107::getConfig()->set('comments_icon', $this->savedCommentsIcon);
		e107::getDb()->delete('comments', 'comment_id = '.(int) $this->commentId);
	}

	public function testNewsCommentsLinkRendersWithoutLastVisit()
	{
		e107::getConfig()->set('comments_icon', 1);

		$this->sc->setScVar('news_item', array(
			'news_id'             => self::NEWS_ID,
			'news_title'          => 'Commented item',
			'news_sef'            => 'commented-item',
			'news_comment_total'  => 1,
			'news_allow_comments' => 0,
		));
		$this->sc->setScVar('param', array('current_action' => 'list'));

		$result = $this->sc->sc_newscomments();

		$this->assertStringContainsString('>1</a>', $result);
	}

	/**
	 * @return array<string, array{0: string, 1: string}>
	 */
	public function generatedSummaryProvider()
	{
		return array(
			'paragraphs saved back to back'     => array('[html]<p>Alpha one is here.</p><p>Bravo two is here.</p><p>Charlie three is here.</p>[/html]', 'Alpha one is here.'),
			'paragraphs left unclosed'          => array('[html]Alpha one is here.<p>Bravo two is here.<p>Charlie three is here.[/html]', 'Alpha one is here.'),
			'upper-case paragraph tags'         => array('[html]<P>Alpha one is here.</P><P>Bravo two is here.</P>[/html]', 'Alpha one is here.'),
			'a first-level heading'             => array('[html]<h1>Alpha heading</h1>Bravo two is here.[/html]', 'Alpha heading'),
			'a sixth-level heading'             => array('[html]<h6>Alpha heading</h6>Bravo two is here.[/html]', 'Alpha heading'),
			'a centred block'                   => array('[center]Alpha centred[/center]Bravo two is here.', 'Alpha centred'),
			'a quotation'                       => array('[html]<blockquote>Alpha quoted</blockquote>Bravo two is here.[/html]', 'Alpha quoted'),
			'preformatted text'                 => array('[html]<pre>Alpha code</pre>Bravo two is here.[/html]', 'Alpha code'),
			'a list'                            => array('[html]<ul><li>Alpha one</li><li>Bravo two</li></ul>[/html]', 'Alpha one'),
			'a definition term'                 => array('[html]<dl><dt>Alpha term</dt></dl>Bravo two is here.[/html]', 'Alpha term'),
			'an indented definition'            => array('[html]<dl><dd>Alpha indented</dd></dl>Bravo two is here.[/html]', 'Alpha indented'),
			'a table header'                    => array('[html]<table><tr><th>Alpha head</th><th>Bravo head</th></tr></table>[/html]', 'Alpha head'),
			'a table'                           => array('[html]<table><tr><td>Alpha one</td><td>Bravo two</td></tr></table>[/html]', 'Alpha one'),
			'a horizontal rule'                 => array('[html]Alpha one is here<hr>Bravo two is here.[/html]', 'Alpha one is here'),
			'a self-closed line break'          => array('[html]<p>Alpha one is here<br/>Bravo two is here.</p>[/html]', 'Alpha one is here'),
			'a line break with a class'         => array('[html]<p>Alpha one is here<br class="bravo">Bravo two is here.</p>[/html]', 'Alpha one is here'),
			'a > inside an attribute'           => array('[html]<p title="alpha>bravo">Alpha one is here. Bravo two is here.</p>[/html]', 'Alpha one is here.'),
			'&nbsp; after a full stop'          => array('[html]<p>Alpha one is here.&nbsp; Bravo two is here.</p>[/html]', 'Alpha one is here.'),
			'&#160; after a full stop'          => array('[html]<p>Alpha one is here.&#160;Bravo two is here.</p>[/html]', 'Alpha one is here.'),
			'&#xa0; after a full stop'          => array('[html]<p>Alpha one is here.&#xa0;Bravo two is here.</p>[/html]', 'Alpha one is here.'),
			'&#XA0; after a full stop'          => array('[html]<p>Alpha one is here.&#XA0;Bravo two is here.</p>[/html]', 'Alpha one is here.'),
			'U+00A0 after a full stop'          => array("[html]<p>Alpha one is here.\xC2\xA0Bravo two is here.</p>[/html]", 'Alpha one is here.'),
			'a leading &nbsp; paragraph'        => array('[html]<p>&nbsp;</p><p>Alpha one is here. Bravo two is here.</p>[/html]', 'Alpha one is here.'),
			'a leading U+00A0 paragraph'        => array("[html]<p>\xC2\xA0</p>\n<p>Alpha one is here. Bravo two is here.</p>[/html]", 'Alpha one is here.'),
			'&nbsp; before an exclamation mark' => array('[html]<p>Alpha one&nbsp;! Bravo two is here.</p>[/html]', 'Alpha one&nbsp;!'),
			'&nbsp; inside the first sentence'  => array('[html]<p>Alpha 10&nbsp;km here. Bravo two is here.</p>[/html]', 'Alpha 10&nbsp;km here.'),
			'a tag that only starts like p'     => array('[html]<p>Alpha <progress>one</progress> is here. Bravo two is here.</p>[/html]', 'Alpha one is here.'),
			'ordinary sentences'                => array("[html]<p>Alpha <strong>one</strong> is here. Bravo two is here.</p>\n<p>Charlie three is here.</p>[/html]", 'Alpha one is here.'),
			'a bbcode line break'               => array("Alpha one is here\nBravo two is here.", 'Alpha one is here'),
		);
	}

	/**
	 * @dataProvider generatedSummaryProvider
	 * @param string $body
	 * @param string $expected
	 */
	public function testNewsSummaryGeneratedFromTheBodyEndsAtTheFirstBreak($body, $expected)
	{
		$this->sc->setScVar('news_item', array(
			'news_summary' => '',
			'news_body'    => $body,
		));

		$this->assertSame($expected, $this->sc->sc_news_summary());
	}

	public function testNewsSummaryReturnsATypedSummaryAsWritten()
	{
		$this->sc->setScVar('news_item', array(
			'news_summary' => 'Alpha typed.&nbsp;Bravo typed.',
			'news_body'    => '[html]<p>Charlie body.</p>[/html]',
		));

		$this->assertSame('Alpha typed.&nbsp;Bravo typed.', $this->sc->sc_news_summary());
	}
}
