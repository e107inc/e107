<?php

/**
 * Regression tests for GHSA-72q5-94gw-prww: admin_ui dispatchers accepted any
 * state-changing etrigger_ submit that simply omitted the e-token field.
 *
 * e_session::check() only ever rejected a token that was PRESENT and wrong, so
 * a cross-site form that carried no token at all sailed through, and
 * ?ajax_used=1 downgraded even the legacy per-file guards from die() to a
 * silently discarded return value.
 *
 * The scenarios below post from a real HTML form served by the site itself,
 * because a genuine cross-site request carries no XMLHttpRequest header and
 * therefore still reaches the trigger loop. Two dispatchers are covered
 * deliberately: links.php carries the legacy per-file guard, so the refusal
 * happens in class2.php, while wmessage.php has no guard at all, so only the
 * new e_admin_controller::checkRequestToken() stands between the forgery and
 * the database.
 */
class AdminCsrfTriggerCest
{
	/** @var string PoC form written into the docroot, removed in _after() */
	private $pocPath = 'ghsa72q5_poc.html';

	/** @var string marker written into every row these tests try to create */
	private $marker;

	public function _before(AcceptanceTester $I)
	{
		if(empty($this->marker))
		{
			// Unique per run, so a row left behind by an earlier run can never
			// satisfy one of the assertions below.
			$this->marker = 'GHSA72Q5 '.substr(md5(uniqid('', true)), 0, 8);
		}

		$this->loginAsAdmin($I);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteAppFile($this->pocPath);
	}

	// -----------------------------------------------------------------------
	// links.php: the reporter's proof of concept
	// -----------------------------------------------------------------------

	public function tokenlessTriggerCreatesNoLink(AcceptanceTester $I)
	{
		$I->wantTo('refuse a tokenless etrigger_ POST to links.php (GHSA-72q5-94gw-prww)');

		$name = $this->marker.' tokenless';

		$this->postPoc($I, '/e107_admin/links.php?mode=main&action=create', $this->linkFields($name));

		$I->dontSeeInDatabase('e107_links', array('link_name' => $name));
	}

	public function wrongTokenCreatesNoLink(AcceptanceTester $I)
	{
		$I->wantTo('refuse an etrigger_ POST to links.php carrying a wrong token');

		$name   = $this->marker.' wrong token';
		$fields = $this->linkFields($name);
		$fields['e-token'] = 'e0e0e0e0e0e0e0e0e0e0e0e0e0e0e0e0';

		$this->postPoc($I, '/e107_admin/links.php?mode=main&action=create', $fields);

		$I->dontSeeInDatabase('e107_links', array('link_name' => $name));
	}

	public function validTokenCreatesTheLink(AcceptanceTester $I)
	{
		$I->wantTo('still create a link when the etrigger_ POST carries a valid token');

		$name   = $this->marker.' valid token';
		$fields = $this->linkFields($name);
		$fields['e-token'] = $this->grabToken($I, '/e107_admin/links.php?mode=main&action=create');

		$this->postPoc($I, '/e107_admin/links.php?mode=main&action=create', $fields);

		$I->seeInDatabase('e107_links', array('link_name' => $name));
	}

	/**
	 * ?ajax_used=1 used to turn class2.php's die() into a discarded return
	 * value, so every legacy per-file guard could be stepped around from the
	 * query string. The refusal is now a 403 that ends the request.
	 */
	public function ajaxUsedDoesNotBypassTheGuard(AcceptanceTester $I)
	{
		$I->wantTo('refuse a wrong-token POST that adds ?ajax_used=1 (GHSA-72q5-94gw-prww)');

		$name   = $this->marker.' ajax bypass';
		$fields = $this->linkFields($name);
		$fields['e-token'] = 'e0e0e0e0e0e0e0e0e0e0e0e0e0e0e0e0';

		$this->postPoc($I, '/e107_admin/links.php?mode=main&action=create&ajax_used=1', $fields);

		$I->seeResponseCodeIs(403);
		$I->see('Unauthorized access!');
		$I->dontSeeInDatabase('e107_links', array('link_name' => $name));
	}

	// -----------------------------------------------------------------------
	// wmessage.php: no per-file guard, so the dispatcher check is the boundary
	// -----------------------------------------------------------------------

	public function tokenlessTriggerOnAnUnguardedDispatcherCreatesNothing(AcceptanceTester $I)
	{
		$I->wantTo('refuse a tokenless etrigger_ POST to an admin page with no legacy guard');

		$title = $this->marker.' unguarded tokenless';

		$this->postPoc($I, '/e107_admin/wmessage.php?mode=main&action=create', $this->wmessageFields($title));

		// The refusal now comes from the global rule in class2.php, which answers
		// before the dispatcher runs, so it is the bare 403 rather than the
		// dispatcher's own message. Either way nothing is written.
		$I->seeResponseCodeIs(403);
		$I->see('Unauthorized access!');
		$I->dontSeeInDatabase('e107_generic', array('gen_ip' => $title));
	}

	public function validTokenOnAnUnguardedDispatcherStillCreates(AcceptanceTester $I)
	{
		$I->wantTo('still create a welcome message when the etrigger_ POST carries a valid token');

		$title  = $this->marker.' unguarded valid';
		$fields = $this->wmessageFields($title);
		$fields['e-token'] = $this->grabToken($I, '/e107_admin/wmessage.php?mode=main&action=create');

		$this->postPoc($I, '/e107_admin/wmessage.php?mode=main&action=create', $fields);

		$I->seeInDatabase('e107_generic', array('gen_ip' => $title));
	}

	// -----------------------------------------------------------------------
	// Helpers
	// -----------------------------------------------------------------------

	/**
	 * Serve an attacker-controlled form from the site and submit it.
	 *
	 * A plain form POST is what a cross-site forgery looks like: no
	 * XMLHttpRequest header, so e_AJAX_REQUEST stays false and the request
	 * reaches the admin_ui trigger loop.
	 *
	 * @param AcceptanceTester $I
	 * @param string $action form action, relative to the docroot
	 * @param array $fields name => value pairs posted as hidden inputs
	 * @return void
	 */
	private function postPoc(AcceptanceTester $I, $action, array $fields)
	{
		$html = "<html><body><form id='poc' method='post' action='".htmlspecialchars($action, ENT_QUOTES)."'>";

		foreach($fields as $name => $value)
		{
			$html .= "<input type='hidden' name='".htmlspecialchars($name, ENT_QUOTES)
				."' value='".htmlspecialchars($value, ENT_QUOTES)."' />";
		}

		$html .= "</form></body></html>";

		$I->writeAppFile($this->pocPath, $html);
		$I->amOnPage('/'.$this->pocPath);
		$I->submitForm('#poc', array());
	}

	/**
	 * @param string $name link_name to create
	 * @return array
	 */
	private function linkFields($name)
	{
		return array(
			'link_name'        => $name,
			'link_url'         => 'https://example.invalid/'.md5($name),
			'link_description' => 'GHSA-72q5-94gw-prww regression',
			'link_button'      => '',
			'link_category'    => 1,
			'link_parent'      => 0,
			'link_open'        => 0,
			'link_class'       => 0,
			'link_order'       => 0,
			'link_sefurl'      => '',
			'link_rel'         => '',
			'link_function'    => '',
			'link_owner'       => 'core',
			'etrigger_submit'  => 'Create',
		);
	}

	/**
	 * @param string $title gen_ip (the welcome message title) to create
	 * @return array
	 */
	private function wmessageFields($title)
	{
		return array(
			'gen_type'        => 'wmessage',
			'gen_datestamp'   => time(),
			'gen_user_id'     => 1,
			'gen_ip'          => $title,
			'gen_intdata'     => 0,
			'gen_chardata'    => 'GHSA-72q5-94gw-prww regression',
			'etrigger_submit' => 'Create',
		);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $page admin page carrying a form with an e-token field
	 * @return string
	 */
	private function grabToken(AcceptanceTester $I, $page)
	{
		$I->amOnPage($page);
		$source = $I->grabPageSource();

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
