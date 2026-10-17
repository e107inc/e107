<?php

/**
 * Saving a comment in Admin > Comments recounts the comments of the news item it was left on, from that item's own comments.
 */
class CommentAdminNewsRecountCest
{
	const FORM = '#core-comments-form';

	/** @var int */
	private $newsId;

	/** @var int[] comment id by comment_type; every one is left on the id the news item has */
	private $comments = array();

	public function _before(AcceptanceTester $I)
	{
		$this->newsId = $I->haveInDatabase('e107_news', \Test\Rows::news(array(
			'news_title'          => 'Fixture Recount News',
			'news_sef'            => 'fixture-recount-news',
			'news_body'           => 'Body of Fixture Recount News',
			'news_datestamp'      => time() - 3600,
			'news_author'         => 1,
			'news_category'       => 1,
			'news_allow_comments' => 0,
			'news_comment_total'  => 7,
		)));

		foreach(array('0', 'page', 'profile', 'fixtureplugin') as $type)
		{
			$this->comments[$type] = $I->haveInDatabase('e107_comments', \Test\Rows::comment(array(
				'comment_item_id'     => $this->newsId,
				'comment_subject'     => 'Subject of '.$type,
				'comment_author_id'   => 1,
				'comment_author_name' => 'fixture',
				'comment_datestamp'   => time() - 60,
				'comment_comment'     => 'Text of '.$type,
				'comment_ip'          => '127.0.0.1',
				'comment_type'        => $type,
			)));
		}

		$I->loginAsAdmin();
	}

	public function savingANewsCommentCountsOnlyTheNewsItemsOwnComments(AcceptanceTester $I)
	{
		$this->saveComment($I, '0');

		$I->seeInDatabase('e107_news', array('news_id' => $this->newsId, 'news_comment_total' => 1));
	}

	public function savingAnotherTypesCommentLeavesTheNewsCountAlone(AcceptanceTester $I)
	{
		$this->saveComment($I, 'page');

		$I->seeInDatabase('e107_news', array('news_id' => $this->newsId, 'news_comment_total' => 7));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $type the comment_type of the fixture comment to save
	 * @return void
	 */
	private function saveComment(AcceptanceTester $I, $type)
	{
		$I->amOnPage('/e107_admin/comment.php?mode=main&action=edit&id='.$this->comments[$type]);
		$I->submitForm(self::FORM, array('comment_comment' => 'Edited text of '.$type), 'etrigger_submit');

		$I->seeInDatabase('e107_comments', array('comment_id' => $this->comments[$type], 'comment_comment' => 'Edited text of '.$type));
	}
}
