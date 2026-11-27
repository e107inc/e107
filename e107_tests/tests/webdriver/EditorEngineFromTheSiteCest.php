<?php

/**
 * The editor runs from the site's own files: it initialises, finds its theme, skin and plugins there, and no case declares an outbound request, so the network boundary fails the run if the browser asks another host for any of it.
 */
class EditorEngineFromTheSiteCest
{
	const SCRIPT_CACHE = 'e107_web/cache/';

	const FOUND = <<<'JS'
var editor = window.tinymce.get(arguments[0]);
var script = document.querySelector(arguments[1]);
var skin = document.querySelector('link[rel="stylesheet"][href$="/skin.min.css"]');

return {
	folder: script ? window.location.origin + new URL(script.src).pathname.split('/tinymce.min.js')[0] : null,
	base: window.tinymce.baseURL,
	suffix: window.tinymce.suffix,
	missingPlugins: editor.settings.plugins.split(/\s+/).filter(function (name) { return name !== '' && !editor.plugins[name]; }),
	toolbarButtons: editor.getContainer().querySelectorAll('.mce-toolbar .mce-btn').length,
	skin: skin ? skin.href : null,
	skinRules: skin && skin.sheet ? skin.sheet.cssRules.length : 0
};
JS;

	/** @var string[] the files the script cache wrote into the site for the running case */
	private $combined = array();

	public function _after(\WebDriverTester $I)
	{
		$I->restoreSitePrefs();

		foreach ($this->combined as $file)
		{
			$I->removeAppPath(self::SCRIPT_CACHE.$file);
		}

		$this->combined = array();
	}

	public function theEditorInitialisesOnAnAdminEditorPage(\WebDriverTester $I)
	{
		$I->amSignedInAs();
		$I->amOnPage('/e107_admin/newspost.php?mode=main&action=create');

		$this->seeTheEditorFoundItsFiles($I, 'news-body');
	}

	public function theEditorInitialisesOnAFrontEndPageWithTheScriptCacheOn(\WebDriverTester $I)
	{
		$I->haveSitePrefUntilAfter('jscsscachestatus', 1);
		$I->haveSitePrefUntilAfter('subnews_htmlarea', 1);
		$I->amSignedInAs();
		$I->amOnPage('/submitnews.php');

		$this->combined = $this->grabCombinedFiles($I);
		$I->assertNotEmpty($this->combined, 'precondition: the script cache is on, so the page names combined files');
		$I->assertSame('function', $I->executeJS('return typeof window.jQuery;'),
			'precondition: the combined files are served, jQuery among them');

		$this->seeTheEditorFoundItsFiles($I, 'submitnews-item');
	}

	/**
	 * @param \WebDriverTester $I
	 * @param string $id the editor area's id
	 * @return void
	 */
	private function seeTheEditorFoundItsFiles(\WebDriverTester $I, $id)
	{
		$I->waitForEditor($id);
		$I->seeElement('#'.$id.'_ifr');

		$found = $I->executeJS(self::FOUND, array($id, \Helper\Editor::ENGINE_SCRIPT));

		$I->assertNotNull($found['folder'], 'the page loads the engine from the plugin\'s own folder');
		$I->assertSame($found['folder'], $found['base'], 'the engine takes its files from its folder on the page\'s own origin, whichever host served its script');
		$I->assertSame('.min', $found['suffix']);
		$I->assertSame(array(), $found['missingPlugins'], 'every plugin the configuration names has loaded');
		$I->assertGreaterThan(0, $found['toolbarButtons'], 'the toolbar is drawn');
		$I->assertSame($found['folder'].'/skins/lightgray/skin.min.css', $found['skin']);
		$I->assertGreaterThan(0, $found['skinRules'], 'the skin stylesheet has loaded');
	}

	/**
	 * @param \WebDriverTester $I
	 * @return string[] names of the combined script and style files the page loads
	 */
	private function grabCombinedFiles(\WebDriverTester $I)
	{
		$addresses = array_merge(
			$I->grabMultiple('script[src*="/'.self::SCRIPT_CACHE.'"]', 'src'),
			$I->grabMultiple('link[href*="/'.self::SCRIPT_CACHE.'"]', 'href')
		);

		return array_map(function ($address)
		{
			return basename(parse_url($address, PHP_URL_PATH));
		}, $addresses);
	}
}
