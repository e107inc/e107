<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * v2 compatibility layer for the e107 SQL API.
 *
 * The implementation lives in the namespaced classes under
 * e107_handlers/Database/ (the e107\Database tree), which new code should
 * reference directly; the e107 namespaced autoloader picks them up with no
 * registration. Loading this file registers every v2-style class name as a
 * true alias of its namespaced class, so v2-era code keeps working
 * unchanged: `implements e_db`, `use e_db_common`, `instanceof e_db_sql`,
 * `catch (e_db_query_exception ...)`, parameter type hints and string class
 * names all behave identically to the namespaced names.
 *
 * The connection classes (e_db_pdo_class.php, mysql_class.php) require this
 * file before they bind the `e_db` contract, so the aliases exist on any
 * page with a database. Standalone code that wants the v2 names without
 * loading a connection first should require this file itself:
 *
 *     require_once(e_HANDLER.'e_db_interface.php');
 *
 * The namespaced files are required directly rather than autoloaded so the
 * aliases also work in bootstrap contexts that run without the e107
 * autoloader (MYSQL_LIGHT, the installer).
 */

require_once(__DIR__.'/Database/ConnectionInterface.php');
class_alias(\e107\Database\ConnectionInterface::class, 'e_db');

require_once(__DIR__.'/Database/ConnectionTrait.php');
class_alias(\e107\Database\ConnectionTrait::class, 'e_db_common');

require_once(__DIR__.'/Database/Exception/QueryException.php');
class_alias(\e107\Database\Exception\QueryException::class, 'e_db_query_exception');

require_once(__DIR__.'/Database/Exception/UnsupportedException.php');
class_alias(\e107\Database\Exception\UnsupportedException::class, 'e_db_unsupported_exception');

require_once(__DIR__.'/Database/IdentifierFilter.php');
class_alias(\e107\Database\IdentifierFilter::class, 'e_db_filter');

require_once(__DIR__.'/Database/SqlFragment.php');
class_alias(\e107\Database\SqlFragment::class, 'e_db_sql');

require_once(__DIR__.'/Database/ExpressionBuilder.php');
class_alias(\e107\Database\ExpressionBuilder::class, 'e_db_expr');

require_once(__DIR__.'/Database/QueryBuilder.php');
class_alias(\e107\Database\QueryBuilder::class, 'e_db_query');

require_once(__DIR__.'/Database/Platform/PlatformInterface.php');
class_alias(\e107\Database\Platform\PlatformInterface::class, 'e_db_platform');

require_once(__DIR__.'/Database/Platform/MysqlPlatform.php');
class_alias(\e107\Database\Platform\MysqlPlatform::class, 'e_db_platform_mysql');

require_once(__DIR__.'/Database/Schema/SchemaBuilderTrait.php');
class_alias(\e107\Database\Schema\SchemaBuilderTrait::class, 'e_db_schema_common');

require_once(__DIR__.'/Database/Schema/Column.php');
class_alias(\e107\Database\Schema\Column::class, 'e_db_column');

require_once(__DIR__.'/Database/Schema/Index.php');
class_alias(\e107\Database\Schema\Index::class, 'e_db_index');

require_once(__DIR__.'/Database/Schema/Table.php');
class_alias(\e107\Database\Schema\Table::class, 'e_db_schema_table');

require_once(__DIR__.'/Database/Schema/SchemaBuilder.php');
class_alias(\e107\Database\Schema\SchemaBuilder::class, 'e_db_schema');
