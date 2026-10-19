<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Saves an admin edit form that is refused, then reads, and saves again, the form that comes back as a browser posts it, through fixtures/AdminUiFailedSavePage.php in a booted child as the CLI main admin.
 */
class adminUiFailedSaveTest extends \Test\Unit
{
	const TABLE = 'admin_ui_failed_save';

	/** An entity typed as text, a tag typed as entities, and a bare ampersand. */
	const TYPED = '&amp;amp; &amp;lt;b&amp;gt; & x';

	/** A value the fixture's before-save hook writes through {@see e_parse::toDB()}. */
	const HOOKED = 'https://example.com/i.php?a=1&b=it\'s "x"&c=$5\\';

	protected function _before()
	{
		\Test\CoreTable::create('admin_history');

		$text = array('fs_title', 'fs_text', 'fs_url', 'fs_tags', 'fs_hidden', 'fs_icon', 'fs_image', 'fs_file', 'fs_dropdown', 'fs_checkboxes', 'fs_radio', 'fs_hooked');
		$columns = '`fs_id` int(10) unsigned NOT NULL auto_increment, ';
		foreach($text as $column)
		{
			$columns .= '`'.$column."` varchar(255) NOT NULL default '', ";
		}
		$columns .= '`fs_textarea` text, `fs_bbarea` text, PRIMARY KEY (`fs_id`)';

		e107::getDb()->execute('CREATE TABLE IF NOT EXISTS `'.MPREFIX.self::TABLE.'` ('.$columns.') ENGINE=InnoDB');
	}

	protected function _after()
	{
		putenv('QUERY_STRING');

		e107::getDb()->execute('DROP TABLE IF EXISTS `'.MPREFIX.self::TABLE.'`');
		\Test\CoreTable::drop('admin_history');
	}

	/**
	 * @return array the field, and what the browser posts for it
	 */
	public function postedValues()
	{
		$path = '&amp;amp;&amp;lt;b&amp;gt;&x.png';

		return array(
			'text'                          => array('fs_text', self::TYPED),
			'text holding no entity'        => array('fs_text', 'it\'s "quoted" <b>bold</b> AT&T'),
			'url'                           => array('fs_url', 'https://example.com/?a=&amp;amp;&b=&amp;lt;b&amp;gt;&c'),
			'tags'                          => array('fs_tags', '&amp;amp;,&amp;lt;b&amp;gt;,&'),
			'hidden'                        => array('fs_hidden', self::TYPED),
			'icon'                          => array('fs_icon', '{e_MEDIA_ICON}'.$path),
			'image'                         => array('fs_image', '{e_MEDIA_IMAGE}'.$path),
			'dropdown'                      => array('fs_dropdown', '&amp;amp;'),
			'dropdown, a tag typed twice'   => array('fs_dropdown', '&amp;lt;b&amp;gt;'),
			'dropdown, a bare ampersand'    => array('fs_dropdown', 'x&y'),
			'checkboxes'                    => array('fs_checkboxes', array('x&y', '&amp;amp;', '&amp;lt;b&amp;gt;')),
			'radio'                         => array('fs_radio', '&amp;amp;'),
			'text a before-save hook wrote' => array('fs_hooked', self::HOOKED),
			'textarea'                      => array('fs_textarea', self::TYPED),
			'bbarea'                        => array('fs_bbarea', self::TYPED),
			'file'                          => array('fs_file', '{e_MEDIA_FILE}'.$path),
		);
	}

	/**
	 * @dataProvider postedValues
	 * @param string $field
	 * @param string|array $posted
	 */
	public function testAFailedSaveShowsWhatWasPostedAndSavesWhatTheFirstSaveWould($field, $posted)
	{
		$control = $this->seed();
		$this->save($control, array($field => $posted, 'fs_title' => 'saved'));
		self::assertSame('saved', $this->stored($control, 'fs_title'), 'the control save was meant to be stored');

		$subject = $this->seed();
		$page = $this->save($subject, array($field => $posted, 'fs_title' => ''));
		$form = $this->posted($page);
		self::assertNotNull($form, "the edit form is not on the page:\n".$page);

		self::assertSame('seeded', $this->stored($subject, 'fs_title'), "the save was meant to fail:\n".$page);
		self::assertSame($posted, isset($form[$field]) ? $form[$field] : null, 'the form that comes back must show exactly what was posted');

		$form['fs_title'] = 'saved';
		$this->save($subject, $form);
		self::assertSame('saved', $this->stored($subject, 'fs_title'), 'saving the form that came back was meant to be stored');

		self::assertSame($this->stored($control, $field), $this->stored($subject, $field),
			'saving the form that came back as it stands must store what the first save would have');
	}

	/**
	 * @return array how a save whose values are valid is refused after its before-save hook has run
	 */
	public function refusals()
	{
		return array(
			'by the archive'  => array('archive'),
			'by the database' => array('database'),
		);
	}

	/**
	 * @dataProvider refusals
	 * @param string $refusal
	 */
	public function testASaveRefusedAfterItsHookShowsWhatWasPosted($refusal)
	{
		$posted = array('fs_title' => 'refused', 'fs_text' => self::TYPED, 'fs_hooked' => self::HOOKED);
		$subject = $this->seed();

		if($refusal === 'archive')
		{
			\Test\CoreTable::drop('admin_history');
		}
		else
		{
			e107::getDb()->execute('ALTER TABLE `'.MPREFIX.self::TABLE.'` DROP `fs_text`');
		}

		$page = $this->save($subject, $posted);
		$form = $this->posted($page);
		self::assertNotNull($form, "the edit form is not on the page:\n".$page);

		self::assertSame('seeded', $this->stored($subject, 'fs_title'), "the save was meant to be refused:\n".$page);
		self::assertSame($posted, array_intersect_key($form, $posted), 'the form that comes back must show exactly what was posted');
	}

	/**
	 * @return int
	 */
	private function seed()
	{
		return (int) e107::getDb()->createQueryBuilder()->insert(self::TABLE)->insertGetId(array('fs_title' => 'seeded'));
	}

	/**
	 * Posts $post as the edit form of record $id, with the triggers its submit button sends.
	 *
	 * @param int $id
	 * @param array $post
	 * @return string the page
	 */
	private function save($id, array $post)
	{
		putenv('QUERY_STRING=mode=main&action=edit&id='.$id);

		$post['etrigger_submit'] = 'update';
		$post['__after_submit_action'] = 'edit';

		$seed = 'e107::getUser()->setData(e107::user(USERID)); ';
		$seed .= '$_POST = '.var_export($post, true)." + array('e-token' => defset('e_TOKEN')); ";

		$page = $this->renderInBootedCli('e107_tests/tests/unit/fixtures/AdminUiFailedSavePage.php', $seed);
		self::assertStringNotContainsString('Fatal error', $page, "the page died:\n".$page);

		return $page;
	}

	/**
	 * @param int $id
	 * @param string $field
	 * @return string
	 */
	private function stored($id, $field)
	{
		$row = e107::getDb()->createQueryBuilder()->select($field)->from(self::TABLE)->where('fs_id', $id)->fetchRow();

		return $row[$field];
	}

	/**
	 * @param string $page
	 * @return array|null what the edit form on the page posts when nobody touches it, parsed as PHP parses a request body
	 */
	private function posted($page)
	{
		$document = new DOMDocument();
		$errors = libxml_use_internal_errors(true);
		$document->loadHTML('<?xml encoding="utf-8"?>'.$page);
		libxml_clear_errors();
		libxml_use_internal_errors($errors);

		$path = new DOMXPath($document);
		$form = $path->query("//form[.//*[@name='fs_title']]")->item(0);

		if($form === null)
		{
			return null;
		}

		$body = array();

		foreach($path->query('.//input[@name and not(@disabled)] | .//select[@name and not(@disabled)] | .//textarea[@name and not(@disabled)]', $form) as $control)
		{
			foreach($this->values($path, $control) as $value)
			{
				$body[] = rawurlencode($control->getAttribute('name')).'='.rawurlencode($value);
			}
		}

		parse_str(implode('&', $body), $posted);

		return $posted;
	}

	/**
	 * @param DOMXPath $path
	 * @param DOMElement $control an enabled input, select or text area
	 * @return string[] the values it submits
	 */
	private function values(DOMXPath $path, DOMElement $control)
	{
		if($control->nodeName === 'textarea')
		{
			return array($control->textContent);
		}

		if($control->nodeName === 'select')
		{
			$selected = $path->query('.//option[@selected]', $control);
			$option = $selected->length ? $selected->item($selected->length - 1) : $path->query('.//option', $control)->item(0);

			return $option ? array($option->getAttribute('value')) : array();
		}

		$type = strtolower($control->getAttribute('type'));

		if(in_array($type, array('submit', 'button', 'image', 'reset', 'file'), true))
		{
			return array();
		}

		if(in_array($type, array('checkbox', 'radio'), true))
		{
			return $control->hasAttribute('checked') ? array($control->getAttribute('value')) : array();
		}

		return array($control->getAttribute('value'));
	}
}
