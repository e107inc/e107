<?php
namespace Test;

/**
 * The featurebox columns that featurebox_sql.php declares and the suite's v2.3.0 dump lacks; a test adds them in _before and restores them in _after.
 */
trait FeatureboxColumns
{
	/** @var bool[] 'table.column' => true, for the columns the database already had */
	private $featureboxColumnsPresent = array();

	/**
	 * @return array[] table, column and definition of each column the dump lacks
	 */
	protected static function featureboxLateColumns()
	{
		return array(
			array('featurebox', 'fb_fields', 'mediumtext'),
			array('featurebox_category', 'fb_category_fields', 'mediumtext'),
			array('featurebox_category', 'fb_category_sef', "varchar(200) NOT NULL DEFAULT ''"),
		);
	}

	/**
	 * @return void
	 */
	protected function addFeatureboxColumns()
	{
		foreach(self::featureboxLateColumns() as $late)
		{
			list($table, $column, $definition) = $late;

			if(self::featureboxHasColumn($table, $column))
			{
				$this->featureboxColumnsPresent[$table.'.'.$column] = true;

				continue;
			}

			\e107::getDb()->schema()->addColumn($table, $column, \e107\Database\SqlFragment::raw($definition));
		}
	}

	/**
	 * Puts each column back as {@see FeatureboxColumns::addFeatureboxColumns()} found it, present or absent.
	 *
	 * @return void
	 */
	protected function restoreFeatureboxColumns()
	{
		$schema = \e107::getDb()->schema();

		foreach(self::featureboxLateColumns() as $late)
		{
			list($table, $column, $definition) = $late;
			$had = isset($this->featureboxColumnsPresent[$table.'.'.$column]);
			$has = self::featureboxHasColumn($table, $column);

			if($had && !$has)
			{
				$schema->addColumn($table, $column, \e107\Database\SqlFragment::raw($definition));
			}
			elseif(!$had && $has)
			{
				$schema->dropColumn($table, $column);
			}
		}

		$this->featureboxColumnsPresent = array();
	}

	/**
	 * @param string $table
	 * @param string $column
	 * @return bool
	 */
	protected static function featureboxHasColumn($table, $column)
	{
		foreach((array) \e107::getDb()->schema()->getColumns($table) as $row)
		{
			if(isset($row['Field']) && $row['Field'] === $column)
			{
				return true;
			}
		}

		return false;
	}
}
