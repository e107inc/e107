<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2026 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

namespace e107\Database;

use e107\Database\Exception\QueryException;
use e107\Database\Exception\UnsupportedException;
use e107\Database\Platform\MysqlPlatform;
use e107\Database\Platform\PlatformInterface;
use e107\Database\Schema\Column;
use e107\Database\Schema\Index;
use e107\Database\Schema\SchemaBuilder;
use e107\Database\Schema\SchemaBuilderTrait;
use e107\Database\Schema\Table;
use ReflectionClass;

	/**
	 * Proves the v2 class names remain first-class aliases of the namespaced
	 * e107\Database classes: the single compatibility file
	 * e107_handlers/e_db_interface.php registers every old name, each names
	 * the very same class entry (not a copy), and each behaves identically in
	 * instanceof and catch checks - the guarantees v2-era core and plugin
	 * code rely on.
	 */
	class V2AliasTest extends \Codeception\Test\Unit
	{
		/**
		 * Every v2 name registered by the e_db_interface.php compatibility
		 * file, mapped to (kind, namespaced class): c = class, i = interface,
		 * t = trait.
		 *
		 * @var array
		 */
		private static $aliases = array(
			'e_db'                       => array('i', ConnectionInterface::class),
			'e_db_common'                => array('t', ConnectionTrait::class),
			'e_db_filter'                => array('c', IdentifierFilter::class),
			'e_db_sql'                   => array('c', SqlFragment::class),
			'e_db_expr'                  => array('c', ExpressionBuilder::class),
			'e_db_query'                 => array('c', QueryBuilder::class),
			'e_db_query_exception'       => array('c', QueryException::class),
			'e_db_unsupported_exception' => array('c', UnsupportedException::class),
			'e_db_platform'              => array('i', PlatformInterface::class),
			'e_db_platform_mysql'        => array('c', MysqlPlatform::class),
			'e_db_schema'                => array('c', SchemaBuilder::class),
			'e_db_schema_common'         => array('t', SchemaBuilderTrait::class),
			'e_db_schema_table'          => array('c', Table::class),
			'e_db_column'                => array('c', Column::class),
			'e_db_index'                 => array('c', Index::class),
		);

		protected function _before()
		{
			// One require registers every v2 alias - the same file the
			// connection classes load before binding the e_db contract.
			require_once(e_HANDLER."e_db_interface.php");
		}

		public function testEveryV2NameAliasesItsNamespacedClass()
		{
			foreach(self::$aliases as $old => $def)
			{
				$kind = $def[0];
				$fqcn = $def[1];

				switch($kind)
				{
					case 'i':
						$this->assertTrue(interface_exists($old, false), $old.' should resolve as an interface');
						break;
					case 't':
						$this->assertTrue(trait_exists($old, false), $old.' should resolve as a trait');
						break;
					default:
						$this->assertTrue(class_exists($old, false), $old.' should resolve as a class');
				}

				// A class_alias is the same class entry, so reflection on the
				// old name reports the namespaced name.
				$reflected = new ReflectionClass($old);
				$this->assertSame($fqcn, $reflected->getName(), $old.' should alias '.$fqcn);
			}
		}

		public function testInstanceofMatchesAcrossTheAlias()
		{
			$frag = \e_db_sql::raw('1=1');

			$this->assertInstanceOf(SqlFragment::class, $frag);
			$this->assertTrue($frag instanceof \e_db_sql);
			$this->assertSame(SqlFragment::class, get_class($frag));
		}

		public function testCatchByV2NameCatchesTheNamespacedException()
		{
			$caught = null;

			try
			{
				throw new QueryException('alias catch');
			}
			catch(\e_db_query_exception $e)
			{
				$caught = $e;
			}

			$this->assertInstanceOf(QueryException::class, $caught);
			$this->assertSame('alias catch', $caught->getMessage());
		}

		public function testConnectionClassComposesTheNamespacedContract()
		{
			require_once(e_HANDLER."e_db_pdo_class.php");

			$this->assertArrayHasKey(ConnectionInterface::class, class_implements('e_db_pdo'),
				'e_db_pdo should implement the namespaced connection interface via the e_db alias');
			$this->assertArrayHasKey(ConnectionTrait::class, class_uses('e_db_pdo'),
				'e_db_pdo should compose the namespaced connection trait via the e_db_common alias');
		}
	}
