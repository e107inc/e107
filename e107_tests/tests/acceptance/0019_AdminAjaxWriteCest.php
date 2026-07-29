<?php

/**
 * Companion to 0018_AdminCsrfTriggerCest for GHSA-72q5-94gw-prww.
 *
 * Honouring e_session::check()'s return value in class2.php had to stop
 * forgeries without taking the admin's own Ajax writes with it. Two of those
 * post outside any form: x-editable inline editing and jQuery sortable drag
 * ordering. Neither used to send e-token, and on a page carrying the legacy
 * per-file guard the synthesised empty token now fails the check, so
 * admin.jquery.js was taught to attach the page's e-token to both payloads.
 *
 * These tests pin the resulting contract:
 *   - a page with no per-file guard keeps accepting a token-less inline edit,
 *     which is the proof the dispatcher check did not over-reach into Ajax;
 *   - a guarded page accepts both writes when the token travels with them,
 *     which is what the browser now does;
 *   - a guarded page refuses a drag-sort with no token, which is why
 *     admin.jquery.js had to change;
 *   - an unguarded page refuses one too, because SortAjaxPage() now checks the
 *     token itself rather than relying on the per-file guard;
 *   - the SEF URL generator, the third core Ajax poster, still answers on a
 *     guarded page now that it sends the token as well.
 */
class AdminAjaxWriteCest
{
	/** @var string unique per run, so a stale row can never satisfy an assertion */
	private $runId;

	public function _before(AcceptanceTester $I)
	{
		if(empty($this->runId))
		{
			$this->runId = substr(md5(uniqid('', true)), 0, 8);
		}

		$this->loginAsAdmin($I);
	}

	public function _after(AcceptanceTester $I)
	{
	}

	/**
	 * A tokenless inline edit used to be allowed on a page carrying no legacy
	 * guard, because check() only ever rejected a token that was present and
	 * wrong. The global rule refuses it now, on every page rather than only the
	 * guarded ones.
	 *
	 * This is a raw POST, so nothing runs the $.ajaxPrefilter that attaches the
	 * token in a real browser. That is deliberate: it is the forged request the
	 * rule exists to stop.
	 */
	public function inlineEditWithoutATokenIsRefusedOnAnUnguardedPage(AcceptanceTester $I)
	{
		$I->wantTo('refuse a token-less inline edit on an unguarded admin page');

		$id       = $this->seedWelcomeMessage($I);
		$newValue = 'inline unguarded '.$this->runId;
		$token    = $this->grabInlineToken($I, '/e107_admin/wmessage.php?mode=main&action=list');

		$I->sendAjaxPostRequest('/e107_admin/wmessage.php?mode=main&action=inline&id='.$id.'&ajax_used=1', array(
			'name'  => 'gen_ip',
			'value' => $newValue,
			'pk'    => $id,
			'token' => $token,
		));

		$I->seeResponseCodeIs(403);
		$I->dontSeeInDatabase('e107_generic', array('gen_id' => $id, 'gen_ip' => $newValue));
	}

	/**
	 * links.php does carry the guard, so the token has to travel with the
	 * payload. This is exactly what the x-editable params callback now sends.
	 */
	public function inlineEditWithATokenStillWorksOnAGuardedPage(AcceptanceTester $I)
	{
		$I->wantTo('keep accepting an inline edit that carries e-token on a guarded admin page');

		$id       = $this->seedLink($I, 'inline target');
		$newValue = 'inline guarded '.$this->runId;

		$I->amOnPage('/e107_admin/links.php?mode=main&action=list');
		$source  = $I->grabPageSource();
		$inline  = $this->extractInlineToken($source, '/e107_admin/links.php?mode=main&action=list');
		$eToken  = $this->extractFormToken($source, '/e107_admin/links.php?mode=main&action=list');

		$I->sendAjaxPostRequest('/e107_admin/links.php?mode=main&action=inline&id='.$id.'&ajax_used=1', array(
			'name'    => 'link_name',
			'value'   => $newValue,
			'pk'      => $id,
			'token'   => $inline,
			'e-token' => $eToken,
		));

		$I->seeInDatabase('e107_links', array('link_id' => $id, 'link_name' => $newValue));
	}

	public function dragSortWithATokenStillReordersRows(AcceptanceTester $I)
	{
		$I->wantTo('keep accepting a drag-sort that carries e-token on a guarded admin page');

		$first  = $this->seedLink($I, 'sort a');
		$second = $this->seedLink($I, 'sort b');
		$eToken = $this->grabFormToken($I, '/e107_admin/links.php?mode=main&action=list');

		$I->sendAjaxPostRequest('/e107_admin/links.php?mode=main&action=sort&ajax_used=1', array(
			'all'      => array('row-'.$first, 'row-'.$second),
			'linkid'   => 'row-'.$second,
			'neworder' => 1,
			'e-token'  => $eToken,
		));

		$this->seeOrderedBefore($I, $first, $second);

		// And the other way round, so the assertion cannot pass on stale data.
		$I->sendAjaxPostRequest('/e107_admin/links.php?mode=main&action=sort&ajax_used=1', array(
			'all'      => array('row-'.$second, 'row-'.$first),
			'linkid'   => 'row-'.$first,
			'neworder' => 1,
			'e-token'  => $eToken,
		));

		$this->seeOrderedBefore($I, $second, $first);
	}

	/**
	 * The counterpart of the test above: without the token the guarded page
	 * refuses, which is the behaviour change admin.jquery.js compensates for.
	 */
	public function dragSortWithoutATokenIsRefusedOnAGuardedPage(AcceptanceTester $I)
	{
		$I->wantTo('refuse a token-less drag-sort on a guarded admin page');

		$first  = $this->seedLink($I, 'unsorted a');
		$second = $this->seedLink($I, 'unsorted b');

		$before = array(
			$first  => $I->grabFromDatabase('e107_links', 'link_order', array('link_id' => $first)),
			$second => $I->grabFromDatabase('e107_links', 'link_order', array('link_id' => $second)),
		);

		$I->sendAjaxPostRequest('/e107_admin/links.php?mode=main&action=sort&ajax_used=1', array(
			'all'      => array('row-'.$second, 'row-'.$first),
			'linkid'   => 'row-'.$first,
			'neworder' => 1,
		));

		$I->seeResponseCodeIs(403);
		$I->seeInDatabase('e107_links', array('link_id' => $first, 'link_order' => $before[$first]));
		$I->seeInDatabase('e107_links', array('link_id' => $second, 'link_order' => $before[$second]));
	}

	/**
	 * users_extended.php carries no legacy per-file guard, so the global check
	 * in class2.php never sees a token at all and lets the request through.
	 * e_admin_ui::SortAjaxPage() is the only boundary left, and it has to hold:
	 * the reset query it starts with is an unconditional
	 * UPDATE <table> SET <sortField> = 999 WHERE 1.
	 */
	public function dragSortWithoutATokenIsRefusedOnAnUnguardedPage(AcceptanceTester $I)
	{
		$I->wantTo('refuse a token-less drag-sort on an unguarded admin page');

		$first  = $this->seedExtendedField($I, 'a', 10);
		$second = $this->seedExtendedField($I, 'b', 20);

		$I->sendAjaxPostRequest('/e107_admin/users_extended.php?mode=main&action=sort&ajax_used=1', array(
			'all'      => array('row-'.$second, 'row-'.$first),
			'linkid'   => 'row-'.$first,
			'neworder' => 1,
		));

		$I->seeResponseCodeIs(403);
		$I->seeInDatabase('e107_user_extended_struct', array('user_extended_struct_id' => $first, 'user_extended_struct_order' => 10));
		$I->seeInDatabase('e107_user_extended_struct', array('user_extended_struct_id' => $second, 'user_extended_struct_order' => 20));
	}

	/**
	 * Control for the test above, so its refusal cannot pass for the wrong
	 * reason. The same payload with the page's own token does reorder.
	 */
	public function dragSortWithATokenReordersRowsOnAnUnguardedPage(AcceptanceTester $I)
	{
		$I->wantTo('keep accepting a drag-sort that carries e-token on an unguarded admin page');

		$first  = $this->seedExtendedField($I, 'c', 30);
		$second = $this->seedExtendedField($I, 'd', 40);
		$eToken = $this->grabFormToken($I, '/e107_admin/users_extended.php?mode=main&action=list');

		$I->sendAjaxPostRequest('/e107_admin/users_extended.php?mode=main&action=sort&ajax_used=1', array(
			'all'      => array('row-'.$second, 'row-'.$first),
			'linkid'   => 'row-'.$first,
			'neworder' => 1,
			'e-token'  => $eToken,
		));

		$secondOrder = (int) $I->grabFromDatabase('e107_user_extended_struct', 'user_extended_struct_order', array('user_extended_struct_id' => $second));
		$firstOrder  = (int) $I->grabFromDatabase('e107_user_extended_struct', 'user_extended_struct_order', array('user_extended_struct_id' => $first));

		$I->assertLessThan($firstOrder, $secondOrder,
			'extended field '.$second.' should sort before extended field '.$first);
	}

	/**
	 * The .e-sef-generate button posts to the page it is displayed on, and
	 * newspost.php is guarded, so the payload has to carry e-token now.
	 * Handled in e107_admin/boot.php before the dispatcher runs.
	 */
	public function sefGenerateAnswersOnAGuardedPage(AcceptanceTester $I)
	{
		$I->wantTo('keep the SEF URL generator working on a guarded admin page');

		$eToken = $this->grabFormToken($I, '/e107_admin/newspost.php?mode=main&action=create');

		$I->sendAjaxPostRequest('/e107_admin/newspost.php?mode=main&action=create', array(
			'mode'    => 'sef',
			'source'  => 'Hello World '.$this->runId,
			'e-token' => $eToken,
		));

		$I->seeResponseCodeIs(200);

		$body    = $I->grabPageSource();
		$decoded = json_decode($body, true);

		$I->assertTrue(is_array($decoded) && !empty($decoded['converted']),
			'The SEF generator should answer with JSON, got: '.$body);
	}

	/**
	 * Control for the test above: the guard is still doing its job on that
	 * endpoint, so the positive case is not passing for a trivial reason.
	 */
	public function sefGenerateWithoutATokenIsRefusedOnAGuardedPage(AcceptanceTester $I)
	{
		$I->wantTo('refuse a token-less SEF URL generation on a guarded admin page');

		$I->sendAjaxPostRequest('/e107_admin/newspost.php?mode=main&action=create', array(
			'mode'   => 'sef',
			'source' => 'Hello World '.$this->runId,
		));

		$I->seeResponseCodeIs(403);
	}

	// -----------------------------------------------------------------------
	// Helpers
	// -----------------------------------------------------------------------

	private function seedExtendedField(AcceptanceTester $I, $label, $order)
	{
		return $I->haveInDatabase('e107_user_extended_struct', array(
			'user_extended_struct_name'       => 'ghsa72q5'.$label.$this->runId,
			'user_extended_struct_text'       => 'GHSA-72q5-94gw-prww sort '.$label,
			'user_extended_struct_type'       => 1,
			'user_extended_struct_parms'      => '',
			'user_extended_struct_values'     => '',
			'user_extended_struct_default'    => '',
			'user_extended_struct_read'       => 0,
			'user_extended_struct_write'      => 0,
			'user_extended_struct_required'   => 0,
			'user_extended_struct_signup'     => 0,
			'user_extended_struct_applicable' => 0,
			'user_extended_struct_order'      => $order,
			'user_extended_struct_parent'     => 0,
		));
	}

	private function seeOrderedBefore(AcceptanceTester $I, $earlier, $later)
	{
		$earlierOrder = (int) $I->grabFromDatabase('e107_links', 'link_order', array('link_id' => $earlier));
		$laterOrder   = (int) $I->grabFromDatabase('e107_links', 'link_order', array('link_id' => $later));

		$I->assertLessThan($laterOrder, $earlierOrder,
			'link '.$earlier.' should sort before link '.$later);
	}

	private function seedWelcomeMessage(AcceptanceTester $I)
	{
		return $I->haveInDatabase('e107_generic', array(
			'gen_type'      => 'wmessage',
			'gen_datestamp' => time(),
			'gen_user_id'   => 1,
			'gen_ip'        => 'ajax seed '.$this->runId,
			'gen_intdata'   => 0,
			'gen_chardata'  => 'GHSA-72q5-94gw-prww ajax regression',
		));
	}

	private function seedLink(AcceptanceTester $I, $label)
	{
		return $I->haveInDatabase('e107_links', array(
			'link_name'        => $label.' '.$this->runId,
			'link_url'         => 'https://example.invalid/'.md5($label.$this->runId),
			'link_description' => 'GHSA-72q5-94gw-prww ajax regression',
			'link_button'      => '',
			'link_category'    => 1,
			'link_order'       => 0,
			'link_parent'      => 0,
			'link_open'        => 0,
			'link_rel'         => '',
			'link_class'       => '0',
			'link_function'    => '',
			'link_sefurl'      => '',
			'link_owner'       => '',
		));
	}

	private function grabInlineToken(AcceptanceTester $I, $page)
	{
		$I->amOnPage($page);

		return $this->extractInlineToken($I->grabPageSource(), $page);
	}

	private function grabFormToken(AcceptanceTester $I, $page)
	{
		$I->amOnPage($page);

		return $this->extractFormToken($I->grabPageSource(), $page);
	}

	private function extractInlineToken($source, $page)
	{
		if(!preg_match('/data-token=[\'"]([^\'"]+)[\'"]/', $source, $matches))
		{
			throw new \RuntimeException('Could not locate an inline data-token on '.$page);
		}

		return $matches[1];
	}

	private function extractFormToken($source, $page)
	{
		if(!preg_match('/name=[\'"]e-token[\'"][^>]*value=[\'"]([^\'"]+)[\'"]/', $source, $matches))
		{
			throw new \RuntimeException('Could not locate an e-token on '.$page);
		}

		return $matches[1];
	}

	private function loginAsAdmin(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_admin/admin.php');
		$I->fillField('authname', 'admin');
		$I->fillField('authpass', 'admin');
		$I->click('authsubmit');
	}
}
