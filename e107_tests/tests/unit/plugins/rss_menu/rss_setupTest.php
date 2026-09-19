<?php

/**
 * @see https://github.com/e107inc/e107/issues/5880
 */
class rss_setupTest extends \Test\Unit
{
	/** @var array */
	private $ids = array();

	protected function _before()
	{
		require_once(e_PLUGIN . 'rss_menu/rss_setup.php');
	}

	protected function _after()
	{
		foreach($this->ids as $id)
		{
			e107::getDb()->createQueryBuilder()->delete('rss')->where('rss_id', $id)->execute();
		}
	}

	/**
	 * The row an upgraded site carries names the feed, or nobody, where the
	 * plugin folder serving it goes now. Resolution copes with all of those, but
	 * a row left that way shows a folder that does not exist in the admin list
	 * and sends every request for the feed through the legacy scan in
	 * {@see rssCreate::addonPath()} that a named folder skips.
	 */
	public function testTheUpgradePointsALegacyCommentsRowAtThePluginThatServesIt()
	{
		$textKey = $this->seedRow('comments', 'comments');
		$numericKey = $this->seedRow('5', '');
		$zeroPath = $this->seedRow('comments', '0');
		$thirdParty = $this->seedRow('comments', 'someplugin');
		$someoneElse = $this->seedRow('news', 'news');

		$setup = new rss_menu_setup();
		$setup->upgrade_post(e107::getPlugin());

		$this::assertSame('rss_menu', $this->pathOf($textKey));
		$this::assertSame('rss_menu', $this->pathOf($numericKey));
		$this::assertSame('rss_menu', $this->pathOf($zeroPath));

		// A row naming a folder belongs to whoever that folder is.
		$this::assertSame('someplugin', $this->pathOf($thirdParty));
		$this::assertSame('news', $this->pathOf($someoneElse));
	}

	/**
	 * @param string $url rss_url
	 * @param string $path rss_path
	 * @return int rss_id
	 */
	private function seedRow($url, $path)
	{
		$db = e107::getDb();

		$id = $db->createQueryBuilder()->insert('rss')->insertGetId(array(
			'rss_name'      => 'rss_setupTest ' . $url,
			'rss_url'       => $url,
			'rss_topicid'   => '',
			'rss_path'      => $path,
			'rss_text'      => 'seeded by rss_setupTest',
			'rss_datestamp' => time(),
			'rss_class'     => '0',
			'rss_limit'     => '9',
		));

		$this::assertNotEmpty($id, 'could not seed an rss row: ' . $db->getLastErrorText());
		$this->ids[] = $id;

		return $id;
	}

	/**
	 * @param int $id rss_id
	 * @return string rss_path
	 */
	private function pathOf($id)
	{
		$row = e107::getDb()->createQueryBuilder()
			->select('rss_path')->from('rss')->where('rss_id', $id)->fetchRow();

		return (string) $row['rss_path'];
	}
}
