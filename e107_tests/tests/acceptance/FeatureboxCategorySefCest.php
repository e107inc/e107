<?php

/**
 * Admin > Feature Box > Categories, once identity moved off the layout (#5994).
 *
 * The unit tests settle the address rule and the sanitiser. What they cannot
 * answer is whether an administrator can actually reach any of it: the sef is
 * settled inside beforeCreate()/beforeUpdate(), so a save that never reaches
 * them, or a field the form does not render, would pass every unit test in the
 * suite and still leave the feature unusable.
 */
class FeatureboxCategorySefCest
{
	const CREATE = '/e107_plugins/featurebox/admin_config.php?mode=category&action=create';
	const LIST_PAGE = '/e107_plugins/featurebox/admin_config.php?mode=category&action=list';
	const FORM = '#plugin-featurebox-form';

	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('featurebox');
		$I->loginAsAdmin();
	}

	public function theCategoryFormOffersTheShortcodeName(AcceptanceTester $I)
	{
		$I->wantTo('name a category for its layouts to address');

		$I->amOnPage(self::CREATE);

		$I->assertSame(200, $I->grabResponseCode());
		$I->see('Shortcode name');
		$I->seeElement('input[name="fb_category_sef"]');
		$I->see('Generate');
	}

	/**
	 * The install seeds carry their own sefs, so a fresh site is already in the
	 * state upgrade_post() puts an existing one into.
	 */
	public function theInstallSeedsAreAlreadyAddressed(AcceptanceTester $I)
	{
		$I->amOnPage(self::LIST_PAGE);

		$I->see('bootstrap3_carousel');
		$I->see('bootstrap_tabs');
	}

	/**
	 * The ask in #5994: one layout, two categories.
	 */
	public function twoCategoriesCanShareOneLayout(AcceptanceTester $I)
	{
		$I->wantTo('give a second section the layout the first one uses');

		$I->amOnPage(self::CREATE);
		$I->submitForm(self::FORM, array(
			'fb_category_title'    => 'What you get',
			'fb_category_sef'      => '',
			'fb_category_template' => 'default',
			'fb_category_limit'    => '3',
		), 'etrigger_submit');

		$I->amOnPage(self::CREATE);
		$I->submitForm(self::FORM, array(
			'fb_category_title'    => 'Why us',
			'fb_category_sef'      => 'why-us',
			'fb_category_template' => 'default',
			'fb_category_limit'    => '3',
		), 'etrigger_submit');

		$I->amOnPage(self::LIST_PAGE);
		$I->see('What you get');
		$I->see('what-you-get');
		$I->see('why-us');
	}

	/**
	 * Menu Manager filters the modifier it writes with an ASCII-only expression,
	 * so a stored accent would be placed as something that resolves to nothing.
	 */
	public function AnAccentedShortcodeNameIsStoredAsAscii(AcceptanceTester $I)
	{
		$I->amOnPage(self::CREATE);
		$I->submitForm(self::FORM, array(
			'fb_category_title'    => 'Slovak',
			'fb_category_sef'      => 'kategória',
			'fb_category_template' => 'default',
			'fb_category_limit'    => '1',
		), 'etrigger_submit');

		$I->amOnPage(self::LIST_PAGE);
		$I->see('kategoria');
		$I->dontSee('kategória');
	}

	/**
	 * The uniqueness check has to exclude the row being saved, or no category
	 * could ever be edited again: its own sef would collide with itself.
	 */
	public function ReSavingACategoryDoesNotCollideWithItself(AcceptanceTester $I)
	{
		$I->amOnPage(self::LIST_PAGE);
		$source = $I->grabPageSource();

		$I->assertMatchesRegularExpression('#action=edit&amp;id=(\d+)#', $source,
			'precondition: the list has to offer an edit link to follow');
		preg_match('#action=edit&amp;id=(\d+)#', $source, $match);

		$I->amOnPage('/e107_plugins/featurebox/admin_config.php?mode=category&action=edit&id='.$match[1]);
		$I->submitForm(self::FORM, array(), 'etrigger_submit');

		$I->dontSee('Another category already uses that shortcode name');
	}

	/**
	 * Seven places read fb_category_template === 'unassigned' as "this is the
	 * system category", and the dropped unique key is what stopped a second row
	 * claiming it. A theme shipping its own 'unassigned' key would offer it in
	 * the Layout dropdown, and a crafted post needs no theme at all.
	 */
	public function theSystemLayoutCannotBeClaimedByASecondCategory(AcceptanceTester $I)
	{
		$I->amOnPage(self::CREATE);
		$I->submitForm(self::FORM, array(
			'fb_category_title'    => 'Impostor',
			'fb_category_sef'      => 'impostor',
			'fb_category_template' => 'unassigned',
			'fb_category_limit'    => '1',
		), 'etrigger_submit');

		$I->see('The Unassigned layout belongs to the system category');

		$I->amOnPage(self::LIST_PAGE);
		$I->dontSee('Impostor');
	}

	/**
	 * The system category is found in SQL, under the column's collation, so a
	 * case variant would be matched there while a case-sensitive guard let it in.
	 */
	public function theSystemLayoutCannotBeClaimedInAnotherCase(AcceptanceTester $I)
	{
		$I->amOnPage(self::CREATE);
		$I->submitForm(self::FORM, array(
			'fb_category_title'    => 'Shouty impostor',
			'fb_category_sef'      => 'shouty-impostor',
			'fb_category_template' => 'Unassigned',
			'fb_category_limit'    => '1',
		), 'etrigger_submit');

		$I->see('The Unassigned layout belongs to the system category');

		$I->amOnPage(self::LIST_PAGE);
		$I->dontSee('Shouty impostor');
	}

	/**
	 * What UNIQUE KEY fb_category_template used to do through MySQL error 1062.
	 */
	public function aShortcodeNameAlreadyInUseIsRefused(AcceptanceTester $I)
	{
		$I->amOnPage(self::CREATE);
		$I->submitForm(self::FORM, array(
			'fb_category_title'    => 'Clash',
			'fb_category_sef'      => 'bootstrap_tabs',
			'fb_category_template' => 'default',
			'fb_category_limit'    => '1',
		), 'etrigger_submit');

		$I->see('Another category already uses that shortcode name');
	}
}
