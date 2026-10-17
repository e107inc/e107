<?php

/**
 * An edited forum post is stored under the same rule as a new one, decided by the class of the post's author.
 */
class ForumEditStorageCest
{
	/** @var array ids from Helper\ForumFixture::haveForumStructure() */
	private $ids;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$this->ids = $I->haveForumStructure();
		$I->purgeForumPermCache();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropForumProbe();
	}

	public function aMembersEditedPostKeepsNoMarkup(AcceptanceTester $I)
	{
		$post = $I->haveForumPost('Original reply', $this->ids['threadA'], $this->ids['forumA'], $I->haveForumMember('tfeditor'));
		$I->loginToForum('tfeditor');

		$this->edit($I, $post, 'Edited text <details open data-feb=1 ontoggle=x>d</details>');

		$stored = $I->grabFromDatabase('e107_forum_post', 'post_entry', array('post_id' => $post));
		$I->assertStringStartsWith('Edited text', $stored);
		$I->assertStringNotContainsString('<details', $stored);
		$I->assertStringNotContainsString('ontoggle', $stored);
		$I->logoutFromForum();
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);
		$I->see('Edited');
		$I->dontSeeInSource('<details open data-feb=1');
	}

	public function anAdministratorsEditedPostKeepsTheirHtml(AcceptanceTester $I)
	{
		$post = $I->haveForumPost('Original reply', $this->ids['threadA'], $this->ids['forumA'], 1);
		$I->loginAsAdmin();

		$this->edit($I, $post, 'Edited <b data-feb=2>bold</b>');

		$I->assertStringContainsString('<b data-feb=', $I->grabFromDatabase('e107_forum_post', 'post_entry', array('post_id' => $post)));
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);
		$I->seeElement('b[data-feb="2"]');
	}

	public function anAdministratorsUnchangedSaveKeepsAMembersPostFreeOfMarkup(AcceptanceTester $I)
	{
		$post = $I->haveForumPost('Member &lt;img src=x data-feb=3 onerror=alert(1)&gt; end', $this->ids['threadA'], $this->ids['forumA'], $I->haveForumMember('tfeditor'));
		$wysiwyg = $I->haveSitePref('wysiwyg', '0');
		$I->loginAsAdmin();

		try
		{
			$this->saveUnchanged($I, $post);
		}
		finally
		{
			$I->haveSitePref('wysiwyg', $wysiwyg);
		}

		$I->assertStringNotContainsString('<img', $I->grabFromDatabase('e107_forum_post', 'post_entry', array('post_id' => $post)));
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);
		$I->dontSeeElement('img[data-feb]');
	}

	public function anAdministratorsSaveCleansAPostWhoseAuthorIsGone(AcceptanceTester $I)
	{
		$post = $I->haveForumPost('Gone <img src=x data-feb=5> end', $this->ids['threadA'], $this->ids['forumA'], 987654321);
		$I->loginAsAdmin();

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=edit&id='.$this->ids['threadA'].'&post='.$post);
		$I->dontSeeInSource('tinymce.min.js');
		$this->saveUnchanged($I, $post);

		$I->assertStringNotContainsString('<img', $I->grabFromDatabase('e107_forum_post', 'post_entry', array('post_id' => $post)));
	}

	public function aModeratorsSaveKeepsAnAdministratorsHtml(AcceptanceTester $I)
	{
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveForumMember('tfmod', '253,'.\Helper\ForumFixture::CLASS_MOD_A);
		$post = $I->haveForumPost('Admin <b data-feb=&quot;4&quot;>bold</b>', $this->ids['threadA'], $this->ids['forumA'], 1);
		$I->purgeForumPermCache();
		$I->loginToForum('tfmod');

		$this->saveUnchanged($I, $post);

		$I->assertSame('Admin <b data-feb=&quot;4&quot;>bold</b>', $I->grabFromDatabase('e107_forum_post', 'post_entry', array('post_id' => $post)));
	}

	public function theRichTextEditorIsOfferedOnlyForAnAuthorWhoMayPostHtml(AcceptanceTester $I)
	{
		$members = $I->haveForumPost('Member text', $this->ids['threadA'], $this->ids['forumA'], $I->haveForumMember('tfeditor'));
		$own = $I->haveForumPost('Admin text', $this->ids['threadA'], $this->ids['forumA'], 1);
		$I->loginAsAdmin();

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=edit&id='.$this->ids['threadA'].'&post='.$members);
		$I->dontSeeInSource('tinymce.min.js');
		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=edit&id='.$this->ids['threadA'].'&post='.$own);
		$I->seeInSource('tinymce.min.js');
	}

	public function aMembersUnchangedSavesKeepTheirCodeAsTyped(AcceptanceTester $I)
	{
		$typed = 'Code [code]a & b <i> "q" &nbsp; x[/code] end';
		$I->haveForumMember('tfcoder');
		$I->loginToForum('tfcoder');
		$page = '/e107_plugins/forum/forum_post.php?f=rp&id='.$this->ids['threadA'];
		$I->sendPostRequest($page, array('post' => $typed, 'reply' => 'Submit', 'e-token' => $I->grabForumToken($page)));
		$post = (int) $I->grabFromDatabase('e107_forum_post', 'post_id', array('post_entry like' => 'Code %'));
		$stored = $I->grabFromDatabase('e107_forum_post', 'post_entry', array('post_id' => $post));

		$I->assertSame($typed, $this->saveUnchanged($I, $post));
		$I->assertSame($stored, $I->grabFromDatabase('e107_forum_post', 'post_entry', array('post_id' => $post)));
		$this->saveUnchanged($I, $post);
		$I->assertSame($stored, $I->grabFromDatabase('e107_forum_post', 'post_entry', array('post_id' => $post)));
	}

	/**
	 * Open the edit form for $post and submit what its box holds, as a browser would.
	 *
	 * @param AcceptanceTester $I
	 * @param int $post
	 * @return string the box's value
	 */
	private function saveUnchanged(AcceptanceTester $I, $post)
	{
		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=edit&id='.$this->ids['threadA'].'&post='.$post);
		$box = $I->grabValueFrom('textarea[name=post]');
		$this->edit($I, $post, $box);

		return $box;
	}

	/**
	 * Submit the posting page's edit form for $post, as the signed-in user.
	 *
	 * @param AcceptanceTester $I
	 * @param int $post
	 * @param string $text
	 */
	private function edit(AcceptanceTester $I, $post, $text)
	{
		$url = '/e107_plugins/forum/forum_post.php?f=edit&id='.$this->ids['threadA'].'&post='.$post;
		$token = $I->grabForumToken($url);
		$I->sendPostRequest($url, array('post' => $text, 'update_reply' => 'Update', 'e-token' => $token));
	}
}
