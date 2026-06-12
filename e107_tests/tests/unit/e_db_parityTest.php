<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


/**
 * Guards the equivalence of the two e_db implementations by reflection.
 *
 * The whitelists below are the agreed, intentional differences between
 * e_db_pdo and e_db_mysql. They should only ever shrink as parity work
 * lands; additions need a justification comment.
 */
class e_db_parityTest extends \Codeception\Test\Unit
{
	/**
	 * Public methods allowed to exist only on e_db_pdo.
	 * @var string[]
	 */
	private static $allowedOnlyInPdo = array(
	);

	/**
	 * Public methods allowed to exist only on e_db_mysql.
	 * @var string[]
	 */
	private static $allowedOnlyInMysql = array(
		'db_Set_Charset',      // legacy charset setter with config guard; the modern API is setCharset()
		'db_Show_Performance', // dead legacy stub
	);

	/**
	 * Shared public methods allowed to have diverging parameter lists.
	 * @var string[]
	 */
	private static $allowedSignatureMismatches = array(
	);

	protected function _before()
	{
		require_once(e_HANDLER.'mysql_class.php');
	}

	public function testPublicMethodParity()
	{
		$pdo = $this->getPublicMethods('e_db_pdo');
		$mysql = $this->getPublicMethods('e_db_mysql');

		$missingFromMysql = array_values(array_diff($pdo, $mysql, self::$allowedOnlyInPdo));
		$missingFromPdo = array_values(array_diff($mysql, $pdo, self::$allowedOnlyInMysql));

		$this->assertSame(array(), $missingFromMysql, 'Public methods of e_db_pdo missing from e_db_mysql');
		$this->assertSame(array(), $missingFromPdo, 'Public methods of e_db_mysql missing from e_db_pdo');
	}

	public function testPublicMethodSignatureParity()
	{
		$shared = array_intersect($this->getPublicMethods('e_db_pdo'), $this->getPublicMethods('e_db_mysql'));

		$mismatches = array();
		foreach ($shared as $name)
		{
			if (in_array($name, self::$allowedSignatureMismatches, true)) continue;

			$pdoParams = $this->describeParameters(new ReflectionMethod('e_db_pdo', $name));
			$mysqlParams = $this->describeParameters(new ReflectionMethod('e_db_mysql', $name));

			if ($pdoParams !== $mysqlParams)
			{
				$mismatches[$name] = array('e_db_pdo' => $pdoParams, 'e_db_mysql' => $mysqlParams);
			}
		}

		$this->assertSame(array(), $mismatches, 'Shared public methods with diverging parameter lists');
	}

	/**
	 * @param string $class
	 * @return string[] method names, sorted
	 */
	private function getPublicMethods($class)
	{
		$reflection = new ReflectionClass($class);
		$methods = array();
		foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
		{
			if ($method->isConstructor()) continue;
			$methods[] = $method->getName();
		}
		sort($methods);
		return $methods;
	}

	/**
	 * @param ReflectionMethod $method
	 * @return string[] parameter names, '=' appended when the parameter is optional
	 */
	private function describeParameters(ReflectionMethod $method)
	{
		$params = array();
		foreach ($method->getParameters() as $param)
		{
			$params[] = $param->getName() . ($param->isOptional() ? '=' : '');
		}
		return $params;
	}
}
