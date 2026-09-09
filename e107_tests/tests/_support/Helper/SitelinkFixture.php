<?php

namespace Helper;

use Codeception\Module as CodeceptionModule;

/**
 * The stored navigation link an acceptance Cest reads, follows and takes back out.
 *
 * A row the application created is a row nothing removes: Codeception's Db module
 * deletes only what it inserted itself, and the acceptance database is not reloaded
 * between runs, so a link left in a rendered category reaches every later test. The
 * delete therefore goes through the navigation manager's own trigger.
 *
 * The reading half is here for the same reason. The token a page publishes, the
 * logout URL the navigation published, and whether the session survived following it
 * are what every Cest about a stored logout link asserts on, so they have one home
 * rather than a copy per Cest to keep in step with the markup.
 */
class SitelinkFixture extends CodeceptionModule
{
	/** Navigation » Manage, which carries the delete trigger. */
	const LIST_PATH = '/e107_admin/links.php?mode=main&action=list';

	/** Served to a visitor who holds a session, and a redirect to anyone else. */
	const SETTINGS_PAGE = '/usersettings.php';

	/**
	 * Delete a navigation link by name through the trigger the manager itself posts.
	 * The caller holds an admin session; a name with no row is not an error, so a run
	 * that died before its create still lands here.
	 *
	 * @param string $name
	 * @return void
	 */
	public function removeSitelink($name)
	{
		if ($this->db()->grabNumRecords('e107_links', array('link_name' => $name)) === 0)
		{
			return;
		}

		$id = (int) $this->db()->grabFromDatabase('e107_links', 'link_id', array('link_name' => $name));

		$this->app()->sendPostRequest(self::LIST_PATH, array(
			'etrigger_delete' => array($id => $id),
			'e-token'         => $this->login()->grabFreshAdminToken(self::LIST_PATH),
		));

		$this->db()->dontSeeInDatabase('e107_links', array('link_name' => $name));
	}

	/**
	 * The navigation publishes an absolute URL, which is what tells a stored link
	 * apart from the root-relative one a theme's own user menu draws.
	 *
	 * @return string path to follow, as the navigation published it
	 */
	public function grabNavigationLogoutLink()
	{
		$matches = array();

		if (!preg_match('#["\']https?://[^"\']*?(index\.php\?logout[^"\']*)["\']#', $this->browser()->grabPageSource(), $matches))
		{
			throw new \RuntimeException('The navigation published no absolute logout link');
		}

		return '/'.str_replace('&amp;', '&', $matches[1]);
	}

	/**
	 * @return string the token the page in hand is publishing for its own forms
	 */
	public function grabPublishedToken()
	{
		$matches = array();

		if (!preg_match('#<meta name="e-token" content="([^"]+)"#', $this->browser()->grabPageSource(), $matches))
		{
			throw new \RuntimeException('The page published no e-token');
		}

		return $matches[1];
	}

	/**
	 * @return void
	 */
	public function seeSignedOut()
	{
		$this->browser()->amOnPage(self::SETTINGS_PAGE);
		$this->browser()->dontSeeCurrentUrlEquals(self::SETTINGS_PAGE);
	}

	/**
	 * @return void
	 */
	public function seeStillSignedIn()
	{
		$this->browser()->amOnPage(self::SETTINGS_PAGE);
		$this->browser()->seeInCurrentUrl(self::SETTINGS_PAGE);
	}

	/**
	 * @return \Helper\Acceptance
	 */
	private function app()
	{
		return $this->getModule('\Helper\Acceptance');
	}

	/**
	 * @return \Codeception\Module\PhpBrowser
	 */
	private function browser()
	{
		return $this->getModule('PhpBrowser');
	}

	/**
	 * @return \Helper\DelayedDb
	 */
	private function db()
	{
		return $this->getModule('\Helper\DelayedDb');
	}

	/**
	 * @return \Helper\AdminLogin
	 */
	private function login()
	{
		return $this->getModule('\Helper\AdminLogin');
	}
}
