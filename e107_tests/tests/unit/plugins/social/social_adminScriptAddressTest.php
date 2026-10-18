<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Saves the social page addresses through the social plugin's admin area, each child putting them back as it found them.
 */
class social_adminScriptAddressTest extends \Test\Unit
{
	use \Test\ScriptClassPages;

	const STORED = '@@social_adminScriptAddressTest@@';

	public function testAnAddressThatRunsScriptIsRefusedOutsideThePostScriptClass()
	{
		list($page, $stored) = $this->savePages(e_UC_NOBODY, array('facebook' => 'javascript:void(0)'));

		self::assertSame(array('facebook' => 'https://e107.org/before'), $stored, "the address was saved:\n".$page);
		self::assertStringContainsString("<div class='s-message-item'><strong>facebook</strong> - ", $page, 'the refusal has to name the field');
	}

	public function testThePostScriptClassMaySaveAnAddressThatRunsScript()
	{
		list($page, $stored) = $this->savePages(e_UC_MAINADMIN, array('facebook' => 'javascript:void(0)'));

		self::assertSame(array('facebook' => 'javascript:void(0)'), $stored, "the address was refused:\n".$page);
	}

	public function testAnAddressThatRunsNoScriptIsSavedOutsideThePostScriptClass()
	{
		list($page, $stored) = $this->savePages(e_UC_NOBODY, array('facebook' => 'https://e107.org/after'));

		self::assertSame(array('facebook' => 'https://e107.org/after'), $stored, "the address was refused:\n".$page);
	}

	public function testAnAddressUnderANameNoNetworkHasIsNotStored()
	{
		list($page, $stored) = $this->savePages(e_UC_NOBODY, array('<b>x</b>' => 'javascript:void(0)', 'facebook' => 'https://e107.org/after'));

		self::assertSame(array('facebook' => 'https://e107.org/after'), $stored, "the address was saved:\n".$page);
		self::assertStringNotContainsString('<b>x</b>', $page);
	}

	/**
	 * The form is opened first, and what it shows is posted back with one other address added.
	 */
	public function testAStoredAddressThatRunsScriptPassesUnchanged()
	{
		$seed = $this->seedAddresses(array('facebook' => 'javascript:void(0)'));
		$shown = $this->inputValues($this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/social/admin_config.php', 'mode=main&action=pages', array(), $seed));

		self::assertSame('javascript:void(0)', $shown['xurl[facebook]'], 'the form has to show the stored address');

		list($page, $stored) = $this->savePages(e_UC_NOBODY, array('facebook' => $shown['xurl[facebook]'], 'twitter' => 'https://e107.org/x'), array('facebook' => 'javascript:void(0)'));

		self::assertSame(array('facebook' => 'javascript:void(0)', 'twitter' => 'https://e107.org/x'), $stored, "the unchanged address was refused:\n".$page);
	}

	/**
	 * @param int $scriptClass the class that may post script
	 * @param array $posted the addresses the form posts, by network
	 * @param array $before the addresses stored before the save
	 * @return array the page, and the addresses stored after the save
	 */
	private function savePages($scriptClass, array $posted, array $before = array('facebook' => 'https://e107.org/before'))
	{
		$page = $this->openAsScriptClass($scriptClass, 'e107_plugins/social/admin_config.php', 'mode=main&action=pages',
			array('save_social_pages' => 1, 'xurl' => $posted), $this->seedAddresses($before));

		self::assertSame(1, preg_match('/'.self::STORED.'(.*)$/m', $page, $match), "the child never printed the addresses:\n".$page);

		return array($page, json_decode($match[1], true));
	}

	/**
	 * @param array $addresses
	 * @return string PHP that stores $addresses for the page and, as the child exits, prints what is stored and puts the preference back
	 */
	private function seedAddresses(array $addresses)
	{
		return "\$xurlWas = e107::getConfig()->get('xurl'); e107::getConfig()->set('xurl', ".var_export($addresses, true)."); "
			."register_shutdown_function(function () use (\$xurlWas) { echo \"\\n".self::STORED."\".json_encode(e107::getConfig()->get('xurl')); "
			."e107::getConfig()->set('xurl', \$xurlWas)->save(false, true, false); }); ";
	}
}
