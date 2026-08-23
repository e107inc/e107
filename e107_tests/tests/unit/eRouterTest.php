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
}
