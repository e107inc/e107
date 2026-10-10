<?php

namespace Sandbox;

/**
 * The sandboxes' databases on the env's server, managed as root; the suites themselves connect as the site's own account.
 */
class Databases
{
	/** @var \PDO */
	private $root;

	/** @var string */
	private $dsn;

	/** @var string */
	private $password;

	/** @var string */
	private $user;

	/** @var array<string,string[]> tables of each template, read once */
	private $tables = array();

	/**
	 * @param string $dsn the server, without a database
	 * @param string $password root's
	 * @param string $user the account the suites connect as
	 */
	public function __construct($dsn, $password, $user)
	{
		$this->dsn = $dsn;
		$this->password = $password;
		$this->user = $user;
		$this->root = new \PDO($dsn, 'root', $password, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
		$this->root->exec("SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");
	}

	/**
	 * Let the suites' account use every database whose name starts with $prefix, as a hosting panel's wildcard grant does.
	 *
	 * @param string $prefix
	 * @return void
	 */
	public function grantPrefix($prefix)
	{
		$this->root->exec('GRANT ALL PRIVILEGES ON `'.addcslashes($prefix, '_%').'%`.* TO '.$this->account());
	}

	/**
	 * @param string $name
	 * @param bool $grant whether the suites' account may use it
	 * @return void
	 */
	public function create($name, $grant)
	{
		$this->root->exec("CREATE DATABASE `$name`");
		if ($grant)
		{
			$this->root->exec("GRANT ALL PRIVILEGES ON `$name`.* TO ".$this->account());
		}
	}

	/** @param string $name */
	public function revoke($name)
	{
		$this->root->exec("REVOKE ALL PRIVILEGES ON `$name`.* FROM ".$this->account());
	}

	/**
	 * Copy a template table by table, keyed rows and all: a pool database costs less than loading the dump again.
	 *
	 * @param string $template
	 * @param string $copy
	 * @return void
	 */
	public function copy($template, $copy)
	{
		if (!isset($this->tables[$template]))
		{
			$list = $this->root->prepare("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'");
			$list->execute(array($template));
			$this->tables[$template] = $list->fetchAll(\PDO::FETCH_COLUMN);
		}
		$this->root->exec("CREATE DATABASE `$copy`");
		foreach ($this->tables[$template] as $table)
		{
			$this->root->exec("CREATE TABLE `$copy`.`$table` LIKE `$template`.`$table`");
			$this->root->exec("INSERT INTO `$copy`.`$table` SELECT * FROM `$template`.`$table`");
		}
	}

	/**
	 * Load a dump the way Codeception's Db module populates a database, through its own driver.
	 *
	 * @param string $name
	 * @param string $dump
	 * @return void
	 */
	public function load($name, $dump)
	{
		$sql = preg_replace('#/\*(?!!\d+).*?\*/#s', '', file_get_contents($dump));
		\Codeception\Lib\Driver\Db::create($this->dsn.";dbname=$name", 'root', $this->password)
			->load(preg_split('#\r\n|\n|\r#', $sql, -1, PREG_SPLIT_NO_EMPTY));
	}

	/** @param string $name */
	public function drop($name)
	{
		$this->root->exec("DROP DATABASE IF EXISTS `$name`");
	}

	/**
	 * @param string $prefix
	 * @return string[]
	 */
	public function named($prefix)
	{
		return $this->root->query('SHOW DATABASES LIKE '.$this->root->quote(addcslashes($prefix, '_%').'%'))->fetchAll(\PDO::FETCH_COLUMN);
	}

	private function account()
	{
		return $this->root->quote($this->user)."@'%'";
	}
}
