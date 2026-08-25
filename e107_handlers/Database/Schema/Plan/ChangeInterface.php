<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Plan;

use e107\Database\Schema\SchemaBuilder;

/**
 * One repair a {@see FixPlan} can apply to one table.
 *
 * An implementation splices the definitions its value objects captured rather
 * than composing its own, and throws rather than rendering an empty or partial
 * statement.
 *
 * Render through {@see SchemaBuilder} and address the table with
 * {@see SchemaBuilder::tablePhysical()}, never {@see SchemaBuilder::table()},
 * which applies the multi-language lan_* routing.
 */
interface ChangeInterface
{
	/**
	 * The unprefixed logical table this change acts on, e.g. 'news'.
	 *
	 * @return string
	 */
	public function getTable();

	/**
	 * The schema file that declared the table: 'core' or a plugin folder.
	 *
	 * @return string
	 */
	public function getSqlFile();

	/**
	 * A short human-readable summary for the admin UI, e.g.
	 * "Add column `news_thumbnail`". Not SQL, and not a sentence.
	 *
	 * @return string
	 */
	public function describe();

	/**
	 * Render the change as one complete statement without a trailing semicolon,
	 * or as an ordered list of them. Never empty, and never null.
	 *
	 * @param SchemaBuilder $schema
	 * @return string|string[]
	 * @throws \RuntimeException when the change cannot be rendered.
	 */
	public function toSql(SchemaBuilder $schema);

	/**
	 * Whether the server could rewrite stored values to carry this change out, such as a character set conversion replacing what the target cannot hold.
	 *
	 * @return bool
	 */
	public function mayLoseData();
}
