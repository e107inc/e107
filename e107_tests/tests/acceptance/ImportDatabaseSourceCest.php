<?php

/**
 * The import plugin's database sources, which read another database on the site's own server.
 */
class ImportDatabaseSourceCest
{
	/** A provider whose source is a database. */
	const ROUTE = '/e107_plugins/import/admin_import.php?mode=main&action=import&type=wordpress';

	const NO_SERVER = 'needs a site on a MySQL or MariaDB server';

	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('import');
		$I->loginAsAdmin();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropPluginInstall('import');
	}

	public function aDatabaseSourceListsTheServersDatabasesOrSaysWhyThereAreNone(AcceptanceTester $I)
	{
		$I->wantTo('Offer the databases of the site\'s server as an import source, or say why a site without one has none');

		$I->amOnPage(self::ROUTE);
		$I->dontSeeElement('input[name=authpass]');

		if($I->getDbModule()->_getDbDriver() === 'sqlite')
		{
			$I->see(self::NO_SERVER);
			$I->dontSeeElement('select[name=dbParamDatabase]');

			return;
		}

		$I->seeElement('select[name=dbParamDatabase]');
		$I->dontSee(self::NO_SERVER);
	}
}
