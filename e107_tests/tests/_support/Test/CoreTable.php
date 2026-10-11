<?php
namespace Test;

/**
 * A table core_sql.php declares that the suite's database, a v2.3.0 dump, predates; a test that creates one drops it again, since the files of a batch share a database.
 */
final class CoreTable
{
	/**
	 * @param string $name without the table prefix
	 * @return void
	 */
	public static function create($name)
	{
		require_once(e_HANDLER.'db_verify_class.php');

		$declared = (new \db_verify(false))->getSqlFileTables(file_get_contents(e_CORE.'sql/core_sql.php'));
		$ordinal = array_search($name, $declared['tables'], true);

		\e107::getDb()->execute('CREATE TABLE IF NOT EXISTS `'.MPREFIX.$name.'` ('.$declared['data'][$ordinal].') ENGINE='.$declared['engine'][$ordinal]);
	}

	/**
	 * @param string $name without the table prefix
	 * @return void
	 */
	public static function drop($name)
	{
		\e107::getDb()->execute('DROP TABLE IF EXISTS `'.MPREFIX.$name.'`');
	}
}
