<?php
namespace Test;

/**
 * Seeds feature box items and saves them through the plugin's admin page; the using class titles its items with its TITLE constant, by which the helpers find them.
 */
trait FeatureboxItems
{
	use ScriptClassPages;

	/**
	 * @param array $fields what the form posts in place of the defaults
	 * @return array the form an item posts
	 */
	protected static function featureboxItemForm(array $fields = array())
	{
		return $fields + array(
			'fb_category' => 1,
			'fb_title'    => static::TITLE,
			'fb_image'    => '',
			'fb_text'     => 'Text',
			'fb_template' => 'default',
			'fb_imageurl' => '',
			'fb_class'    => e_UC_PUBLIC,
			'fb_order'    => 1,
		);
	}

	/**
	 * @param int $scriptClass the class that may post script
	 * @param int $id the item to update, 0 to create one
	 * @param array $form what the form posts
	 * @return string the page
	 */
	protected function saveFeatureboxItem($scriptClass, $id, array $form)
	{
		$query = $id ? 'mode=main&action=edit&id='.(int) $id : 'mode=main&action=create';

		return $this->openAsScriptClass($scriptClass, 'e107_plugins/featurebox/admin_config.php', $query,
			$form + array('etrigger_submit' => $id ? 'update' : 'create'));
	}

	/**
	 * @param array $row the columns to store in place of the defaults
	 * @return int
	 */
	protected static function seedFeatureboxItem(array $row = array())
	{
		$id = (int) \e107::getDb()->createQueryBuilder()->insert('featurebox')->insertGetId($row + self::featureboxItemForm());

		self::assertGreaterThan(0, $id, 'the item was not seeded');

		return $id;
	}

	/**
	 * @param string $column
	 * @return array the column's value in each item the class saved
	 */
	protected static function featureboxItemsColumn($column)
	{
		return \e107::getDb()->createQueryBuilder()->select($column)->from('featurebox')->whereLike('fb_title', static::TITLE.'%')->fetchColumn();
	}
}
