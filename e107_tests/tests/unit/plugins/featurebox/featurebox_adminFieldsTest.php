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
 * Issue #6093: a category defines custom fields and its items store values for them, through the plugin's admin area.
 */
class featurebox_adminFieldsTest extends \Test\Unit
{
	use \Test\FeatureboxItems;
	use \Test\FeatureboxColumns;

	const TITLE = 'featurebox_adminFieldsTest';

	/** @var int */
	private static $templates = 0;

	protected function _before()
	{
		require_once(e_HANDLER.'db_verify_class.php');

		$this->addFeatureboxColumns();
	}

	protected function _after()
	{
		$db = e107::getDb();
		$db->createQueryBuilder()->delete('featurebox')->whereLike('fb_title', self::TITLE.'%')->execute();
		$db->createQueryBuilder()->delete('featurebox_category')->whereLike('fb_category_title', self::TITLE.'%')->execute();

		$this->restoreFeatureboxColumns();
	}

	public function testDatabaseVerifyReportsBothColumnsAsMissingAndAddsThem()
	{
		foreach(self::columns() as $table => $column)
		{
			e107::getDb()->schema()->dropColumn($table, $column);
		}

		$dbv = new db_verify();
		$dbv->compare('featurebox');
		$dbv->compileResults();

		$fix = array();

		foreach(self::columns() as $table => $column)
		{
			self::assertArrayHasKey($column, isset($dbv->fixList['featurebox'][$table]) ? $dbv->fixList['featurebox'][$table] : array(), $column.' is declared, so its absence is a missing field');
			self::assertContains('insert', $dbv->fixList['featurebox'][$table][$column]);
			$fix[$table] = array($column => array('insert'));
		}

		$dbv->runFix(array('featurebox' => $fix));

		foreach(self::columns() as $table => $column)
		{
			self::assertTrue(self::featureboxHasColumn($table, $column), $column.' was added');
		}
	}

	public function testACategoryStoresTheFieldsItDefines()
	{
		$page = $this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/featurebox/admin_config.php', 'mode=category&action=create', array(
			'fb_category_title'       => self::TITLE,
			'fb_category_sef'         => 'fbfieldstest',
			'fb_category_icon'        => '',
			'fb_category_template'    => 'fbfieldstestcat',
			'fb_category_random'      => 0,
			'fb_category_class'       => e_UC_PUBLIC,
			'fb_category_limit'       => 1,
			'fb_category_parms'       => '',
			'__e_customfields_tabs__' => 'Extras',
			'fb_category_fields'      => array(
				array('key' => 'price', 'title' => 'Price', 'type' => 'text', 'writeParms' => '', 'help' => ''),
			),
			'etrigger_submit'         => 'create',
		));

		$stored = e107::getDb()->createQueryBuilder()->select('fb_category_fields')->from('featurebox_category')
			->where('fb_category_title', self::TITLE)->fetchColumn();

		self::assertNotEmpty($stored, "the category was not saved:\n".$page);

		$fields = e107::unserialize($stored[0]);

		self::assertSame('text', $fields['price']['type']);
		self::assertSame('Price', $fields['price']['title']);
	}

	public function testAnItemStoresTheValuesOfItsCategorysFields()
	{
		$page = $this->createItem(e_UC_NOBODY, array('fb_fields__price' => '19.99'));

		self::assertSame(array('19.99'), $this->storedValues('price'), "the item was not saved:\n".$page);
	}

	public function testAnItemEditFormShowsTheStoredValue()
	{
		$id = $this->seedItem($this->seedCategory(), array('price' => '19.99'));

		$shown = $this->inputValues($this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/featurebox/admin_config.php', 'mode=main&action=edit&id='.$id));

		self::assertSame('19.99', $shown['fb_fields__price']);
	}

	public function testALinkFieldThatRunsScriptIsRefusedOutsideThePostScriptClass()
	{
		$page = $this->createItem(e_UC_NOBODY, array('fb_fields__link' => 'javascript:void(0)'));

		self::assertSame(array(), $this->storedValues('link'), "the item was saved:\n".$page);
		self::assertStringContainsString("<div class='s-message-item'><strong>Link</strong> - ", $page, 'the refusal has to name the field');
	}

	public function testThePostScriptClassMaySaveALinkFieldThatRunsScript()
	{
		$page = $this->createItem(e_UC_MAINADMIN, array('fb_fields__link' => 'javascript:void(0)'));

		self::assertSame(array('javascript:void(0)'), $this->storedValues('link'), "the item was refused:\n".$page);
	}

	/**
	 * The edit form is opened first, and the link it shows is posted back with the price changed.
	 *
	 * @dataProvider untouchedStoredAddresses
	 * @param string $address
	 */
	public function testAStoredLinkPassesUnchanged($address)
	{
		$category = $this->seedCategory();
		$id = $this->seedItem($category, array('price' => '1', 'link' => $address));
		$shown = $this->inputValues($this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/featurebox/admin_config.php', 'mode=main&action=edit&id='.$id));

		self::assertArrayHasKey('fb_fields__link', $shown, 'the form has to show the stored link');

		$page = $this->updateItem($id, $category, array('fb_fields__price' => '2', 'fb_fields__link' => $shown['fb_fields__link']));

		self::assertSame(array('price' => '2', 'link' => $address), $this->storedFields($id), "the unchanged link was refused:\n".$page);
	}

	public function testAnEditThatMovesAnItemKeepsTheValuesItPosts()
	{
		$id = $this->seedItem($this->seedCategory(), array('price' => '1', 'link' => 'https://e107.org/'));
		$priced = $this->category(array('price' => self::field('Price', 'text')));

		$page = $this->updateItem($id, $priced, array('fb_fields__price' => '2', 'fb_fields__link' => 'https://e107.org/'));

		self::assertSame($priced, $this->storedCategory($id), "the item was not moved:\n".$page);
		self::assertSame(array('price' => '2', 'link' => 'https://e107.org/'), $this->storedFields($id));
	}

	/**
	 * The edit form moves the item into a category without the url field, posting a script address for it, and an inline edit moves it back.
	 */
	public function testAValueSavedOutsideAUrlFieldIsJudgedWhenAMovePutsItInOne()
	{
		$asLink = $this->seedCategory();
		$without = $this->category(array('price' => self::field('Price', 'text')));
		$id = $this->seedItem($asLink, array('price' => '1', 'link' => 'https://e107.org/'));

		$this->updateItem($id, $without, array('fb_fields__price' => '1', 'fb_fields__link' => 'javascript:void(0)'));

		self::assertSame($without, $this->storedCategory($id), 'the edit form moves the item');

		$page = $this->moveInline($id, $asLink);

		self::assertSame($without, $this->storedCategory($id), "the item was moved back:\n".$page);
	}

	public function testAnInlineMoveJudgesAStoredValueTheNewCategoryHoldsAsALink()
	{
		$asLink = $this->category(array('link' => self::field('Link', 'url')));
		$asText = $this->category(array('link' => self::field('Link', 'text')));
		$inert = $this->seedItem($asText, array('link' => 'https://e107.org/'));
		$script = $this->seedItem($asText, array('link' => 'javascript:void(0)'));

		$this->moveInline($inert, $asLink);
		$page = $this->moveInline($script, $asLink);

		self::assertSame($asLink, $this->storedCategory($inert), 'a link that runs no script moves');
		self::assertSame($asText, $this->storedCategory($script), "the item was moved:\n".$page);
	}

	public function testABatchMoveJudgesAStoredValueTheNewCategoryHoldsAsALink()
	{
		$asLink = $this->category(array('link' => self::field('Link', 'url')));
		$asText = $this->category(array('link' => self::field('Link', 'text')));
		$inert = $this->seedItem($asText, array('link' => 'https://e107.org/'));
		$script = $this->seedItem($asText, array('link' => 'javascript:void(0)'));

		$this->moveInBatch($inert, 'fb_category__'.$asLink);
		$page = $this->moveInBatch($script, 'fb_category__'.$asLink);

		self::assertSame($asLink, $this->storedCategory($inert), 'a link that runs no script moves');
		self::assertSame($asText, $this->storedCategory($script), "the item was moved:\n".$page);
		self::assertStringContainsString("<div class='s-message-item'><strong>Link (".self::TITLE.")</strong> - ", $page, 'the refusal has to name the field and the item');
	}

	/**
	 * @return array batch triggers that would set fb_category other than through its dropdown; %1$d is the category holding the value as a link, %2$d the one holding it as text
	 */
	public function batchesBesideTheDropdown()
	{
		return array(
			'a suffixed category id' => array('fb_category__%1$dx'),
			'attach'                 => array('attach__fb_category__%1$d'),
			'deattach'               => array('deattach__fb_category__%2$d'),
			'bool'                   => array('bool__fb_category__1'),
			'attach, spelled with an empty bbcode pair' => array('attach__fb_categ[p][/p]ory__%1$d'),
			'attach, with a trailing space'             => array('attach__fb_category __%1$d'),
			'bool, spelled with an empty bbcode pair'   => array('bool__fb_categ[p][/p]ory__1'),
		);
	}

	/**
	 * @dataProvider batchesBesideTheDropdown
	 * @param string $trigger
	 */
	public function testABatchCannotSetTheCategoryOtherThanThroughItsDropdown($trigger)
	{
		$asLink = $this->category(array('link' => self::field('Link', 'url')));
		$asText = $this->category(array('link' => self::field('Link', 'text')));
		$id = $this->seedItem($asText, array('link' => 'javascript:void(0)'));

		$page = $this->moveInBatch($id, sprintf($trigger, $asLink, $asText));

		self::assertSame($asText, $this->storedCategory($id), "the item was moved:\n".$page);
	}

	public function testAMoveBetweenCategoriesThatBothHoldTheValueAsALinkKeepsIt()
	{
		$from = $this->category(array('link' => self::field('Link', 'url')));
		$to = $this->category(array('link' => self::field('Link', 'url')));
		$id = $this->seedItem($from, array('link' => 'javascript:void(0)'));

		$page = $this->moveInline($id, $to);

		self::assertSame($to, $this->storedCategory($id), "the item was not moved:\n".$page);
	}

	public function testTheCreateFormLoadsTheFieldsOfTheCategoryChosenIntoTheirTab()
	{
		$category = $this->seedCategory();
		$form = $this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/featurebox/admin_config.php', 'mode=main&action=create');

		$pane = self::targetOf('fb_category', $form);

		self::assertNotSame('', $pane, "the category dropdown loads nothing:\n".$form);

		self::assertStringContainsString('id="'.$pane.'"', $form, 'the dropdown has to target the pane of a tab');
		self::assertStringContainsString('href="#'.$pane.'"', $form, 'the dropdown has to target the pane of a tab');

		$sent = $this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/featurebox/admin_config.php', 'mode=main&action=category-change', array('fb_category' => $category), '', true);
		$start = strpos($sent, '[{"command"');
		$commands = ($start === false) ? null : json_decode((string) substr($sent, $start), true);

		self::assertIsArray($commands, "the answer is not a list of commands:\n".$sent);

		$filled = array();

		foreach($commands as $command)
		{
			$filled[$command['target']] = implode('', array_map('strval', $command['arguments']));
		}

		self::assertArrayHasKey('a[href="#'.$pane.'"]', $filled, 'the tab has to be shown');
		self::assertArrayHasKey('#'.$pane, $filled, 'the pane has to be filled');
		self::assertStringContainsString('fb_fields__price', $filled['#'.$pane], 'the pane has to receive the fields of the category chosen');
	}

	public function testAnItemFormWhoseCategoryDefinesNoFieldsHidesTheirTab()
	{
		$id = $this->seedItem($this->category(array()), array());

		$page = $this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/featurebox/admin_config.php', 'mode=main&action=edit&id='.$id);

		self::assertSame(1, preg_match('/a\[href="([^"]+)"\]\s*\{\s*display:\s*none;/', $page, $hidden), "no tab is hidden:\n".$page);
		self::assertSame(1, preg_match('/<a\b[^>]*\shref="'.preg_quote($hidden[1], '/').'"/', $page), 'the rule has to hide a tab the form has');
	}

	/**
	 * @param string $name
	 * @param string $page
	 * @return string the id of the element the named dropdown loads its answer into, '' when it loads none
	 */
	private static function targetOf($name, $page)
	{
		preg_match_all('/<select\b[^>]*>/', $page, $selects);

		foreach($selects[0] as $select)
		{
			if(preg_match('/\bname=[\'"]'.preg_quote($name, '/').'[\'"]/', $select) && preg_match('/\bdata-target=[\'"]#?([^\'"]+)[\'"]/', $select, $target))
			{
				return $target[1];
			}
		}

		return '';
	}

	/**
	 * @return array table => the custom fields column it gains
	 */
	private static function columns()
	{
		return array('featurebox' => 'fb_fields', 'featurebox_category' => 'fb_category_fields');
	}

	/**
	 * @param string $title
	 * @param string $type
	 * @return array the definition of a custom field
	 */
	private static function field($title, $type)
	{
		return array('title' => $title, 'type' => $type, 'writeParms' => '', 'help' => '');
	}

	/**
	 * @param array $definitions the fields it defines, by key
	 * @return int
	 */
	private function category(array $definitions)
	{
		self::$templates++;

		$id = (int) e107::getDb()->createQueryBuilder()->insert('featurebox_category')->insertGetId(array(
			'fb_category_title'    => self::TITLE,
			'fb_category_template' => 'fbfieldstest'.self::$templates,
			'fb_category_parms'    => '',
			'fb_category_fields'   => empty($definitions) ? null : json_encode($definitions),
		));

		self::assertGreaterThan(0, $id, 'the category was not seeded');

		return $id;
	}

	/**
	 * @return int a category defining a text field 'price' and a url field 'link'
	 */
	private function seedCategory()
	{
		return $this->category(array('price' => self::field('Price', 'text'), 'link' => self::field('Link', 'url')));
	}

	/**
	 * @param int $category
	 * @param array $values its custom field values, by key
	 * @return int
	 */
	private function seedItem($category, array $values)
	{
		return self::seedFeatureboxItem(array('fb_category' => $category, 'fb_fields' => empty($values) ? null : json_encode($values)));
	}

	/**
	 * @param int $scriptClass the class that may post script
	 * @param array $values the custom field inputs to post
	 * @return string the page
	 */
	private function createItem($scriptClass, array $values)
	{
		return $this->saveFeatureboxItem($scriptClass, 0, self::featureboxItemForm(array('fb_category' => $this->seedCategory(), 'fb_fields' => '') + $values));
	}

	/**
	 * @param int $id
	 * @param int $category the category it is saved in
	 * @param array $values the custom field inputs to post
	 * @return string the page
	 */
	private function updateItem($id, $category, array $values)
	{
		return $this->saveFeatureboxItem(e_UC_NOBODY, $id, self::featureboxItemForm(array('fb_category' => $category, 'fb_fields' => '') + $values));
	}

	/**
	 * @param int $id
	 * @param int $category
	 * @return string the page
	 */
	private function moveInline($id, $category)
	{
		return $this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/featurebox/admin_config.php', 'mode=main&action=inline&id='.$id,
			array('name' => 'fb_category', 'value' => $category, 'pk' => $id), '', true);
	}

	/**
	 * @param int $id
	 * @param string $trigger
	 * @return string the page
	 */
	private function moveInBatch($id, $trigger)
	{
		return $this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/featurebox/admin_config.php', 'mode=main&action=list',
			array('multiselect' => array($id), 'etrigger_batch' => $trigger, 'e__execute_batch' => 1));
	}

	/**
	 * @param int $id
	 * @return int
	 */
	private function storedCategory($id)
	{
		return (int) e107::getDb()->createQueryBuilder()->select('fb_category')->from('featurebox')->where('fb_id', $id)->fetchColumn()[0];
	}

	/**
	 * @param int $id
	 * @return array its custom field values by key, without their trust levels
	 */
	private function storedFields($id)
	{
		$fields = e107::unserialize(e107::getDb()->createQueryBuilder()->select('fb_fields')->from('featurebox')->where('fb_id', $id)->fetchColumn()[0]);
		unset($fields['__trust__']);

		return $fields;
	}

	/**
	 * @param string $key
	 * @return array the value under $key of each item this test saved that holds one
	 */
	private function storedValues($key)
	{
		$values = array();

		foreach(self::featureboxItemsColumn('fb_fields') as $stored)
		{
			$fields = e107::unserialize($stored);

			if(isset($fields[$key]))
			{
				$values[] = $fields[$key];
			}
		}

		return $values;
	}
}
