<?php

/**
 * A custom page field's title, help text and settings, from whoever stored them, print as text in the page form and on
 * the page.
 */
class PageCustomFieldTitleCest
{
	/** An administrator who holds the Pages permission and nothing else. */
	const PAGES_ADMIN = 'cfq7tpages';

	const PAGES_ADMIN_PASS = 'Cfq7tPagesPass';

	/** Markup a setting may carry; the id marks it apart from anything the admin area prints. */
	const MARKUP = '"><svg id=cfq7t onload=alert(1)>';

	/** A class that leaves its attribute, as a parameter could write it. */
	const CLASS_BREAK = "x' onmouseover='cfq7t()";

	public function _before(AcceptanceTester $I)
	{
		$I->haveMember(self::PAGES_ADMIN, self::PAGES_ADMIN_PASS, array('user_admin' => 1, 'user_perms' => '5.'));
	}

	public function _after(AcceptanceTester $I)
	{
		$I->restoreSitePrefs();
	}

	public function aPagesAdministratorsFieldSettingsAreStoredAsAFieldsOwn(AcceptanceTester $I)
	{
		$chapter = $this->haveChapter($I, array('mytext' => array('title' => 'Text', 'type' => 'text', 'writeParms' => '', 'help' => '')));

		$I->haveSitePrefUntilAfter('post_html', 254);
		$I->haveSitePrefUntilAfter('post_script', 255);
		$I->resetAllCookies();
		$I->loginAsAdmin(self::PAGES_ADMIN, self::PAGES_ADMIN_PASS);
		$I->amOnPage('/e107_admin/cpage.php?mode=cat&action=edit&id='.$chapter);
		$I->submitForm('#core-page-chapters-form', array(
			'chapter_fields[0][key]'   => 'mytext',
			'chapter_fields[0][title]' => 'Text 2',
			'chapter_fields[0][type]'  => 'text',
			'chapter_fields[0][note]'  => 'Note'.self::MARKUP,
			'chapter_fields[1][key]'   => 'mymethod',
			'chapter_fields[1][title]' => 'Method',
			'chapter_fields[1][type]'  => 'method',
		), 'etrigger_submit');
		$I->restoreSitePrefs();

		$stored = json_decode($I->grabFromDatabase('e107_page_chapters', 'chapter_fields', array('chapter_id' => $chapter)), true);

		$I->assertSame(array('title' => 'Text 2', 'type' => 'text', 'writeParms' => '', 'help' => ''), $stored['mytext']);
		$I->assertArrayNotHasKey('mymethod', $stored);
		$I->assertSame(1, $stored['__trust__']['mytext']['title']);
	}

	public function aLowerAdministratorsSaveKeepsTheSettingsTheyLeftAlone(AcceptanceTester $I)
	{
		$chapter = $this->haveChapter($I, array('mytext' => array('title' => 'Text', 'type' => 'text', 'writeParms' => '', 'help' => '')));
		$page = $this->havePage($I, $chapter, array('mytext' => 'Some text'));

		$I->haveSitePrefUntilAfter('post_html', 254);
		$I->haveSitePrefUntilAfter('post_script', 254);
		$this->saveChapterAsPagesAdmin($I, $chapter, array('chapter_fields[0][title]' => 'Text <b id="cfq7tk" onclick="cfq7t()">kept</b>'));
		$I->restoreSitePrefs();

		$I->haveSitePrefUntilAfter('post_html', 254);
		$I->haveSitePrefUntilAfter('post_script', 255);
		$this->saveChapterAsPagesAdmin($I, $chapter, array('chapter_fields[0][help]' => 'Help <b id="cfq7tc" onclick="cfq7t()">changed</b>'));
		$I->restoreSitePrefs();

		$I->resetAllCookies();
		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/cpage.php?mode=page&action=edit&id='.$page);
		$I->seeElement('#cfq7tk[onclick]');
		$I->seeElement('#cfq7tc');
		$I->dontSeeElement('#cfq7tc[onclick]');
	}

	public function aHigherAdministratorsSaveDoesNotPromoteSettingsTheyLeftAlone(AcceptanceTester $I)
	{
		$chapter = $this->haveChapter($I, array('mytext' => array('title' => 'Text', 'type' => 'text', 'writeParms' => '', 'help' => '')));
		$page = $this->havePage($I, $chapter, array('mytext' => 'Some text'));

		$I->haveSitePrefUntilAfter('post_html', 254);
		$I->haveSitePrefUntilAfter('post_script', 255);
		$this->saveChapterAsPagesAdmin($I, $chapter, array('chapter_fields[0][title]' => 'Text &lt;b id="cfq7tp" onclick="cfq7t()"&gt;typed&lt;/b&gt;'));
		$I->restoreSitePrefs();

		$I->haveSitePrefUntilAfter('post_html', 254);
		$I->haveSitePrefUntilAfter('post_script', 254);
		$this->saveChapterAsPagesAdmin($I, $chapter, array());
		$I->restoreSitePrefs();

		$I->resetAllCookies();
		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/cpage.php?mode=page&action=edit&id='.$page);
		$I->seeInSource('page_fields__mytext');
		$I->dontSeeElement('#cfq7tp');

		$I->haveSiteTheme('bootstrap3');
		$I->amOnPage('/page.php?id='.$page);
		$I->seeInSource('custom-fields-example');
		$I->dontSeeElement('#cfq7tp');
	}

	/**
	 * A dropdown's first option, which a page created untouched posts, keeps no level, so it prints as text whoever
	 * created the page, once the field is made a text field.
	 */
	public function aCreatedPagesChoiceKeepsNoLevel(AcceptanceTester $I)
	{
		$key = '<b id="cfq7tc" onclick="cfq7t()">t</b>';
		$chapter = $this->haveChapter($I, array('d' => array('title' => 'D', 'type' => 'dropdown', 'writeParms' => json_encode(array('optArray' => array($key => 'T', 'b' => 'B'))), 'help' => '')));

		$I->haveSitePrefUntilAfter('post_html', 254);
		$I->haveSitePrefUntilAfter('post_script', 254);
		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/cpage.php?mode=page&action=create');
		$I->submitForm('#core-page-form', array('page_chapter' => $chapter, 'page_title' => 'Created cfq7t', 'page_fields__d' => $key), 'etrigger_submit');
		$I->restoreSitePrefs();

		$stored = json_decode($I->grabFromDatabase('e107_page', 'page_fields', array('page_title' => 'Created cfq7t')), true);
		$I->assertStringContainsString('cfq7tc', $stored['d']);
		$I->assertArrayNotHasKey('__trust__', $stored);
	}

	public function anUntouchedSaveStoresEachSettingAgainByteForByte(AcceptanceTester $I)
	{
		$fields = array(
			'mytext'    => array('title' => 'java[p][/p]script:cfq7t()', 'type' => 'text', 'writeParms' => '{"size":"block-level"}', 'help' => 'Help [h][/h]<b>help</b>'),
			'__trust__' => array('__tabs__' => 2, 'mytext' => array('title' => 1, 'writeParms' => 2, 'help' => 0)),
		);
		$chapter = $this->haveChapter($I, $fields, 'Tab [p][/p]<b>tab</b>');

		$this->saveChapterAsPagesAdmin($I, $chapter, array());

		$I->assertSame(array('__tabs__' => array('additional' => 'Tab [p][/p]<b>tab</b>')) + $fields,
			json_decode($I->grabFromDatabase('e107_page_chapters', 'chapter_fields', array('chapter_id' => $chapter)), true));
	}

	public function anAuthorsHtmlTitlePrintsInTheMainAdministratorsForm(AcceptanceTester $I)
	{
		$chapter = $this->haveChapter($I, array('mytext' => array('title' => 'Text', 'type' => 'text', 'writeParms' => '', 'help' => '')));
		$page = $this->havePage($I, $chapter, array('mytext' => 'Some text'));

		$I->haveSitePrefUntilAfter('post_html', 254);
		$I->haveSitePrefUntilAfter('post_script', 255);
		$I->resetAllCookies();
		$I->loginAsAdmin(self::PAGES_ADMIN, self::PAGES_ADMIN_PASS);
		$I->amOnPage('/e107_admin/cpage.php?mode=cat&action=edit&id='.$chapter);
		$I->submitForm('#core-page-chapters-form', array(
			'chapter_fields[0][key]'   => 'mytext',
			'chapter_fields[0][title]' => 'Text <b id="cfq7tb">bold</b>',
			'chapter_fields[0][type]'  => 'text',
			'chapter_fields[0][help]'  => 'Help <script id="cfq7ts">var cfq7t = 1;</script><img id="cfq7ti" src="x.png" onload="cfq7t()"><svg id=cfq7t onload=alert(1)>',
		), 'etrigger_submit');
		$I->restoreSitePrefs();

		$I->resetAllCookies();
		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/cpage.php?mode=page&action=edit&id='.$page);
		$I->seeInSource('<b id="cfq7tb">bold</b>');
		$I->dontSeeInSource('<svg id=cfq7t');
		$I->dontSeeInSource('<script id="cfq7ts"');
		$I->seeElement('#cfq7ti');
		$I->dontSeeElement('#cfq7ti[onload]');
	}

	public function theMainAdministratorsPageFormPrintsStoredSettingsAsText(AcceptanceTester $I)
	{
		$page = $this->havePage($I, $this->haveChapter($I, array(
			'mytext'     => array('title' => 'Text'.self::MARKUP, 'type' => 'text', 'writeParms' => 'trClass='.rawurlencode(self::CLASS_BREAK).'&tabindex='.rawurlencode("1'".self::MARKUP), 'help' => 'Help'.self::MARKUP, 'note' => 'Note'.self::MARKUP),
			'mydropdown' => array('title' => 'Colour', 'type' => 'dropdown', 'writeParms' => json_encode(array('optArray' => array('blue' => 'Blue'.self::MARKUP, "x'>".self::MARKUP => array('a' => 'A')), 'tdClassLeft' => self::CLASS_BREAK)), 'help' => ''),
		), 'Tab'.self::MARKUP, 'x'.self::MARKUP), array('mytext' => 'Some text', 'mydropdown' => 'blue'));

		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/cpage.php?mode=page&action=edit&id='.$page);
		$I->seeInSource('page_fields__mytext');
		$I->dontSeeInSource('<svg id=cfq7t');
		$I->dontSeeInSource("onmouseover='cfq7t");

		$I->haveSiteTheme('bootstrap3');
		$I->amOnPage('/page.php?id='.$page);
		$I->seeInSource('custom-fields-example');
		$I->dontSeeInSource('<svg id=cfq7t');
	}

	public function legitimateSettingsStillShape(AcceptanceTester $I)
	{
		$page = $this->havePage($I, $this->haveChapter($I, array(
			'mytext'     => array('title' => 'Plain title', 'type' => 'text', 'writeParms' => '', 'help' => 'Plain help'),
			'mydropdown' => array('title' => 'Colour', 'type' => 'dropdown', 'writeParms' => '{ "optArray": { "blue": "Blue", "R&D": "Research" }, "default": "blank" }', 'help' => ''),
		), 'Extra'), array('mytext' => 'Some text', 'mydropdown' => 'R&D'));

		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/cpage.php?mode=page&action=edit&id='.$page);
		$I->see('Plain title');
		$I->seeInSource('Plain help');
		$I->seeOptionIsSelected('page_fields__mydropdown', 'Research');

		$I->haveSiteTheme('bootstrap3');
		$I->amOnPage('/page.php?id='.$page);
		$I->see('Plain title');
		$I->see('Research');
	}

	public function anImageTitleStaysOutOfItsModalLink(AcceptanceTester $I)
	{
		$page = $this->havePage($I, $this->haveChapter($I, array(
			'myimage'   => array('title' => 'Img &lt;b id=cfq7tm&gt;bold&lt;/b&gt;', 'type' => 'image', 'writeParms' => '', 'help' => ''),
			'__trust__' => array('myimage' => array('title' => 0)),
		)), array('myimage' => '{e_IMAGE}e107_icon_32.png', '__trust__' => array('myimage' => 0)));

		$I->haveSiteTheme('bootstrap3');
		$I->amOnPage('/page.php?id='.$page);
		$I->seeElement('a.e-modal');
		$I->dontSeeElement('a.e-modal[data-modal-caption]');
	}

	/**
	 * Save chapter $chapter through its edit form, as the administrator who holds only the Pages permission.
	 *
	 * @param AcceptanceTester $I
	 * @param int $chapter
	 * @param array $values form field => value
	 */
	private function saveChapterAsPagesAdmin(AcceptanceTester $I, $chapter, array $values)
	{
		$I->resetAllCookies();
		$I->loginAsAdmin(self::PAGES_ADMIN, self::PAGES_ADMIN_PASS);
		$I->amOnPage('/e107_admin/cpage.php?mode=cat&action=edit&id='.$chapter);
		$I->submitForm('#core-page-chapters-form', $values, 'etrigger_submit');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param array $fields field key => settings, stored as they are given
	 * @param string $tab the fields' tab label
	 * @param string $tabKey the key the tab label is stored under
	 * @return int chapter_id
	 */
	private function haveChapter(AcceptanceTester $I, array $fields, $tab = 'Extra', $tabKey = 'additional')
	{
		return $I->haveInDatabase('e107_page_chapters', array(
			'chapter_parent'           => 1,
			'chapter_name'             => 'Custom field settings cfq7',
			'chapter_sef'              => 'custom-field-settings-cfq7-'.count($fields).strlen($tab),
			'chapter_template'         => 'default',
			'chapter_meta_description' => '',
			'chapter_meta_keywords'    => '',
			'chapter_icon'             => '',
			'chapter_fields'           => json_encode(array('__tabs__' => array($tabKey => $tab)) + $fields),
			'chapter_manager'          => 254,
			'chapter_image'            => '',
			'chapter_order'            => 0,
			'chapter_visibility'       => 0,
		));
	}

	/**
	 * A page in $chapter, laid out by bootstrap3's 'customfields' layout.
	 *
	 * @param AcceptanceTester $I
	 * @param int $chapter
	 * @param array $values custom field key => value
	 * @return int page_id
	 */
	private function havePage(AcceptanceTester $I, $chapter, array $values)
	{
		return $I->haveInDatabase('e107_page', array(
			'page_title'     => 'Field settings cfq7 '.$chapter,
			'page_sef'       => 'field-settings-cfq7-'.$chapter,
			'page_chapter'   => $chapter,
			'page_text'      => 'Body',
			'page_author'    => 1,
			'page_datestamp' => time(),
			'page_class'     => 0,
			'page_password'  => '',
			'page_template'  => 'customfields',
			'page_order'     => 0,
			'page_fields'    => json_encode($values),
		));
	}
}
