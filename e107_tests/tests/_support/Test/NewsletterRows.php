<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace Test;

use e107;

/**
 * The newsletter rows a unit test needs, planted and taken away again, with the plugin installed for its table when the site lacks it.
 */
trait NewsletterRows
{
	/** @var int[] every newsletter and issue planted */
	private $newsletterRows = array();

	/** @var bool whether this test installed the newsletter plugin for its table */
	private $newsletterRowsInstalledPlugin = false;

	/**
	 * @return void
	 */
	protected function haveNewsletterTable()
	{
		if(!e107::getDb()->isTable('newsletter'))
		{
			e107::getPlugin()->install('newsletter');
			$this->newsletterRowsInstalledPlugin = true;
		}
	}

	/**
	 * @return string PHP that marks the newsletter plugin installed in a booted child, at the version its plugin.xml carries
	 */
	protected static function newsletterInstalled()
	{
		$version = (string) simplexml_load_file(e_PLUGIN.'newsletter/plugin.xml')['version'];

		return "e107::getConfig()->setPref('plug_installed/newsletter', ".var_export($version, true)."); ";
	}

	/**
	 * @param int $parent 0 for a newsletter, else the newsletter this is an issue of
	 * @param int[] $subscribers
	 * @return int the new row's id
	 */
	protected function haveNewsletter($parent, array $subscribers)
	{
		$id = (int) e107::getDb()->insert('newsletter', array(
			'newsletter_datestamp'   => time(),
			'newsletter_title'       => 'Planted by '.get_class($this),
			'newsletter_text'        => '',
			'newsletter_header'      => '',
			'newsletter_footer'      => '',
			'newsletter_subscribers' => $subscribers ? chr(1).implode(chr(1), $subscribers) : '',
			'newsletter_parent'      => (int) $parent,
			'newsletter_flag'        => 0,
			'newsletter_issue'       => '',
		));

		self::assertGreaterThan(0, $id, 'the fixture could not plant a newsletter row');

		$this->newsletterRows[] = $id;

		return $id;
	}

	/**
	 * @param int $id
	 * @return string|null the row's newsletter_subscribers column; null once the row is gone
	 */
	protected function storedSubscribers($id)
	{
		$sql = e107::getDb();

		return $sql->select('newsletter', 'newsletter_subscribers', 'newsletter_id = '.(int) $id) ? $sql->fetch()['newsletter_subscribers'] : null;
	}

	/**
	 * Drops every row planted, and the table again if this test installed the plugin for it.
	 *
	 * @return void
	 */
	protected function dropNewsletterRows()
	{
		if($this->newsletterRows)
		{
			e107::getDb()->delete('newsletter', 'newsletter_id IN ('.implode(',', array_map('intval', $this->newsletterRows)).')');
		}

		$this->newsletterRows = array();

		if($this->newsletterRowsInstalledPlugin)
		{
			e107::getPlugin()->uninstall('newsletter', array('delete_tables' => true));
			$this->newsletterRowsInstalledPlugin = false;
		}
	}
}
