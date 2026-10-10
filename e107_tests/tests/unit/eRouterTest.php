<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

class eRouterTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	/**
	 * user.php reads its query string positionally as from.records.order, so a
	 * member-list URL that carries only the offset used to leave the record
	 * count at zero and the listing came back as LIMIT 0. It also hands the
	 * paging bar one URL carrying a token, so the token has to come back intact.
	 */
	public function testLegacyMemberListUrlCarriesEveryPagingComponent()
	{
		require_once e_CORE . 'url/user/url.php';
		$config = new core_user_url();

		self::assertSame('user.php?40.20.DESC', $config->create(array('profile', 'list'), array('page' => 40)));
		self::assertSame('user.php?40.5.ASC', $config->create(array('profile', 'list'), array('page' => 40, 'records' => 5, 'order' => 'ASC')));

		self::assertSame('user.php?--FROM--.20.DESC', $config->create(array('profile', 'list'), array('page' => '--FROM--')));
		self::assertSame('user.php', $config->create(array('profile', 'list'), array()));
	}

	/**
	 * A top replier whose account has gone carries a null id and a null name.
	 */
	public function testAProfileUrlForNobodyFallsBackToTheMemberList()
	{
		require_once e_CORE . 'url/user/url.php';
		$config = new core_user_url();

		$deleted = array('user_id' => null, 'user_name' => null, 'user_forums' => 9, 'percentage' => 9);

		self::assertSame('user.php', $config->create(array('profile', 'view'), $deleted));
		self::assertSame('user.php', $config->create(array('profile', 'edit'), $deleted));
		self::assertSame('user.php?id.7', $config->create(array('profile', 'view'), array('user_id' => 7, 'user_name' => 'replier')));
	}

	/**
	 * The SEF rule reaches user.php through the same positional query, built by
	 * {@see e_parse::simpleParse()} from the request parameters the rule allows.
	 */
	public function testSefMemberListRuleBuildsEveryPagingComponent()
	{
		require_once e_CORE . 'url/user/rewrite_url.php';
		$config = new core_user_rewrite_url();
		$rules = $config->config();
		$template = $rules['rules']['list']['legacyQuery'];
		$tp = e107::getParser();

		self::assertSame('40.5.ASC', $tp->simpleParse($template, new e_vars(array('page' => 40, 'records' => 5, 'order' => 'ASC')), '0'));
		self::assertSame('0.0.0', $tp->simpleParse($template, new e_vars(array()), '0'));
	}

	/**
	 * The front end compiles the URL config by one relative path and the admin area deletes it by another, as on a site; with opcache.enable_file_override, OPcache answers loadConfig()'s is_readable() for a script it holds.
	 */
	public function testClearCacheLeavesOpcacheNoCopyOfTheUrlConfig()
	{
		if(!function_exists('opcache_invalidate'))
		{
			self::markTestSkipped('OPcache is not loaded, so no compiled copy can outlive the file');
		}

		$dir = sys_get_temp_dir() . '/e107_url_' . uniqid('', true) . '/';
		mkdir($dir . 'url', 0777, true);
		mkdir($dir . 'admin');

		$php = "define('e107_INIT', true); define('e_CACHE_URL', '../url/'); ";
		$php .= "require '" . addslashes(e_HANDLER . 'core_functions.php') . "'; ";
		$php .= "require '" . addslashes(e_HANDLER . 'e107_class.php') . "'; ";
		$php .= "require '" . addslashes(e_HANDLER . 'application.php') . "'; ";
		$php .= "chdir('" . addslashes($dir) . "'); file_put_contents('url/config.php', '<?php return array();'); include './url/config.php'; ";
		$php .= "if(!opcache_is_script_cached('" . addslashes($dir) . "url/config.php')) { echo 'OPcache did not compile the config'; exit(1); } ";
		$php .= "chdir('admin'); eRouter::clearCache(); chdir('..'); var_export(is_readable('./url/config.php'));";

		try
		{
			list($output, $status) = $this->runInCli($php, '-d opcache.enable_cli=1 -d opcache.enable_file_override=1 -d opcache.validate_timestamps=0 -d opcache.file_update_protection=0');
		}
		finally
		{
			@unlink($dir . 'url/config.php');
			rmdir($dir . 'url');
			rmdir($dir . 'admin');
			rmdir($dir);
		}

		self::assertSame(0, $status, implode("\n", $output));
		self::assertSame(array('false'), $output, 'once clearCache() has deleted the URL config, nothing reports it readable, so the next request rebuilds it');
	}
}
