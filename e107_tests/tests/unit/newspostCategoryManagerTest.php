<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The news category admin in e107_admin/newspost.php offers no Manager control, and leaves the category_manager column to the database.
 *
 * @see https://github.com/e107inc/e107/issues/6738
 */
class newspostCategoryManagerTest extends \Test\Unit
{
	use \Test\ScriptClassPages;

	const TITLE = 'newspostCategoryManagerTest';

	const SCRIPT = 'e107_admin/newspost.php';

	protected function _after()
	{
		e107::getDb()->createQueryBuilder()->delete('news_category')->whereLike('category_name', self::TITLE.'%')->execute();
	}

	public function testTheCategoryFormHasNoManagerField()
	{
		$page = $this->openAsScriptClass(e_UC_NOBODY, self::SCRIPT, 'mode=cat&action=create');

		self::assertStringContainsString('category_template', $page, "the Advanced tab did not render:\n".$page);
		self::assertStringNotContainsString('category_manager', $page);
	}

	public function testTheCategoryListHasNoManagerColumnFilterOrBatch()
	{
		$page = $this->openAsScriptClass(e_UC_NOBODY, self::SCRIPT, 'mode=cat&action=list');

		self::assertStringContainsString('category_sef', $page, "the list did not render:\n".$page);
		self::assertStringNotContainsString('category_manager', $page);
	}

	public function testACreateThatStillPostsAManagerStoresTheColumnDefault()
	{
		$page = $this->openAsScriptClass(e_UC_NOBODY, self::SCRIPT, 'mode=cat&action=create', array(
			'etrigger_submit'           => 'create',
			'category_icon'             => '',
			'category_name'             => self::TITLE,
			'category_meta_description' => '',
			'category_meta_keywords'    => '',
			'category_sef'              => strtolower(self::TITLE),
			'category_manager'          => e_UC_PUBLIC,
			'category_template'         => '',
			'category_order'            => 0,
		));

		self::assertSame(array(self::TITLE => e_UC_ADMIN), $this->storedManagers(), "the category was not saved with the column default:\n".$page);
	}

	public function testAnEditThatStillPostsAManagerKeepsTheStoredValue()
	{
		$id = (int) e107::getDb()->createQueryBuilder()->insert('news_category')->insertGetId(array(
			'category_name'             => self::TITLE,
			'category_sef'              => strtolower(self::TITLE),
			'category_meta_description' => '',
			'category_manager'          => e_UC_MEMBER,
		));

		self::assertGreaterThan(0, $id, 'the category was not seeded');

		$page = $this->openAsScriptClass(e_UC_NOBODY, self::SCRIPT, 'mode=cat&action=edit&id='.$id, array(
			'etrigger_submit'  => 'update',
			'category_name'    => self::TITLE.' renamed',
			'category_sef'     => strtolower(self::TITLE),
			'category_manager' => e_UC_PUBLIC,
		));

		self::assertSame(array(self::TITLE.' renamed' => e_UC_MEMBER), $this->storedManagers(), "the edit did not keep the stored value:\n".$page);
	}

	/**
	 * @return array the category_manager of each category this test saved, by name
	 */
	private function storedManagers()
	{
		$managers = array();

		foreach(e107::getDb()->createQueryBuilder()->select('*')->from('news_category')->whereLike('category_name', self::TITLE.'%')->fetchAll() as $row)
		{
			$managers[$row['category_name']] = (int) $row['category_manager'];
		}

		return $managers;
	}
}
