<?php

/**
 * comment.php's AJAX answers, opened as a page and as core's comment script reads them.
 */
class CommentAjaxFlowCest
{
	/** @var int news item with news_allow_comments 0, which is comments on */
	private $news;

	public function _before(WebDriverTester $I)
	{
		$I->haveSitePrefUntilAfter('allowCommentEdit', 1);

		$this->news = $I->haveInDatabase('e107_news', \Test\Rows::news(array(
			'news_title'          => 'csgr news',
			'news_sef'            => 'csgr-news',
			'news_body'           => 'fixture',
			'news_datestamp'      => time() - 3600,
			'news_author'         => 1,
			'news_allow_comments' => 0,
		)));

		$I->amSignedInAs();
	}

	public function _after(WebDriverTester $I)
	{
		$I->restoreSitePrefs();
		$I->dropForumProbe();
	}

	public function aLinkToAnEmptySubmitRunsNoScript(WebDriverTester $I)
	{
		$I->amOnPage('/comment.php?ajax_used=1&mode=submit&x='.rawurlencode('<img src=x onerror=top.__csgr=1>'));

		$I->waitForJS('return document.readyState === "complete";', 10);
		$I->assertNull($I->executeJS('return window.__csgr === undefined ? null : window.__csgr;'));
	}

	public function aCommentStillPostsFromTheForm(WebDriverTester $I)
	{
		$I->amOnPage('/news.php?extend.'.$this->news);
		$I->fillField('#e-comment-form textarea[name=comment]', 'csgr from the form');
		$I->click('#e-comment-form .e-comment-submit');

		$I->waitForText('csgr from the form', 10, '#comments-container-news');
		$I->seeInDatabase('e107_comments', array('comment_comment' => 'csgr from the form', 'comment_item_id' => $this->news, 'comment_pid' => 0));
	}

	public function aReplyStillOpensAndPosts(WebDriverTester $I)
	{
		$parent = $this->haveComment($I, 'csgr parent', 0);
		$I->amOnPage('/news.php?extend.'.$this->news);
		$I->click('#e-comment-reply-'.$parent);

		$I->waitForElementVisible('#e-comment-form-reply textarea[name=comment]', 10);
		$I->fillField('#e-comment-form-reply textarea[name=comment]', 'csgr reply');
		$I->click('#e-comment-form-reply .e-comment-submit');

		$I->waitForText('csgr reply', 10, '#comments-container-news');
		$I->seeInDatabase('e107_comments', array('comment_comment' => 'csgr reply', 'comment_pid' => $parent));
	}

	public function anEditStillSaves(WebDriverTester $I)
	{
		$comment = $this->haveComment($I, 'csgr before edit', 0);
		$I->amOnPage('/news.php?extend.'.$this->news);
		$I->click('#e-comment-edit-'.$comment);

		$I->waitForElementVisible('#e-comment-edit-save-'.$comment, 10);
		$I->executeJS('document.getElementById("comment-'.$comment.'-edit").textContent = "csgr after edit";');
		$I->click('#e-comment-edit-save-'.$comment);

		$I->waitForElement('div.e-comment-edit-success', 10);
		$I->seeInDatabase('e107_comments', array('comment_id' => $comment, 'comment_comment' => 'csgr after edit'));
	}

	public function aDeletionStillHidesTheComment(WebDriverTester $I)
	{
		$comment = $this->haveComment($I, 'csgr doomed', 0);
		$I->amOnPage('/news.php?extend.'.$this->news);
		$I->click('#e-comment-delete-'.$comment);

		$I->waitForElementNotVisible('#comment-'.$comment, 10);
		$I->seeInDatabase('e107_comments', array('comment_id' => $comment, 'comment_blocked' => 1));
	}

	public function anApprovalStillMarksTheComment(WebDriverTester $I)
	{
		$comment = $this->haveComment($I, 'csgr pending', 2);
		$I->amOnPage('/news.php?extend.'.$this->news);
		$I->click('#e-comment-approve-'.$comment);

		$I->waitForElementNotVisible('#e-comment-approve-'.$comment, 10);
		$I->seeInDatabase('e107_comments', array('comment_id' => $comment, 'comment_blocked' => 0));
	}

	public function aDashboardApprovalStillHidesTheComment(WebDriverTester $I)
	{
		$I->haveSitePrefUntilAfter('adminstyle', 'infopanel');
		$comment = $this->haveComment($I, 'csgr pending on the dashboard', 2);
		$I->amOnPage('/e107_admin/admin.php');
		$I->click('button[data-comment-action="approve"][data-comment-id="'.$comment.'"]');

		$I->waitForElementNotVisible('#comment-'.$comment, 10);
		$I->seeInDatabase('e107_comments', array('comment_id' => $comment, 'comment_blocked' => 0));
	}

	/**
	 * @param WebDriverTester $I
	 * @param string $text
	 * @param int $blocked comment_blocked, where 2 is awaiting approval
	 * @return int comment id
	 */
	private function haveComment(WebDriverTester $I, $text, $blocked)
	{
		return $I->haveInDatabase('e107_comments', \Test\Rows::comment(array(
			'comment_item_id'     => $this->news,
			'comment_subject'     => 'csgr subject',
			'comment_author_id'   => 1,
			'comment_author_name' => 'admin',
			'comment_datestamp'   => time() - 1800,
			'comment_comment'     => $text,
			'comment_blocked'     => $blocked,
			'comment_ip'          => '127.0.0.1',
		)));
	}
}
