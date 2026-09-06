<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Declared;

use e107\Database\Schema\Introspect\TableSchema;

/**
 * Settles the engine and character set a declared table is created with on this server: {@see \db_verify::resolve()} implements it.
 */
interface EngineCharsetResolverInterface
{
	/**
	 * @param DeclaredTable $table
	 * @param TableSchema|null $live the table as it stands, when it exists
	 * @return array ['engine' => string, 'charset' => string], both non-empty
	 */
	public function resolve(DeclaredTable $table, $live = null);
}
