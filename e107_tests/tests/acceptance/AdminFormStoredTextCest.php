<?php

/**
 * Text another user stored with its markup encoded stays text in the admin edit form that loads it.
 */
class AdminFormStoredTextCest
{
	/** As toDB() stores the text, typed by an author, that closes an editor and adds an element. */
	const STORED = 'Look &lt;/textarea&gt;&lt;img id=n2q7c src=x onerror=alert(1)&gt; here';

	public function aMembersCommentStaysTextInTheCommentsManager(AcceptanceTester $I)
	{
		$author = $I->haveMember('n2q7cmember', 'N2q7cMemberPass');
		$id = $I->haveInDatabase('e107_comments', \Test\Rows::comment(array(
			'comment_item_id' => 1, 'comment_subject' => 'Re: news', 'comment_author_id' => $author,
			'comment_author_name' => 'n2q7cmember', 'comment_author_email' => 'n2q7cmember@example.com',
			'comment_datestamp' => time() - 60, 'comment_comment' => self::STORED, 'comment_ip' => '127.0.0.1',
		)));

		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/comment.php?mode=main&action=edit&id='.$id);
		$I->seeInSource('Look &lt;/textarea&gt;&lt;img id=n2q7c');
		$I->dontSeeElement('#n2q7c');
	}

	public function aNewsBodyStaysTextInTheNewsForm(AcceptanceTester $I)
	{
		$news = $I->haveInDatabase('e107_news', array(
			'news_title' => 'Stored text n2q7c', 'news_sef' => 'stored-text-n2q7c',
			'news_body' => self::STORED, 'news_extended' => '',
			'news_meta_title' => '', 'news_meta_keywords' => '', 'news_meta_description' => '',
			'news_datestamp' => time() - 3600, 'news_author' => 1, 'news_category' => 1,
			'news_allow_comments' => 0, 'news_start' => 0, 'news_end' => 0, 'news_class' => '0', 'news_render_type' => '0',
			'news_summary' => '', 'news_thumbnail' => '',
		));

		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/newspost.php?mode=main&action=edit&id='.$news);
		$I->seeInSource('Look &lt;/textarea&gt;&lt;img id=n2q7c');
		$I->dontSeeElement('#n2q7c');
	}

	public function aPageBodyStaysTextInThePageForm(AcceptanceTester $I)
	{
		$page = $I->haveInDatabase('e107_page', array(
			'page_title'     => 'Stored text n2q7c',
			'page_sef'       => 'stored-text-n2q7c',
			'page_chapter'   => 0,
			'page_text'      => self::STORED,
			'page_author'    => 1,
			'page_datestamp' => time(),
			'page_class'     => 0,
			'page_password'  => '',
			'page_template'  => 'default',
			'page_order'     => 0,
			'page_fields'    => '',
		));

		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/cpage.php?mode=page&action=edit&id='.$page);
		$I->seeInSource('Look &lt;/textarea&gt;&lt;img id=n2q7c');
		$I->dontSeeElement('#n2q7c');
	}
}
