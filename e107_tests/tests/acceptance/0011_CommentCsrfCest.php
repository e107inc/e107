<?php

/**
 * Regression tests for GHSA-m4hh-m278-jwg5: comment.php AJAX mutation endpoints
 * (mode=delete, mode=approve, mode=edit) must reject POSTs that omit the
 * e-token CSRF field.
 */
class CommentCsrfCest
{
	public function _before(AcceptanceTester $I)
	{
		$this->loginAsAdmin($I);
	}

	public function _after(AcceptanceTester $I)
	{
	}

	public function deleteRejectsForgedRequestWithoutToken(AcceptanceTester $I)
	{
		$I->wantTo("Reject comment.php?mode=delete POSTs that omit e-token");

		$commentId = $this->seedComment($I, 0);

		$I->sendAjaxPostRequest('/comment.php?ajax_used=1&mode=delete', array(
			'id'     => $commentId,
			'table'  => 'news',
			'itemid' => 1,
		));

		$I->seeInSource('"error":true');
		$I->seeInSource('Unauthorized access!');
		$I->seeInDatabase('e107_comments', array(
			'comment_id'      => $commentId,
			'comment_blocked' => 0,
		));
	}

	public function approveRejectsForgedRequestWithoutToken(AcceptanceTester $I)
	{
		$I->wantTo("Reject comment.php?mode=approve POSTs that omit e-token");

		$commentId = $this->seedComment($I, 2);

		$I->sendAjaxPostRequest('/comment.php?ajax_used=1&mode=approve', array(
			'itemid' => $commentId,
		));

		$I->seeInSource('"error":true');
		$I->seeInSource('Unauthorized access!');
		$I->seeInDatabase('e107_comments', array(
			'comment_id'      => $commentId,
			'comment_blocked' => 2,
		));
	}

	public function deleteAcceptsRequestWithValidToken(AcceptanceTester $I)
	{
		$I->wantTo("Accept comment.php?mode=delete POSTs that carry a valid e-token");

		$commentId = $this->seedComment($I, 0);
		$token     = $this->grabFreshToken($I);

		$I->sendAjaxPostRequest('/comment.php?ajax_used=1&mode=delete', array(
			'id'      => $commentId,
			'table'   => 'news',
			'itemid'  => 1,
			'e-token' => $token,
		));

		$I->seeInSource('"error":false');
		$I->seeInDatabase('e107_comments', array(
			'comment_id'      => $commentId,
			'comment_blocked' => 1,
		));
	}

	private function loginAsAdmin(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_admin/admin.php');
		$I->fillField('authname', \Helper\AdminLogin::ADMIN_USER);
		$I->fillField('authpass', \Helper\AdminLogin::ADMIN_PASS);
		$I->click('authsubmit');
	}

	private function seedComment(AcceptanceTester $I, $blocked)
	{
		return $I->haveInDatabase('e107_comments', array(
			'comment_pid'          => 0,
			'comment_item_id'      => 1,
			'comment_subject'      => 'CSRF regression seed',
			'comment_author_id'    => 0,
			'comment_author_name'  => 'Anon',
			'comment_author_email' => '',
			'comment_datestamp'    => time(),
			'comment_comment'      => 'csrf regression target',
			'comment_blocked'      => $blocked,
			'comment_ip'           => '127.0.0.1',
			'comment_type'         => 'news',
			'comment_lock'         => 0,
			'comment_share'        => 0,
		));
	}

	private function grabFreshToken(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_admin/comment.php');
		$source = $I->grabPageSource();
		if (!preg_match('/name=[\'"]e-token[\'"][^>]*value=[\'"]([^\'"]+)[\'"]/', $source, $matches))
		{
			throw new \RuntimeException('Could not locate an e-token on /e107_admin/comment.php');
		}
		return $matches[1];
	}
}