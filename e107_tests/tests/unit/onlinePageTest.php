<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Covers the Who's Online page, whose counts are concatenated onto their labels in online.php itself.
 */
class onlinePageTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	const MOST_MEMBERS = 7;
	const MOST_GUESTS = 5;
	const SEEDED_MEMBERS = 2;

	/** @var array the tracking preferences as this test found them, absent ones held as null */
	private $prefState = array();

	protected function _before()
	{
		$config = e107::getConfig();
		$current = $config->getPref();

		foreach(array('track_online', 'antiflood1') as $name)
		{
			$this->prefState[$name] = array_key_exists($name, $current) ? $current[$name] : null;
			$config->set($name, 1);
		}

		$config->save(false, true, false);
	}

	protected function _after()
	{
		$config = e107::getConfig();

		foreach($this->prefState as $name => $value)
		{
			$value === null ? $config->remove($name) : $config->set($name, $value);
		}

		$config->save(false, true, false);
		$this->prefState = array();

		e107::getDb()->truncate('online');
	}

	/**
	 * Puts more members in the online table than the rendering child leaves guests, so each count is a number of its own.
	 */
	private function seedMembersOnline()
	{
		$sql = e107::getDb();
		$ip = e107::getIPHandler();

		$sql->truncate('online');

		for($i = 1; $i <= self::SEEDED_MEMBERS; $i++)
		{
			$sql->insert('online', array(
				'online_timestamp' => time(),
				'online_flag'      => 0,
				'online_user_id'   => $i.'.member'.$i,
				'online_ip'        => $ip->ipEncode('203.0.113.'.$i),
				'online_location'  => 'online.php',
				'online_pagecount' => 1,
				'online_active'    => 1,
				'online_agent'     => 'onlinePageTest',
				'online_language'  => 'en',
			));
		}
	}

	/**
	 * Renders online.php in a subprocess against a known site history, running $before at global scope ahead of the page and $after behind it.
	 *
	 * @param string $before
	 * @param string $after
	 * @return array the rendered page under 'out', the child's exit status under 'exit'
	 */
	private function renderOnlinePage($before = '', $after = '')
	{
		$php = "chdir('".addslashes(APP_PATH)."'); ";
		$php .= "register_shutdown_function(function() { while(ob_get_level() > 0) { @ob_end_flush(); } }); ";
		$php .= "e107::getConfig('history')->setPref('most_members_online', ".self::MOST_MEMBERS.")";
		$php .= "->setPref('most_guests_online', ".self::MOST_GUESTS.")->setPref('most_online_datestamp', time()); ";
		$php .= $before;
		$php .= "require_once('".addslashes(APP_PATH.'/online.php')."'); ";
		$php .= $after;

		list($output, $status) = $this->runInBootedCli($php);

		return array('out' => implode("\n", $output), 'exit' => $status);
	}

	/**
	 * A theme that prints either token gets the figure its name says. Nothing in the tree reads them, so the page itself proves nothing: the probe stands in for the theme.
	 *
	 * @see https://github.com/e107inc/e107/issues/6522
	 */
	public function testTheMemberAndGuestTokensCarryTheirOwnFigures()
	{
		e107::coreLan('online');
		$this->seedMembersOnline();

		$probe = "@@MEMBERS_TOKEN={ONLINE_TABLE_MEMBERS_ONLINE}@@\n@@GUESTS_TOKEN={ONLINE_TABLE_GUESTS_ONLINE}@@";

		$result = $this->renderOnlinePage(
			'$ONLINE_TABLE_MISC = '.var_export($probe, true).'; ',
			'echo "\n@@COUNTS=", MEMBERS_ONLINE, "/", GUESTS_ONLINE, "@@\n"; ');

		$out = $result['out'];

		self::assertSame(0, $result['exit'], $out);

		$counts = array();
		self::assertSame(1, preg_match('/@@COUNTS=(\d+)\/(\d+)@@/', $out, $counts),
			"The page never reported the counts the tokens were built from.\n".$out);
		self::assertSame((string) self::SEEDED_MEMBERS, $counts[1],
			"The seeded member rows never reached the count, so the member figure is a constant zero.\n".$out);
		self::assertNotSame($counts[1], $counts[2],
			"The two counts have to differ, or a token carrying the other one's figure would still read correctly.\n".$out);

		self::assertStringContainsString('@@MEMBERS_TOKEN='.ONLINE_EL2.$counts[1].'@@', $out,
			"ONLINE_TABLE_MEMBERS_ONLINE has to carry the member label and the member count.\n".$out);
		self::assertStringContainsString('@@GUESTS_TOKEN='.ONLINE_EL1.$counts[2].'@@', $out,
			"ONLINE_TABLE_GUESTS_ONLINE has to carry the guest label and the guest count.\n".$out);
	}
}
