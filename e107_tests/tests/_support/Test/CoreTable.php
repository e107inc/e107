<?php
namespace Test;

/**
 * A table that core_sql.php or a bundled plugin's SQL file declares and the suite's database, a v2.3.0 dump with no plugin tables, lacks; a test that creates one drops it again, since the files of a batch share a database.
 */
final class CoreTable
{
	use \Helper\SchemaIntent;

	/**
	 * @param string $name without the table prefix
	 * @return void
	 */
	public static function create($name)
	{
		self::createDeclared($name, e_CORE.'sql/core_sql.php');
	}

	/**
	 * Creates a table as an SQL file declares it, on the engine and character set the plugin manager would give it, unless the database already has it.
	 *
	 * @param string $name without the table prefix
	 * @param string $file the declaring file, e.g. e_PLUGIN.'pm/pm_sql.php'
	 * @return bool whether this call created the table
	 * @throws \RuntimeException when the file does not declare the table, or the database will not create it
	 */
	public static function createDeclared($name, $file)
	{
		$db = \e107::getDb();

		if($db->isTable($name))
		{
			return false;
		}

		$declared = (new \e107\Database\Schema\Declared\SqlFileCatalogue())->parse(file_get_contents($file), basename($file, '_sql.php'));

		if(!isset($declared[$name]))
		{
			throw new \RuntimeException(basename($file).' declares no '.$name.' table');
		}

		if(!$db->schema()->createTableRaw($name, $db->createQueryBuilder()->raw($declared[$name]->getBody()), (new self())->schemaIntentFor($declared[$name])))
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
		\e107::getDb()->execute('DROP TABLE IF EXISTS `'.MPREFIX.$name.'`');
	}
}
