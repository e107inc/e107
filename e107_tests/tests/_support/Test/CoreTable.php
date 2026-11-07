<?php
namespace Test;

/**
 * A table that a bundled plugin's SQL file declares and the suite's database, a v2.3.0 dump with no plugin tables, lacks; a test that creates one drops it again, since the files of a batch share a database. Keep this in PHP 5.6 syntax.
 */
final class CoreTable
{
	/**
	 * Creates a table as an SQL file declares it, unless the database already has it.
	 *
	 * @param string $name without the table prefix
	 * @param string $file the declaring file, e.g. e_PLUGIN.'pm/pm_sql.php'
	 * @return bool whether this call created the table
	 * @throws \RuntimeException when the file does not declare the table, or the database will not create it
	 */
	public static function createDeclared($name, $file)
	{
		if(\e107::getDb()->isTable($name))
		{
			return false;
		}

		require_once(e_HANDLER.'db_table_admin_class.php');

		$admin = new \db_table_admin();

		if(!is_array($admin->get_table_def($name, $file)))
		{
			throw new \RuntimeException(basename($file).' declares no '.$name.' table');
		}

		if(!$admin->createTable($file, $name))
		{
			throw new \RuntimeException('Could not create the '.$name.' table '.basename($file).' declares');
		}

		return true;
	}

	/**
	 * @param string $name without the table prefix
	 * @return void
	 */
	public static function drop($name)
	{
		\e107::getDb()->gen('DROP TABLE IF EXISTS `'.MPREFIX.$name.'`');
	}
}
