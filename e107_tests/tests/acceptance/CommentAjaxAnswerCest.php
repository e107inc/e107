<?php

/**
 * What comment.php's AJAX branch answers, and the content type it answers with.
 */
class CommentAjaxAnswerCest
{
	const MARKUP = '<img src=x onerror=alert(1)>';

	/** @var int news item with news_allow_comments 0, which is comments on */
	private $news;

	/** @var string */
	private $token;

	/** @var string what allowCommentEdit was before this test */
	private $allowCommentEdit;

	public function _before(AcceptanceTester $I)
	{
		$this->allowCommentEdit = $I->haveSitePref('allowCommentEdit', 1);

		$this->news = $I->haveInDatabase('e107_news', array(
			'news_title' => 'csgr news', 'news_sef' => 'csgr-news',
			'news_body' => 'fixture', 'news_extended' => '',
			'news_meta_title' => '', 'news_meta_keywords' => '', 'news_meta_description' => '',
			'news_datestamp' => time() - 3600, 'news_author' => 1, 'news_category' => 0,
			'news_allow_comments' => 0,
			'news_start' => 0, 'news_end' => 0, 'news_class' => '0', 'news_render_type' => '0',
			'news_summary' => '', 'news_thumbnail' => '',
		));

		$I->loginAsAdmin();
		$this->token = $this->grabCommentToken($I);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveSitePref('allowCommentEdit', $this->allowCommentEdit);
		$I->dropForumProbe();
	}

	public function anEmptySubmitAnswersWithItsMessageAlone(AcceptanceTester $I)
	{
		$I->amOnPage('/comment.php?ajax_used=1&mode=submit&x='.rawurlencode(self::MARKUP));

		$I->assertSame(array('error' => true, 'msg' => 'Please write something first.'), $this->grabJsonAnswer($I));
	}

	public function aSubmittedCommentIsAnsweredWithItsRenderedHtml(AcceptanceTester $I)
	{
		$I->sendAjaxPostRequest('/comment.php?ajax_used=1&mode=submit', array(
			'comment'       => 'csgr submitted',
			'subject'       => 'csgr subject',
			'author_name'   => '',
			'table'         => 'news',
			'itemid'        => $this->news,
			'pid'           => 0,
			'comment_share' => 0,
			'e-token'       => $this->token,
		));

		$answer = $this->grabJsonAnswer($I);
		$I->assertFalse($answer['error']);
		$I->assertStringContainsString('csgr submitted', $answer['html']);
		$I->seeInDatabase('e107_comments', array('comment_comment' => 'csgr submitted', 'comment_item_id' => $this->news));
	}

	public function aReplyIsAnsweredWithItsForm(AcceptanceTester $I)
	{
		$parent = $this->haveComment($I, 'csgr parent', 0);

		$I->sendAjaxPostRequest('/comment.php?ajax_used=1&mode=reply', array(
			'itemid'  => $parent,
			'table'   => 'news',
			'e-token' => $this->token,
		));

		$answer = $this->grabJsonAnswer($I);
		$I->assertFalse($answer['error']);
		$I->assertStringContainsString("id='e-comment-form-reply'", $answer['html']);
	}

	public function anEditIsAnsweredWithItsMessage(AcceptanceTester $I)
	{
		$comment = $this->haveComment($I, 'csgr before edit', 0);

		$I->sendAjaxPostRequest('/comment.php?ajax_used=1&mode=edit', array(
			'comment' => 'csgr after edit',
			'itemid'  => $comment,
			'e-token' => $this->token,
		));

		$answer = $this->grabJsonAnswer($I);
		$I->assertFalse($answer['error']);
		$I->seeInDatabase('e107_comments', array('comment_id' => $comment, 'comment_comment' => 'csgr after edit'));
	}

	public function anApprovalIsAnsweredWithItsMessage(AcceptanceTester $I)
	{
		$comment = $this->haveComment($I, 'csgr pending', 2);

		$I->sendAjaxPostRequest('/comment.php?ajax_used=1&mode=approve', array(
			'itemid'  => $comment,
			'e-token' => $this->token,
		));

		$answer = $this->grabJsonAnswer($I);
		$I->assertFalse($answer['error']);
		$I->seeInDatabase('e107_comments', array('comment_id' => $comment, 'comment_blocked' => 0));
	}

	public function aDeletionIsAnsweredWithItsMessage(AcceptanceTester $I)
	{
		$comment = $this->haveComment($I, 'csgr doomed', 0);

		$I->sendAjaxPostRequest('/comment.php?ajax_used=1&mode=delete', array(
			'id'      => $comment,
			'table'   => 'news',
			'itemid'  => $this->news,
			'e-token' => $this->token,
		));

		$answer = $this->grabJsonAnswer($I);
		$I->assertFalse($answer['error']);
		$I->seeInDatabase('e107_comments', array('comment_id' => $comment, 'comment_blocked' => 1));
	}

	/**
	 * @param AcceptanceTester $I
	 * @return array the answer, after asserting it was sent as JSON with markup escaped
	 */
	private function grabJsonAnswer(AcceptanceTester $I)
	{
		$I->assertStringStartsWith('application/json', (string) $I->grabHttpHeader('Content-Type'));

		$body = $I->grabResponseBody();
		$I->assertStringNotContainsString('<', $body, 'The answer carries markup unescaped.');

		$answer = json_decode($body, true);
		$I->assertTrue(is_array($answer), 'The answer is not a JSON object: '.$body);

		return $answer;
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string the token the comment form publishes, which is what the core script sends
	 */
	private function grabCommentToken(AcceptanceTester $I)
	{
		$I->amOnPage('/comment.php?comment.news.'.$this->news);
		$source = $I->grabPageSource();
		$form = strpos($source, "id='e-comment-form'");

		if ($form === false || !preg_match('/name=[\'"]e-token[\'"][^>]*value=[\'"]([^\'"]+)[\'"]/', (string) substr($source, $form), $m))
		{
			throw new \RuntimeException('The comment page served no comment form with a token');
		}

		return $m[1];
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $text
	 * @param int $blocked comment_blocked, where 2 is awaiting approval
	 * @return int comment id
	 */
	private function haveComment(AcceptanceTester $I, $text, $blocked)
	{
		return $I->haveInDatabase('e107_comments', array(
			'comment_pid' => 0, 'comment_item_id' => $this->news,
			'comment_subject' => 'csgr subject',
			'comment_author_id' => 1, 'comment_author_name' => 'admin',
			'comment_author_email' => '', 'comment_datestamp' => time() - 1800,
			'comment_comment' => $text, 'comment_blocked' => $blocked, 'comment_ip' => '127.0.0.1',
			'comment_type' => '0', 'comment_lock' => 0, 'comment_share' => 0,
		));
	}
}
