<?php

/**
 * What download's feed hands to rss_menu.
 *
 * Every link it builds is relative to e_PLUGIN, because rssCreate prefixes
 * anything without a scheme with SITEURLBASE.e_PLUGIN_ABS; an absolute string
 * here is the site path twice on a subdirectory install.
 *
 * The suite escalates a PHP diagnostic into a failure, so data() reaching its
 * return at all is what covers the undefined variable the three link
 * expressions used to read a property on.
 *
 * enc_url is asserted for its shape rather than for its effect: no feed emits an
 * enclosure for a download today, which is #6509 and not this.
 *
 * @see https://github.com/e107inc/e107/discussions/6497
 */
class download_e_rssTest extends \Codeception\Test\Unit
{
	/** The whole feed, which is more than the two rows seeded here. */
	const LIMIT = 9;

	/** The sef both fixture rows carry, so _after can take them back out again. */
	const MARKER = 'e107help-feed-probe';

	/** @var bool whether this test installed the download plugin for its tables */
	private $downloadInstalled = false;

	/** @var int */
	private $categoryId;

	/** @var int */
	private $downloadId;

	/** @var int */
	private $anonymousId;

	protected function _before()
	{
		require_once(e_PLUGIN.'download/e_rss.php');

		// Asked of the server rather than through isTable(), which answers from a
		// list the connection cached before anything in this run created a table.
		if(!e107::getDb()->gen("SHOW TABLES LIKE '".MPREFIX."download'"))
		{
			e107::getPlugin()->install('download');
			$this->downloadInstalled = true;
		}

		$this->categoryId = e107::getDb()->insert('download_category', array(
			'download_category_name'        => 'Feed fixture category',
			'download_category_description' => 'fixture',
			'download_category_icon'        => '',
			'download_category_parent'      => 0,
			'download_category_class'       => e_UC_PUBLIC,
			'download_category_order'       => 1,
			'download_category_sef'         => self::MARKER,
		));

		$this->downloadId = $this->haveDownload('Feed fixture download', 'Ahsanul');
		$this->anonymousId = $this->haveDownload('Feed fixture download without an author', '');
	}

	protected function _after()
	{
		e107::getDb()->delete('download', "download_sef = '".self::MARKER."'");
		e107::getDb()->delete('download_category', "download_category_sef = '".self::MARKER."'");

		if($this->downloadInstalled)
		{
			e107::getPlugin()->uninstall('download', array('delete_tables' => true));
			$this->downloadInstalled = false;
		}
	}

	public function testEveryLinkIsRelativeToThePluginDirectory()
	{
		$item = $this->feedItem($this->downloadId);

		$this->assertSame('download/download.php?view.'.$this->downloadId, $item['link']);
		$this->assertSame('download/download.php?list.'.$this->categoryId, $item['category_link']);
		$this->assertSame('download/request.php?'.$this->downloadId, $item['enc_url']);
	}

	/**
	 * The keys an item carries are the addon's to decide, so rss_menu reads
	 * every one of them for absence. item_id is never set by a v2 addon at all,
	 * and author is set only where there is one.
	 */
	public function testAnItemCarriesOnlyTheKeysTheAddonSet()
	{
		$item = $this->feedItem($this->anonymousId);

		$this->assertArrayNotHasKey('author', $item);
		$this->assertArrayNotHasKey('item_id', $item);

		$credited = $this->feedItem($this->downloadId);
		$this->assertSame('Ahsanul', $credited['author']);
	}

	/**
	 * @param int $id download_id
	 * @return array the item the feed built for that download
	 */
	private function feedItem($id)
	{
		$rss = new download_rss();

		foreach($rss->data(array('url' => 'download', 'id' => '', 'limit' => self::LIMIT)) as $item)
		{
			if($item['enc_url'] === 'download/request.php?'.$id)
			{
				return $item;
			}
		}

		$this->fail('The feed did not name download '.$id.', so there is nothing to assert about its links.');
	}

	/**
	 * @param string $name download_name, which carries a unique key
	 * @param string $author download_author, empty where nobody is credited
	 * @return int download_id
	 */
	private function haveDownload($name, $author)
	{
		return e107::getDb()->insert('download', array(
			'download_name'           => $name,
			'download_url'            => 'fixture.txt',
			'download_sef'            => self::MARKER,
			'download_author'         => $author,
			'download_author_email'   => 'fixture@example.com',
			'download_author_website' => '',
			'download_description'    => 'Body of '.$name,
			'download_keywords'       => '',
			'download_filesize'       => 12,
			'download_requested'      => 0,
			'download_category'       => $this->categoryId,
			'download_active'         => 1,
			'download_datestamp'      => time() - 3600,
			'download_thumb'          => '',
			'download_image'          => '',
			'download_comment'        => 0,
			'download_class'          => e_UC_PUBLIC,
			'download_mirror'         => '',
			'download_mirror_type'    => 0,
			'download_visible'        => e_UC_PUBLIC,
		));
	}
}
