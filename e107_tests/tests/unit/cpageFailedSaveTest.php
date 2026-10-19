<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Saves a page through e107_admin/cpage.php as a browser posts the form a refused save brings back, when nobody touches it.
 */
class cpageFailedSaveTest extends \Test\Unit
{
	const TITLE = 'cpageFailedSaveTest';

	/** @var string the edit form the overview list opens, which saves a page and its menu alike */
	const EDIT = 'mode=overview&action=edit&id=';

	/** Entities that spell a tag, typed by an author without post_html. */
	const TYPED = '&amp;lt;i id=cfx&amp;gt;typed&amp;lt;/i&amp;gt;';

	/** @var int the chapter whose pages carry a custom text field named typed */
	private $chapter = 0;

	/** @var string[] the page columns the suite's database predates and cpage.php saves, which this test added */
	private $addedColumns = array();

	protected function _before()
	{
		\Test\CoreTable::create('admin_history');

		foreach(array('page_subtitle', 'page_metatitle', 'page_metaimage') as $column)
		{
			if(!e107::getDb()->field('page', $column) && e107::getDb()->execute('ALTER TABLE `#page` ADD `'.$column."` varchar(250) NOT NULL default ''"))
			{
				$this->addedColumns[] = $column;
			}
		}

		$this->chapter = (int) e107::getDb()->createQueryBuilder()->insert('page_chapters')->insertGetId(array(
			'chapter_parent'           => 1,
			'chapter_name'             => self::TITLE,
			'chapter_sef'              => strtolower(self::TITLE),
			'chapter_meta_description' => '',
			'chapter_fields'           => e107::serialize(array('typed' => array('title' => 'Typed', 'type' => 'text', 'writeParms' => '', 'help' => '')), 'json'),
		));

		self::assertGreaterThan(0, $this->chapter, 'the chapter was not seeded');
	}

	protected function _after()
	{
		putenv('QUERY_STRING');

		$db = e107::getDb();
		$db->createQueryBuilder()->delete('page')->whereLike('page_title', self::TITLE.'%')->execute();
		$db->createQueryBuilder()->delete('page_chapters')->where('chapter_id', $this->chapter)->execute();
		$db->createQueryBuilder()->delete('menus')->where('menu_name', self::TITLE)->execute();

		foreach($this->addedColumns as $column)
		{
			$db->execute('ALTER TABLE `#page` DROP `'.$column.'`');
		}

		\Test\CoreTable::drop('admin_history');
	}

	public function testAnUntouchedCustomFieldSurvivesARefusedSave()
	{
		$this->open('mode=page&action=create', array(
			'etrigger_submit'    => 'create',
			'page_title'         => self::TITLE,
			'page_subtitle'      => '',
			'page_sef'           => strtolower(self::TITLE),
			'page_chapter'       => $this->chapter,
			'page_template'      => 'default',
			'page_author'        => 1,
			'page_text'          => 'Text',
			'page_class'         => 0,
			'page_rating_flag'   => 0,
			'page_comment_flag'  => 0,
			'page_password'      => '',
			'page_metatitle'     => '',
			'page_metadscr'      => '',
			'page_metakeys'      => '',
			'page_metaimage'     => '',
			'page_metarobots'    => '',
			'page_order'         => 0,
			'menu_name'          => self::TITLE,
			'menu_title'         => '',
			'menu_text'          => '',
			'menu_template'      => 'default',
			'menu_class'         => 0,
			'menu_button_text'   => '',
			'menu_button_url'    => '',
			'menu_icon'          => '',
			'menu_image'         => '',
			'page_fields__typed' => self::TYPED,
		), "e107::getConfig()->set('post_html', e_UC_NOBODY)->set('post_script', e_UC_NOBODY); ");

		$written = $this->stored();
		self::assertNotNull($written, 'the page was not saved');

		$id = (int) $written['page_id'];
		$shown = $this->posted($this->open(self::EDIT.$id));
		self::assertStringContainsString('&lt;i id=cfx&gt;', $shown['page_fields__typed'], 'the edit form has to show the value with an entity in it');

		$refused = $this->open(self::EDIT.$id, array('page_title' => '', 'menu_name' => '') + $shown);
		self::assertSame($written, $this->stored(), "the save was meant to be refused:\n".$refused);

		$back = $this->posted($refused);
		self::assertNotNull($back, "the edit form did not come back:\n".$refused);
		self::assertSame($shown['page_fields__typed'], $back['page_fields__typed'], 'the form that comes back must show exactly what was posted');

		$this->open(self::EDIT.$id, array('page_title' => self::TITLE, 'menu_name' => self::TITLE) + $back);

		self::assertSame($written['page_fields'], $this->stored()['page_fields'], 'the untouched value has to be kept as its author stored it');
	}

	/**
	 * @param string $query
	 * @param array $post what the form posts, with the update trigger when it names none; empty to open the page
	 * @param string $seed PHP run before the page
	 * @return string the page
	 */
	private function open($query, array $post = array(), $seed = '')
	{
		putenv('QUERY_STRING='.$query);

		$seed = "e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_admin.php'); e107::getUser()->setData(e107::user(USERID)); ".$seed;

		if($post)
		{
			$seed .= '$_POST = '.var_export($post + array('etrigger_submit' => 'update'), true)." + array('e-token' => defset('e_TOKEN')); ";
		}

		$page = $this->renderInBootedCli('e107_admin/cpage.php', $seed);
		self::assertStringNotContainsString('Fatal error', $page, "the page died:\n".$page);

		return $page;
	}

	/**
	 * @return array|null the page this test saved
	 */
	private function stored()
	{
		$row = e107::getDb()->createQueryBuilder()->select('*')->from('page')->whereLike('page_title', self::TITLE.'%')->fetchRow();

		return $row ?: null;
	}

	/**
	 * @param string $page
	 * @return array|null what the page form on the page posts when nobody touches it, parsed as PHP parses a request body
	 */
	private function posted($page)
	{
		$document = new DOMDocument();
		$errors = libxml_use_internal_errors(true);
		$document->loadHTML('<?xml encoding="utf-8"?>'.$page);
		libxml_clear_errors();
		libxml_use_internal_errors($errors);

		$path = new DOMXPath($document);
		$form = $path->query("//form[.//*[@name='page_title']]")->item(0);

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
