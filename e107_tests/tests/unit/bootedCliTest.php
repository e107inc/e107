<?php

/**
 * The guarantee {@see \Test\BootedCli} makes to every test that boots a child of its own.
 *
 * Keep this file in PHP 5.6 syntax: release/v2.3.x runs its suite on php:5.6.
 */
class bootedCliTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	/**
	 * Every booted child visits as itself, because e107 counts page hits per address and warns the ninetieth visit within five minutes off the site.
	 */
	public function testEachBootedChildVisitsAsItsOwnAddress()
	{
		$first  = $this->visit();
		$second = $this->visit($first['key']);

		self::assertTrue($first['counting'],
			'The child booted with tracking or flood control off, so nothing counts hits and this case would pass whatever the helper did.');
		self::assertNotSame($first['visitor'], $second['visitor'],
			'Two children of one run presented e107 with the same address, so the run is one visitor and its later boots meet the flood warning instead of the page under test.');
		self::assertLessThanOrEqual($first['hits'], $second['hits'],
			'Booting the second child added a hit to the first child\'s address ('.$first['visitor'].'), which is what warns a run off the site part way through.');
	}

	/**
	 * Boots a child and reports the address it visited as, encoded and for display, whether e107 counted the visit at all, and the hits standing against $key (its own address when omitted).
	 *
	 * @param string $key an encoded address, as e107 stores it
	 * @return array visitor, key, counting and hits
	 */
	private function visit($key = '')
	{
		$counted = $key === '' ? 'e107::getIPHandler()->getIP(false)' : "'".$key."'";

		list($output) = $this->runInBootedCli(
			"\$ip = ".$counted."; "
			."\$hits = e107::getDb()->retrieve('online', 'online_pagecount', \"online_ip = '\".e107::getDb()->escape(\$ip).\"' AND online_user_id = '0'\"); "
			."echo 'VISITOR=', e107::getIPHandler()->getIP(true), ' KEY=', e107::getIPHandler()->getIP(false), "
			."' COUNTING=', (int) !deftrue('e_TRACKING_DISABLED'), ' HITS=', (int) \$hits, PHP_EOL;");

		$visit = array();
		foreach($output as $line)
		{
			if(preg_match('/^VISITOR=(\S+) KEY=([0-9a-fA-F:.]+) COUNTING=([01]) HITS=(\d+)$/', $line, $visit))
			{
				return array('visitor' => $visit[1], 'key' => $visit[2], 'counting' => $visit[3] === '1', 'hits' => (int) $visit[4]);
			}
		}

		self::fail("The child never reported the visit it made. It printed:\n".implode("\n", $output));
	}
}
