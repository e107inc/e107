<?php

/**
 * Text that toDB() or a bbcode stores percent-escaped comes out of toForm() as text, not as markup.
 */
class ToFormPercentEscapeCest
{
	public function _after(AcceptanceTester $I)
	{
		$I->dropForumProbe();
	}

	public function theForumEditFormPrintsAMembersImageCaptionAsText(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$ids = $I->haveForumStructure();
		$I->haveForumMember('tfcaptioner');
		$I->purgeForumPermCache();
		$I->loginToForum('tfcaptioner');
		$token = $I->grabForumToken('/e107_plugins/forum/forum_post.php?f=rp&id='.$ids['threadA']);
		$I->sendPostRequest('/e107_plugins/forum/forum_post.php?f=rp&id='.$ids['threadA'], array(
			'post' => 'Caption [img figcaption=%3C/textarea%3E%3Cb data-tfud=1%3Ex%3C/b%3E]https://example.com/x.jpg[/img] end',
			'reply' => 'Submit', 'e-token' => $token,
		));
		$post = $I->grabFromDatabase('e107_forum_post', 'post_id', array('post_thread' => $ids['threadA'], 'post_entry like' => 'Caption%'));
		$entry = $I->grabFromDatabase('e107_forum_post', 'post_entry', array('post_id' => $post));
		$I->assertStringContainsString('%3C%2Ftextarea%3E', $entry);
		$I->logoutFromForum();
		$I->loginAsAdmin();

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=edit&id='.$ids['threadA'].'&post='.$post);

		$I->seeInSource('data-tfud');
		$I->dontSeeInSource('</textarea><b');
	}

	public function theCommentPagePrintsANewsTitlesEscapesAsText(AcceptanceTester $I)
	{
		$news = $I->haveInDatabase('e107_news', \Test\Rows::news(array(
			'news_title'     => 'Title %3C/title%3E%3Cb data-tfud=1%3E',
			'news_body'      => 'body',
			'news_datestamp' => time() - 60,
			'news_author'    => 1,
			'news_category'  => 1,
			'news_sef'       => 'tofrom-percent-title',
		)));

		$I->amOnPage('/comment.php?comment.news.'.$news);

		$I->seeInSource('&lt;/title&gt;&lt;b data-tfud=1&gt;');
		$I->dontSeeInSource('</title><b data-tfud=1>');
	}
}
