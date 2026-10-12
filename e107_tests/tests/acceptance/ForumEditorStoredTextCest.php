<?php

/**
 * The forum's editor box gives a rich-text editor text stored as text, as text.
 */
class ForumEditorStoredTextCest
{
	const STORED = 'Look &lt;details open ontoggle=x&gt;y&lt;/details&gt; here a+b';

	/** @var array ids from Helper\ForumFixture::haveForumStructure() */
	private $ids;

	/** @var int the member's post */
	private $post;

	/** @var int the same text in a post of the administrator's, the only kind the editor is offered for on Edit */
	private $adminsPost;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$this->ids = $I->haveForumStructure();
		$this->post = $I->haveForumPost(self::STORED, $this->ids['threadA'], $this->ids['forumA'], $I->haveForumMember('tfeditor'));
		$this->adminsPost = $I->haveForumPost(self::STORED, $this->ids['threadA'], $this->ids['forumA'], 1);
		$I->purgeForumPermCache();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropForumProbe();
	}

	public function anAdministratorsEditBoxHoldsTheirPostAsStored(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=edit&id='.$this->ids['threadA'].'&post='.$this->adminsPost);

		$I->seeInSource('Look &amp;lt;details open ontoggle=x&amp;gt;y');
	}

	public function anAdministratorsQuoteBoxKeepsAMembersMarkupAsText(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=quote&id='.$this->ids['threadA'].'&post='.$this->post);

		$I->seeInSource('Look &amp;lt;details open ontoggle=x');
	}

	public function anAdministratorsQuoteOfAnHtmlPostKeepsItsAttributes(AcceptanceTester $I)
	{
		$post = $I->haveForumPost('[html]<p><a href=&quot;https://example.com/q&quot;>l</a></p>[/html]', $this->ids['threadA'], $this->ids['forumA'], 1);
		$I->loginAsAdmin();

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=quote&id='.$this->ids['threadA'].'&post='.$post);

		$I->seeInSource('&lt;a href="https://example.com/q"&gt;l&lt;/a&gt;');
	}

	public function anAdministratorsPreviewHoldsWhatTheEditorSent(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$url = '/e107_plugins/forum/forum_post.php?f=edit&id='.$this->ids['threadA'].'&post='.$this->adminsPost;
		$token = $I->grabForumToken($url);

		$I->sendPostRequest($url, array('post' => '[html]<p>Look &lt;details open ontoggle=x&gt;y&lt;/details&gt;</p>[/html]', 'fpreview' => 'Preview', 'e-token' => $token));

		$I->seeInSource('Look &amp;lt;details open ontoggle=x&amp;gt;y');
	}

	public function anAdministratorsHtmlPostReachesTheEditorAsBefore(AcceptanceTester $I)
	{
		$post = $I->haveForumPost('[html]<p>Mine &lt;b&gt;x&lt;/b&gt; <b>bold</b></p>[/html]', $this->ids['threadA'], $this->ids['forumA'], 1);
		$I->loginAsAdmin();

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=edit&id='.$this->ids['threadA'].'&post='.$post);

		$I->seeInSource('Mine &amp;lt;b&amp;gt;x&amp;lt;/b&amp;gt; &lt;b&gt;bold&lt;/b&gt;');
	}

	public function anAdministratorsEditBoxKeepsPercentEscapesInAnHtmlPostAsText(AcceptanceTester $I)
	{
		$post = $I->haveForumPost('[html]<p>Hi %3Cdetails open ontoggle=x%3Ey%3C/details%3E a+b</p>[/html]', $this->ids['threadA'], $this->ids['forumA'], 1);
		$I->loginAsAdmin();

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=edit&id='.$this->ids['threadA'].'&post='.$post);

		$I->seeInSource('Hi %3Cdetails open ontoggle=x%3Ey%3C/details%3E a+b');
	}

	public function aMembersOwnEditBoxShowsTheirTextAsTyped(AcceptanceTester $I)
	{
		$I->loginToForum('tfeditor');

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=edit&id='.$this->ids['threadA'].'&post='.$this->post);

		$I->seeInSource('Look &lt;details open ontoggle=x&gt;y&lt;/details&gt; here a+b');
		$I->dontSeeInSource('&amp;lt;details');
	}
}
