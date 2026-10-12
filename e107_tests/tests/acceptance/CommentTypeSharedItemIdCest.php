<?php

/**
 * A news item and a password-protected page with the same id: the news item's comments are only its own.
 */
class CommentTypeSharedItemIdCest
{
	const READER_NAME = 'cmttypereader';

	const READER_PASSWORD = 'Fixture-Reader-Pass-1';

	/** @var int the id the news item and the page share */
	private $id;

	public function _before(AcceptanceTester $I)
	{
		$this->id = 1 + max(array_merge(array(0),
			$I->grabColumnFromDatabase('e107_news', 'news_id'),
			$I->grabColumnFromDatabase('e107_page', 'page_id')
		));

		$I->haveInDatabase('e107_news', array(
			'news_id'               => $this->id,
			'news_title'            => 'Fixture Shared Id News',
			'news_sef'              => 'fixture-shared-id-news',
			'news_body'             => 'Body of Fixture Shared Id News',
			'news_extended'         => '',
			'news_meta_keywords'    => '',
			'news_meta_description' => '',
			'news_datestamp'        => time() - 3600,
			'news_author'           => 1,
			'news_category'         => 1,
			'news_allow_comments'   => 0,
			'news_start'            => 0,
			'news_end'              => 0,
			'news_class'            => '0',
			'news_render_type'      => 0,
			'news_comment_total'    => 0,
			'news_summary'          => '',
			'news_thumbnail'        => '',
			'news_sticky'           => 0,
		));

		$I->haveInDatabase('e107_page', array(
			'page_id'           => $this->id,
			'page_title'        => 'Fixture Shared Id Page',
			'page_sef'          => 'fixture-shared-id-page',
			'page_text'         => 'Body of Fixture Shared Id Page',
			'page_author'       => 1,
			'page_datestamp'    => time() - 3600,
			'page_comment_flag' => 1,
			'page_password'     => 'fixture-secret',
			'page_class'        => 0,
			'page_template'     => '',
			'page_order'        => 1,
		));

		foreach(array('CMTTYPENEWS' => '0', 'CMTTYPEPAGE' => 'page', 'CMTTYPEPROFILE' => 'profile', 'CMTTYPEPLUGIN' => 'fixtureplugin') as $marker => $type)
		{
			$I->haveInDatabase('e107_comments', array(
				'comment_item_id'     => $this->id,
				'comment_subject'     => $marker,
				'comment_author_id'   => 1,
				'comment_author_name' => 'fixture',
				'comment_datestamp'   => time() - 3600,
				'comment_comment'     => 'Text of '.$marker,
				'comment_blocked'     => 0,
				'comment_ip'          => '127.0.0.1',
				'comment_type'        => $type,
				'comment_lock'        => 0,
				'comment_share'       => 0,
			));
		}

		$I->resetAllCookies();
	}

	public function theNewsItemsPagesListOnlyItsOwnComments(AcceptanceTester $I)
	{
		$I->amOnPage('/page.php?id='.$this->id);
		$I->dontSeeInSource('Text of CMTTYPEPAGE');

		foreach(array('/news.php?extend.'.$this->id, '/comment.php?comment.news.'.$this->id) as $page)
		{
			$I->amOnPage($page);
			$this->seeOnlyTheNewsComment($I);
		}
	}

	public function theAjaxCommentListOfTheNewsItemHoldsOnlyItsOwnComments(AcceptanceTester $I)
	{
		$this->loginAsReader($I);

		$I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
		$I->amOnPage('/comment.php?mode=list&type=news&id='.$this->id.'&from=0');
		$I->deleteHeader('X-Requested-With');

		$this->seeOnlyTheNewsComment($I);
	}

	public function aNewsCommentIsNotTakenForACopyOfAnotherTypesComment(AcceptanceTester $I)
	{
		$token = $this->loginAsReader($I);

		$I->sendAjaxPostRequest('/comment.php?ajax_used=1&mode=submit', array(
			'comment'       => 'Text of CMTTYPEPAGE',
			'subject'       => 'Fixture subject',
			'author_name'   => '',
			'pid'           => 0,
			'comment_share' => 0,
			'table'         => 'news',
			'itemid'        => $this->id,
			'e-token'       => $token,
		));

		$I->seeInDatabase('e107_comments', array('comment_comment' => 'Text of CMTTYPEPAGE', 'comment_type' => '0', 'comment_item_id' => $this->id));
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function seeOnlyTheNewsComment(AcceptanceTester $I)
	{
		$I->seeInSource('Text of CMTTYPENEWS');

		foreach(array('CMTTYPEPAGE', 'CMTTYPEPROFILE', 'CMTTYPEPLUGIN') as $marker)
		{
			$I->dontSeeInSource($marker);
		}
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string the session's e-token
	 */
	private function loginAsReader(AcceptanceTester $I)
	{
		$I->haveMember(self::READER_NAME, self::READER_PASSWORD);
		$I->loginAsMember(self::READER_NAME, self::READER_PASSWORD);

		return $I->grabForumToken('/comment.php?comment.news.'.$this->id);
	}
}
