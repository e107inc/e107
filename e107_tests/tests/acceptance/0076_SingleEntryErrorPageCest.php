<?php

/**
 * The single entry point defines e_PAGE only after {@see eFront::run()} has
 * returned, and the 404 page is rendered inside that call, so anything
 * constructing {@see e_plugin} while the shortcode parser includes a plugin's
 * e_shortcode.php reads the constant before it exists. PHP 8 turns that into a
 * fatal Error and the error page never renders.
 *
 * The fixture plugin is the smallest thing that reaches the defect: an
 * e_shortcode.php calling {@see e107::getPlug()} at include time, which is what
 * user_friends does in the wild (issue #6024). The front page is the control:
 * its shortcode batch loads from the legacy include index.php reaches after
 * {@see eFront::run()} has returned, by which point e_PAGE exists, and it
 * answered normally throughout.
 */
class SingleEntryErrorPageCest
{
	const PLUGIN = 'temptest6024';
	const LOADED_HEADER = 'X-E107-Tests-6024';
	const NO_SUCH_ROUTE = '/index.php/e107-tests-no-such-route';

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile('e107_plugins/'.self::PLUGIN.'/plugin.xml', $this->pluginXml());
		$I->writeAppFile('e107_plugins/'.self::PLUGIN.'/e_shortcode.php', $this->shortcodeSource());

		$I->havePluginInstalled(self::PLUGIN);
		$I->seeInDatabase('e107_plugin', array(
			'plugin_path' => self::PLUGIN, 'plugin_installflag' => 1));
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropPluginInstall(self::PLUGIN);
		$I->dropPluginProbe();

		$I->deleteAppFile('e107_plugins/'.self::PLUGIN.'/e_shortcode.php');
		$I->deleteAppFile('e107_plugins/'.self::PLUGIN.'/plugin.xml');
	}

	public function anUnroutableUrlRendersTheErrorPage(AcceptanceTester $I)
	{
		$I->wantTo('render the 404 page when a plugin builds e_plugin during dispatch');

		$this->seePageWithoutAFatal($I, '/');
		$this->seePageWithoutAFatal($I, self::NO_SUCH_ROUTE);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $page
	 */
	private function seePageWithoutAFatal(AcceptanceTester $I, $page)
	{
		$I->amOnPage($page);
		$I->seeHttpHeader(self::LOADED_HEADER, '1');
		$I->dontSeeInSource('Fatal error');
		$I->dontSeeInSource('Uncaught');
		$I->dontSeeInSource('e_PAGE');
		$I->seeInSource('</html>');
	}

	/**
	 * @return string
	 */
	private function pluginXml()
	{
		return <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<e107Plugin name="e107 Tests 6024" version="1.0" date="2026-08-23" compatibility="2.0" installRequired="true">
	<author name="e107 Inc." url="https://e107.org" />
	<description>Fixture for 0076_SingleEntryErrorPageCest. Removed again in the Cest's _after().</description>
	<category>misc</category>
</e107Plugin>
XML;
	}

	/**
	 * @return string
	 */
	private function shortcodeSource()
	{
		return <<<'PHP'
<?php
// Fixture for 0076_SingleEntryErrorPageCest. Removed again in the Cest's _after().
if(!defined('e107_INIT'))
{
	exit;
}

header('X-E107-Tests-6024: 1');

e107::getPlug();

class temptest6024_shortcodes extends e_shortcode
{
	public function sc_temptest6024()
	{
		return '';
	}
}
PHP;
	}
}
