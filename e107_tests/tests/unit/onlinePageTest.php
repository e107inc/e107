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
class onlinePageTest extends \Test\Unit
{
	const MOST_MEMBERS = 7;
	const MOST_GUESTS = 5;

	/** @var array<string,mixed> the tracking preferences as this test found them, absent ones held as null */
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
	}

	/**
	 * Renders online.php in a subprocess against a known site history.
	 *
	 * @return array the rendered page under 'out', the child's exit status under 'exit'
	 */
	private function renderOnlinePage()
	{
		$php = "chdir('".addslashes(APP_PATH)."'); ";
		$php .= "register_shutdown_function(function() { while(ob_get_level() > 0) { @ob_end_flush(); } }); ";
		$php .= "e107::getConfig('history')->setPref('most_members_online', ".self::MOST_MEMBERS.")";
		$php .= "->setPref('most_guests_online', ".self::MOST_GUESTS.")->setPref('most_online_datestamp', time()); ";
		$php .= "require_once('".addslashes(APP_PATH.'/online.php')."'); ";

		list($output, $status) = $this->runInBootedCli($php);

		return array('out' => implode("\n", $output), 'exit' => $status);
	}

	/**
	 * Every label on the page used to carry its own trailing space, and the conversion to array language files took them all.
	 *
	 * @see https://github.com/e107inc/e107/issues/6331
	 */
	public function testOnlineCountsAreSpacedFromTheirLabels()
	{
		e107::coreLan('online');

		$result = $this->renderOnlinePage();
		$out = $result['out'];

		self::assertSame(0, $result['exit'], $out);

		foreach(array('ONLINE_EL1' => 'guests', 'ONLINE_EL2' => 'members') as $lan => $what)
		{
			$label = preg_quote(constant($lan), '/');
			self::assertMatchesRegularExpression("/$label \\d/", $out,
				"The count of $what must be separated from its label.\n".$out);
			self::assertDoesNotMatchRegularExpression("/$label\\d/", $out,
				"The count of $what must not run into its label.\n".$out);
		}

		$total = self::MOST_MEMBERS + self::MOST_GUESTS;
		self::assertStringContainsString(ONLINE_EL8.' '.$total, $out,
			"The most-ever-online total must be separated from its label.\n".$out);
		self::assertStringContainsString(ONLINE_EL2.' '.self::MOST_MEMBERS, $out,
			"The most-members-online count must be separated from its label.\n".$out);
		self::assertStringContainsString(ONLINE_EL1.' '.self::MOST_GUESTS, $out,
			"The most-guests-online count must be separated from its label.\n".$out);
	}
}
