<?php

/**
 * A page with an editor area asks the site itself for the editor engine, and tells the engine which folder it was served from.
 */
class EditorEngineScriptCest
{
	const BASE_PREFIX = 'window.tinyMCEPreInit = ';

	const BASE_STATEMENT = '//script[starts-with(normalize-space(.), "'.self::BASE_PREFIX.'")]';

	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/newspost.php?mode=main&action=create');
		$I->seeElement('textarea.e-wysiwyg');
	}

	public function anEditorPageLoadsTheEngineFromThePluginsOwnFolder(AcceptanceTester $I)
	{
		$I->seeElement(\Helper\Editor::ENGINE_SCRIPT);
	}

	public function anEditorPageNamesNoCdnHost(AcceptanceTester $I)
	{
		$I->seeInSource('tinymce.min.js');
		$I->dontSeeInSource('cdnjs.cloudflare.com');
	}

	public function theEngineIsToldItsOwnFolderBeforeItRuns(AcceptanceTester $I)
	{
		$I->seeElement(self::BASE_STATEMENT.'/following-sibling::script[1][contains(@src, "/tinymce.min.js")]');

		$statement = trim($I->grabTextFrom(self::BASE_STATEMENT));
		$folder = dirname(parse_url($I->grabAttributeFrom(\Helper\Editor::ENGINE_SCRIPT, 'src'), PHP_URL_PATH));

		$I->assertSame(
			array('base' => $folder, 'suffix' => '.min'),
			json_decode((string) substr($statement, strlen(self::BASE_PREFIX), -1), true),
			'the base the page gives the engine is the folder its script is served from'
		);
	}
}
