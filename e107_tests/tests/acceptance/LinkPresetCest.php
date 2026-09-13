<?php

/**
 * "Predefined link" is the chooser above the URL field in Admin Area » Settings »
 * Navigation. Picking Logout fills in the URL, the name and the user class of the
 * entry e107 knows how to build, and it writes the URL into the field the
 * administrator can read, {E_TOKEN} and all.
 *
 * link_preset is a virtual field, so nothing of that name reaches the links table:
 * the controller applies the preset on save. That is the half these cases measure,
 * because they post the form exactly as a browser with scripting off does, and a row
 * identical either way is the whole point of applying it there. The browser half is
 * e107_tests/tests/webdriver/LinkPresetChooserCest.php.
 *
 * The acceptance database is not reloaded between runs, so every row the application
 * creates here is taken back out through the manager's own delete trigger.
 *
 * @see links_admin_ui::applyPreset()
 * @see links_admin_form_ui::link_preset()
 */
class LinkPresetCest
{
	/** admin_ui's create trigger for the navigation manager. */
	const CREATE_PATH = '/e107_admin/links.php?mode=main&action=create';

	/** Its edit trigger, which wants the row id after it. */
	const EDIT_PATH = '/e107_admin/links.php?mode=main&action=edit&id=';

	/** Where a bundled theme publishes {NAVIGATION=main}. */
	const FRONT_PAGE = '/index.php';

	/** The key the chooser posts for the one preset core ships. */
	const PRESET = 'logout';

	/** What that preset is: LAN_LOGOUT, the placeholder URL, and e_UC_MEMBER. */
	const PRESET_NAME = 'Logout';
	const PRESET_URL = 'index.php?logout&e-token={E_TOKEN}';
	const PRESET_CLASS = '253';

	/** LAN_LINKS_URL_REQUIRED, which the field's help tip would stand in for if link_url declared no 'error'. */
	const URL_REQUIRED = 'Type a URL or choose a predefined link.';

	/** A link an administrator writes for themselves, which no preset may touch. */
	const TYPED_NAME = 'LinkPresetTypedProbe';
	const TYPED_URL = 'contact.php?linkpreset';

	/** The category {NAVIGATION=main} renders. */
	const NAV_CATEGORY = 1;

	/**
	 * The cookie jar is emptied first because one of these cases ends the session and
	 * the others do not, and loginAsAdmin() wants the login form either way.
	 */
	public function _after(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->loginAsAdmin();
		$I->removeSitelink(self::PRESET_NAME);
		$I->removeSitelink(self::TYPED_NAME);
	}

	/**
	 * The task the chooser exists for: an administrator who wants a logout entry and
	 * knows nothing about security tokens. Name and URL are posted empty because that
	 * is what the form holds when the preset is picked and the browser runs no script.
	 */
	public function aChosenPresetFillsInTheLinkTheFormLeftEmpty(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$this->submit($I, self::CREATE_PATH, 'create', array('link_preset' => self::PRESET));

		$I->assertSame(self::PRESET_URL, $this->storedUrl($I, self::PRESET_NAME),
			'the preset stored something other than the placeholder URL');
		$I->assertSame(self::PRESET_CLASS,
			$I->grabFromDatabase('e107_links', 'link_class', array('link_name' => self::PRESET_NAME)),
			'a logout entry offered to guests is a defect the administrator would meet later');

		$I->amOnPage(self::FRONT_PAGE);

		$link = $I->grabNavigationLogoutLink();
		$I->dontSeeInSource('{E_TOKEN}');
		$I->assertStringContainsString('e-token='.$I->grabPublishedToken(), $link,
			'the row the preset wrote has to reach the navigation carrying this session\'s token');

		$I->amOnPage($link);

		$I->seeSignedOut();
	}

	/**
	 * A key the chooser never offered fills nothing in, so the required URL fails as
	 * an empty URL always did, with the field's own message rather than its help tip.
	 * An array is one of those keys: the chooser posts a string, and reading anything
	 * else as an array offset is a TypeError from PHP 8.
	 */
	public function aPresetKeyTheChooserNeverOfferedFillsNothingIn(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		foreach(array('no-such-preset', array(self::PRESET)) as $chosen)
		{
			$this->submit($I, self::CREATE_PATH, 'create', array(
				'link_preset' => $chosen,
				'link_name'   => self::TYPED_NAME,
			));

			$I->seeInSource(self::URL_REQUIRED);
			$I->dontSeeInDatabase('e107_links', array('link_name' => self::TYPED_NAME));
		}
	}

	/**
	 * The chooser is on the edit form as well, which is how a link stored before
	 * v2.3.12 is repaired: open it, pick Logout, save. The row it starts from is
	 * created through the same form with the chooser left alone, so this also says a
	 * typed URL is stored as it was typed.
	 */
	public function aPresetChosenOnTheEditFormReplacesWhatWasTyped(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$this->submit($I, self::CREATE_PATH, 'create', array(
			'link_name' => self::TYPED_NAME,
			'link_url'  => self::TYPED_URL,
		));

		$I->assertSame(self::TYPED_URL, $this->storedUrl($I, self::TYPED_NAME),
			'no preset was chosen, so the URL had to be stored as it was typed');

		$id = (int) $I->grabFromDatabase('e107_links', 'link_id', array('link_name' => self::TYPED_NAME));

		$this->submit($I, self::EDIT_PATH.$id, 'update', array(
			'link_preset' => self::PRESET,
			'link_name'   => self::TYPED_NAME,
			'link_url'    => self::TYPED_URL,
		));

		$I->assertSame(self::PRESET_URL, $this->storedUrl($I, self::PRESET_NAME));
		$I->assertSame(self::PRESET_CLASS,
			$I->grabFromDatabase('e107_links', 'link_class', array('link_id' => $id)));
		$I->dontSeeInDatabase('e107_links', array('link_name' => self::TYPED_NAME));
	}

	/**
	 * Post the manager's own form the way a browser with no JavaScript does: every
	 * field the create and edit screens render, holding what the administrator left
	 * in them.
	 *
	 * @param AcceptanceTester $I
	 * @param string $path
	 * @param string $trigger 'create' or 'update'
	 * @param array $fields what the case is about; the rest is the form's empty state
	 * @return void
	 */
	private function submit(AcceptanceTester $I, $path, $trigger, array $fields)
	{
		$posted = array_merge(array(
			'link_preset'      => '',
			'link_name'        => '',
			'link_url'         => '',
			'link_description' => '',
			'link_button'      => '',
			'link_category'    => self::NAV_CATEGORY,
			'link_order'       => 950,
			'link_parent'      => 0,
			'link_open'        => 0,
			'link_class'       => 0,
			'link_function'    => '',
			'link_sefurl'      => '',
			'link_rel'         => '',
			'link_owner'       => '',
		), $fields);

		$posted['etrigger_submit'] = $trigger;
		$posted['__after_submit_action'] = 'list';
		$posted['e-token'] = $I->grabFreshAdminToken($path);

		$I->sendPostRequest($path, $posted);
	}

	/**
	 * toDB() encodes the ampersand on the way in, so the stored URL is read back
	 * decoded rather than compared against a second spelling of the same thing.
	 *
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @return string
	 */
	private function storedUrl(AcceptanceTester $I, $name)
	{
		$stored = $I->grabFromDatabase('e107_links', 'link_url', array('link_name' => $name));

		return html_entity_decode($stored, ENT_QUOTES, 'UTF-8');
	}
}
