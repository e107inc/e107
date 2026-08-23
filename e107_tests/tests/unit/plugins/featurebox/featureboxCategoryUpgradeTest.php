<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 *
 * Issue #5994 on a site that already has featurebox_category: db_verify has to
 * reach the new column, every {FEATUREBOX|x} already in a layout has to keep
 * resolving in the window before the backfill, and the upgrade has to release
 * UNIQUE KEY fb_category_template, which db_verify never drops for it.
 *
 * Each test starts from the pre-upgrade shape and _after puts the table back as
 * it was found.
 */
class featureboxCategoryUpgradeTest extends \Test\Unit
{
	/** @var db_verify */
	private $dbv;

	/** @var bool */
	private $hadSefColumn;

	/** @var bool */
	private $hadTemplateKey;

	/** @var array[] */
	private $rows = array();

	protected function _before()
	{
		require_once(e_HANDLER."db_verify_class.php");
		require_once(e_PLUGIN."featurebox/featurebox_setup.php");

		$sql = e107::getDb();

		if(!$sql->isTable('featurebox_category'))
		{
			$this->markTestSkipped('featurebox is not installed in this environment.');
		}

		$this->rows = (array) $sql->createQueryBuilder()->select('*')->from('featurebox_category')->fetchAll();
		$this->hadSefColumn = $this->hasColumn('fb_category_sef');
		$this->hadTemplateKey = $this->hasIndex('fb_category_template');

		$this->beUpgradedFrom();

		$this->dbv = $this->make('db_verify');
		$this->dbv->__construct();
	}

	protected function _after()
	{
		$sql = e107::getDb();
		$schema = $sql->schema();

		$sql->createQueryBuilder()->delete('featurebox_category')
			->where('fb_category_id', '>', 200)->execute();

		if($this->hadTemplateKey && !$this->hasIndex('fb_category_template'))
		{
			$schema->addIndex('featurebox_category', \e107\Database\Schema\Index::unique('fb_category_template', 'fb_category_template'));
		}
		elseif(!$this->hadTemplateKey && $this->hasIndex('fb_category_template'))
		{
			$schema->dropIndex('featurebox_category', 'fb_category_template');
		}

		if(!$this->hadSefColumn)
		{
			if($this->hasColumn('fb_category_sef'))
			{
				$schema->dropColumn('featurebox_category', 'fb_category_sef');
			}

			return;
		}

		if(!$this->hasColumn('fb_category_sef'))
		{
			$schema->addColumn('featurebox_category', 'fb_category_sef',
				\e107\Database\Schema\Column::define('VARCHAR', 200)->notNull()->defaultValue(''), 'fb_category_title');
		}

		foreach($this->rows as $row)
		{
			$sql->createQueryBuilder()->update('featurebox_category')
				->set('fb_category_sef', $row['fb_category_sef'])
				->where('fb_category_id', (int) $row['fb_category_id'])
				->execute();
		}
	}

	/**
	 * The shape every live site is in the moment before this change reaches it:
	 * no sef column, and the template still carrying the unique key.
	 *
	 * @return void
	 */
	private function beUpgradedFrom()
	{
		$schema = e107::getDb()->schema();

		if($this->hasColumn('fb_category_sef'))
		{
			$schema->dropColumn('featurebox_category', 'fb_category_sef');
		}

		if(!$this->hasIndex('fb_category_template'))
		{
			$schema->addIndex('featurebox_category', \e107\Database\Schema\Index::unique('fb_category_template', 'fb_category_template'));
		}
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	private function hasColumn($name)
	{
		foreach((array) e107::getDb()->schema()->getColumns('featurebox_category') as $column)
		{
			if(isset($column['Field']) && $column['Field'] === $name)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	private function hasIndex($name)
	{
		foreach((array) e107::getDb()->schema()->getIndexes('featurebox_category') as $index)
		{
			if(isset($index['Key_name']) && $index['Key_name'] === $name)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return void
	 */
	private function runPluginUpgrade()
	{
		$plugin = new e107plugin();
		$plugin->current_plug = array('plugin_version' => '1.1');

		$setup = new featurebox_setup();
		$setup->upgrade_post($plugin);
	}

	/**
	 * @param int $id
	 * @param string $template
	 * @param string $sef
	 * @return void
	 */
	private function addCategory($id, $template, $sef)
	{
		e107::getDb()->createQueryBuilder()->insert('featurebox_category')->valuesTyped(array(
			'fb_category_id'       => $id,
			'fb_category_title'    => 'Test '.$id,
			'fb_category_sef'      => $sef,
			'fb_category_icon'     => '',
			'fb_category_template' => $template,
			'fb_category_random'   => 0,
			'fb_category_class'    => 0,
			'fb_category_limit'    => 1,
			'fb_category_parms'    => '',
		))->execute();
	}

	public function testDatabaseVerifyReportsTheSefColumnAsMissingAndAddsIt()
	{
		self::assertFalse($this->hasColumn('fb_category_sef'), 'precondition: the site has not got the column yet');

		$this->dbv->compare('featurebox');
		$this->dbv->compileResults();

		self::assertArrayHasKey('fb_category_sef', $this->dbv->fixList['featurebox']['featurebox_category'],
			'db_verify walks what the sql file declares, so a new column is what it is best at finding');
		self::assertContains('insert', $this->dbv->fixList['featurebox']['featurebox_category']['fb_category_sef']);

		$this->dbv->runFix(array('featurebox' => array('featurebox_category' => array(
			'fb_category_sef' => $this->dbv->fixList['featurebox']['featurebox_category']['fb_category_sef'],
		))));

		self::assertTrue($this->hasColumn('fb_category_sef'));
	}

	/**
	 * db_verify adds the column but never fills it, and an admin can run Database
	 * Verify without ever running the plugin upgrade. Every layout on the site
	 * addresses its categories by template name, so that window has to resolve.
	 */
	public function testAnExistingShortcodeStillResolvesWhileEverySefIsEmpty()
	{
		e107::getDb()->schema()->addColumn('featurebox_category', 'fb_category_sef',
			\e107\Database\Schema\Column::define('VARCHAR', 200)->notNull()->defaultValue(''), 'fb_category_title');

		$category = new plugin_featurebox_category();
		$category->loadBySef('bootstrap3_carousel');

		self::assertTrue($category->hasData(), 'the seeded Carousel category answers to its template name');
		self::assertSame('bootstrap3_carousel', $category->get('fb_category_template'));
		self::assertSame('', (string) $category->get('fb_category_sef'));
	}

	public function testTheUpgradeFillsEverySefAndReleasesTheTemplate()
	{
		$this->runPluginUpgrade();

		self::assertTrue($this->hasColumn('fb_category_sef'));
		self::assertFalse($this->hasIndex('fb_category_template'),
			'db_verify never drops an index the sql file stopped declaring, so upgrade_post has to');

		$rows = (array) e107::getDb()->createQueryBuilder()->select('*')->from('featurebox_category')->fetchAll();

		self::assertNotEmpty($rows);

		foreach($rows as $row)
		{
			self::assertSame($row['fb_category_template'], $row['fb_category_sef'],
				'every category keeps the exact string its layouts already name');
		}
	}

	public function testTheUpgradeCanBeRunTwice()
	{
		$this->runPluginUpgrade();
		$before = (array) e107::getDb()->createQueryBuilder()->select('*')->from('featurebox_category')->fetchAll();

		self::assertNotEmpty($before[0]['fb_category_sef'],
			'precondition: the first run has to have done something for the second to be worth checking');

		$this->runPluginUpgrade();

		self::assertSame($before, (array) e107::getDb()->createQueryBuilder()->select('*')->from('featurebox_category')->fetchAll());
	}

	/**
	 * The ask itself: one template, two categories, each reachable on its own.
	 */
	public function testTwoCategoriesCanShareOneTemplateAndStayAddressable()
	{
		$this->runPluginUpgrade();

		$this->addCategory(201, 'theme_features', 'what-you-get');
		$this->addCategory(202, 'theme_features', 'why-us');

		$first = new plugin_featurebox_category();
		$second = new plugin_featurebox_category();

		self::assertTrue($first->loadBySef('what-you-get')->hasData());
		self::assertTrue($second->loadBySef('why-us')->hasData());

		self::assertNotSame($first->getId(), $second->getId());
		self::assertSame('theme_features', $first->get('fb_category_template'));
		self::assertSame('theme_features', $second->get('fb_category_template'));
	}

	/**
	 * BC: master matched the address in SQL under the column's collation, which is
	 * case-insensitive, so a hand-written {FEATUREBOX|Bootstrap_Tabs} resolved.
	 */
	public function testAnAddressResolvesWithoutRegardToCase()
	{
		$this->runPluginUpgrade();

		$category = new plugin_featurebox_category();

		self::assertTrue($category->loadBySef('Bootstrap3_Carousel')->hasData());
		self::assertSame('bootstrap3_carousel', $category->get('fb_category_template'));
	}

	/**
	 * Before the backfill every sef is empty, so a sef that duplicates a live
	 * address has to be caught against the address rather than against the column.
	 */
	public function testAnAddressAlreadyInUseIsSeenWhileTheSefsAreStillEmpty()
	{
		e107::getDb()->schema()->addColumn('featurebox_category', 'fb_category_sef',
			\e107\Database\Schema\Column::define('VARCHAR', 200)->notNull()->defaultValue(''), 'fb_category_title');

		$taken = plugin_featurebox_category::findByAddress('bootstrap3_carousel', false);

		self::assertNotNull($taken);
		self::assertSame('1', (string) $taken['fb_category_id']);
	}

	/**
	 * The system category is hidden from the front end by its user class, so the
	 * visible-only lookup the shortcode uses must not reach it.
	 */
	public function testTheSystemCategoryIsInvisibleToTheFrontEndLookup()
	{
		$this->runPluginUpgrade();

		self::assertNotNull(plugin_featurebox_category::findByAddress('unassigned', false));

		$category = new plugin_featurebox_category();

		self::assertFalse($category->loadBySef('unassigned')->hasData());
	}

	/**
	 * BC: the old public entry point resolves the same string, so a third-party
	 * caller that still names a template keeps working.
	 */
	public function testLoadByTemplateStillResolvesTheSameCategory()
	{
		$this->runPluginUpgrade();

		$byTemplate = new plugin_featurebox_category();
		$bySef = new plugin_featurebox_category();

		$byTemplate->loadByTemplate('bootstrap3_carousel');
		$bySef->loadBySef('bootstrap3_carousel');

		self::assertTrue($byTemplate->hasData());
		self::assertSame($bySef->getId(), $byTemplate->getId());
	}
}
