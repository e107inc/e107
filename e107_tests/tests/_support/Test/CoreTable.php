<?php
namespace Test;

/**
 * A table that core_sql.php or a bundled plugin's SQL file declares, or a column that core_sql.php declares, and the suite's database, a v2.3.0 dump with no plugin tables, lacks; a test that creates one drops it again, since the files of a batch share a database.
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

		$declared = self::declaration($name, $file);

		if(!$db->schema()->createTableRaw($name, $db->createQueryBuilder()->raw($declared->getBody()), (new self())->schemaIntentFor($declared)))
		{
			throw new \RuntimeException('Could not create the '.$name.' table '.basename($file).' declares');
		}

		return true;
	}

	/**
	 * Adds whichever of these columns a core table lacks, each as the server builds it from core_sql.php's declaration, in one statement that adds all or none.
	 *
	 * @param string $name the table, without the table prefix
	 * @param string[] $columns
	 * @return string[] the columns this call added
	 * @throws \RuntimeException when core_sql.php does not declare the table or a column, or the database will not add one
	 */
	public static function addColumns($name, array $columns)
	{
		$db = \e107::getDb();
		$missing = array();

		foreach($columns as $column)
		{
			if(!$db->field($name, $column))
			{
				$missing[] = $column;
			}
		}

		if(!$missing)
		{
			return array();
		}

		$declared = self::declaration($name, e_CORE.'sql/core_sql.php');
		$intent = (new self())->schemaIntentFor($declared);
		$built = (new \e107\Database\Schema\Declared\Materialiser($db, new \e107\Database\Schema\Introspect\SchemaReader($db), MPREFIX))->materialise($declared, $intent['engine'], $intent['charset']);

		$alter = $db->schema()->table($name);

		foreach($missing as $column)
		{
			$definition = $built->getColumn($column);

			if($definition === null)
			{
				throw new \RuntimeException('core_sql.php declares no '.$name.'.'.$column.' column');
			}

			$alter->addColumnRaw($db->createQueryBuilder()->raw($definition->getDdl()));
		}

		if($alter->execute() === false)
		{
			throw new \RuntimeException('Could not add the '.$name.' columns core_sql.php declares: '.implode(', ', $missing));
		}

		return $missing;
	}

	/**
	 * @param string $name the table, without the table prefix
	 * @param string[] $columns
	 * @return void
	 */
	public static function dropColumns($name, array $columns)
	{
		foreach($columns as $column)
		{
			\e107::getDb()->schema()->dropColumn($name, $column);
		}
	}

	/**
	 * @param string $name without the table prefix
	 * @return void
	 */
	public static function drop($name)
	{
		\e107::getDb()->execute('DROP TABLE IF EXISTS `'.MPREFIX.$name.'`');
	}

	/**
	 * @param string $name without the table prefix
	 * @param string $file the declaring SQL file
	 * @return \e107\Database\Schema\Declared\DeclaredTable
	 * @throws \RuntimeException when the file does not declare the table
	 */
	private static function declaration($name, $file)
	{
		$declared = (new \e107\Database\Schema\Declared\SqlFileCatalogue())->parse(file_get_contents($file), basename($file, '_sql.php'));

		if(!isset($declared[$name]))
		{
			throw new \RuntimeException(basename($file).' declares no '.$name.' table');
		}

		return $declared[$name];
	}
}
