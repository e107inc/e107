<?php

/**
 * Regression tests for GHSA-72q5-94gw-prww: an admin_ui trigger POST that
 * carries no e-token, or the wrong one, must not reach the trigger loop.
 *
 * The scenario is the reporter's proof of concept: e107_admin/links.php was one
 * of the 34 admin_ui pages with no token validation of any kind, so a POST of
 * link_* fields plus etrigger_submit=create created a site navigation link on
 * behalf of whichever administrator loaded the attacker's page.
 *
 * Rows this Cest makes the application create are named distinctly and left in
 * place: the acceptance suite reloads its dump before every run, and no public
 * actor action can delete them.
 */
class AdminUiCsrfCest
{
	const CREATE_PATH = '/e107_admin/links.php?mode=main&action=create';
	const LIST_PATH = '/e107_admin/links.php?mode=main&action=list';

	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
	}

	public function _after(AcceptanceTester $I)
	{
	}

	/**
	 * The forgery itself. No e-token anywhere in the request.
	 */
	public function tokenlessTriggerPostIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an admin_ui create trigger that carries no e-token');

		$I->sendPostRequest(self::CREATE_PATH, $this->linkFields('CsrfProbeNoToken'));

		$I->seeInSource('Unauthorized access!');
		$I->dontSeeInDatabase('e107_links', array('link_name' => 'CsrfProbeNoToken'));
	}

	public function wrongTokenTriggerPostIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an admin_ui create trigger that carries the wrong e-token');

		$fields = $this->linkFields('CsrfProbeWrongToken');
		$fields['e-token'] = str_repeat('0', 32);

		$I->sendPostRequest(self::CREATE_PATH, $fields);

		$I->seeInSource('Unauthorized access!');
		$I->dontSeeInDatabase('e107_links', array('link_name' => 'CsrfProbeWrongToken'));
	}

	/**
	 * The control. Without this the two tests above would also pass if the page
	 * had simply stopped working.
	 */
	public function validTokenTriggerPostCreatesTheRow(AcceptanceTester $I)
	{
		$I->wantTo('Accept an admin_ui create trigger that carries a valid e-token');

		$fields = $this->linkFields('CsrfProbeValidToken');
		$fields['e-token'] = $I->grabFreshAdminToken(self::CREATE_PATH);

		$I->sendPostRequest(self::CREATE_PATH, $fields);

		$I->seeInDatabase('e107_links', array(
			'link_name' => 'CsrfProbeValidToken',
			'link_url'  => 'news.php',
		));
	}

	/**
	 * e_AJAX_REQUEST is defined from isset($_REQUEST['ajax_used']), so an attacker
	 * sets it from the query string. class2.php turned the global die() into a
	 * bare return for such a request and then discarded the result, which defeated
	 * every one of the legacy per-file guards.
	 */
	public function ajaxUsedDoesNotDefeatTheCheck(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a wrong-token POST that sets ajax_used to dodge the check');

		$fields = $this->linkFields('CsrfProbeAjaxBypass');
		$fields['e-token'] = str_repeat('0', 32);

		$I->sendPostRequest(self::CREATE_PATH . '&ajax_used=1', $fields);

		$I->seeInSource('Unauthorized access!');
		$I->dontSeeInDatabase('e107_links', array('link_name' => 'CsrfProbeAjaxBypass'));
	}

	/**
	 * Inline editing posts through the same ajax_used route the bypass used, so
	 * this is the over-reach check: the admin area has to keep working.
	 */
	public function inlineEditStillWorks(AcceptanceTester $I)
	{
		$I->wantTo('Keep inline editing working with the token core now attaches');

		$linkId = $I->haveInDatabase('e107_links', $this->linkRow('CsrfProbeInlineBefore', 800));

		$I->amOnPage(self::LIST_PATH);
		$source = $I->grabPageSource();

		$I->sendPostRequest('/e107_admin/links.php?mode=main&action=inline&id=' . $linkId . '&ajax_used=1', array(
			'name'    => 'link_name',
			'value'   => 'CsrfProbeInlineAfter',
			'pk'      => $linkId,
			'token'   => $this->grabRowToken($source),
			'e-token' => $I->grabFreshAdminToken(self::LIST_PATH),
		));

		$I->seeInDatabase('e107_links', array(
			'link_id'   => $linkId,
			'link_name' => 'CsrfProbeInlineAfter',
		));
	}

	/**
	 * Drag-to-sort is the other tokenless AJAX route core relies on.
	 */
	public function dragSortStillWorks(AcceptanceTester $I)
	{
		$I->wantTo('Keep drag-to-sort working with the token core now attaches');

		$first  = $I->haveInDatabase('e107_links', $this->linkRow('CsrfProbeSortA', 900));
		$second = $I->haveInDatabase('e107_links', $this->linkRow('CsrfProbeSortB', 901));

		$I->sendPostRequest('/e107_admin/links.php?mode=main&action=sort&ajax_used=1&from=0', array(
			'all'      => array('row-' . $second, 'row-' . $first),
			'linkid'   => 'row-' . $second,
			'neworder' => 0,
			'e-token'  => $I->grabFreshAdminToken(self::LIST_PATH),
		));

		$I->assertLessThan(
			$I->grabFromDatabase('e107_links', 'link_order', array('link_id' => $first)),
			$I->grabFromDatabase('e107_links', 'link_order', array('link_id' => $second)),
			'The dragged row should have been given the lower sort value.'
		);
	}

	/**
	 * The row payload the reporter's proof of concept posts.
	 *
	 * @param string $name
	 * @return array
	 */
	private function linkFields($name)
	{
		return array(
			'link_name'             => $name,
			'link_url'              => 'news.php',
			'link_category'         => 1,
			'link_parent'           => 0,
			'link_class'            => 0,
			'link_order'            => 0,
			'link_open'             => 0,
			'link_owner'            => 'core',
			'link_description'      => '',
			'link_sefurl'           => '',
			'link_button'           => '',
			'link_rel'              => '',
			'link_function'         => '',
			'etrigger_submit'       => 'create',
			'__after_submit_action' => 'list',
		);
	}

	/**
	 * @param string $name
	 * @param int $order
	 * @return array
	 */
	private function linkRow($name, $order)
	{
		return array(
			'link_name'        => $name,
			'link_url'         => 'news.php',
			'link_description' => '',
			'link_button'      => '',
			'link_category'    => 1,
			'link_order'       => $order,
			'link_parent'      => 0,
			'link_open'        => 0,
			'link_class'       => 0,
			'link_function'    => '',
			'link_sefurl'      => '',
			'link_rel'         => '',
			'link_owner'       => 'core',
		);
	}

	/**
	 * The per-row inline-edit token, which is a password_hash() of the session id
	 * rather than the CSRF token, and is emitted as data-token on every editable
	 * cell.
	 *
	 * @param string $source
	 * @return string
	 */
	private function grabRowToken($source)
	{
		$matches = array();

		if (!preg_match('/data-token=[\'"]([^\'"]+)[\'"]/', $source, $matches))
		{
			throw new \RuntimeException('Could not locate an inline-edit data-token on ' . self::LIST_PATH);
		}

		return $matches[1];
	}
}
