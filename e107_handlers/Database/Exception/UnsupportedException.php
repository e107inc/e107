<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Exception;

use e107\Database\Schema\SchemaBuilder;
use InvalidArgumentException;
use RuntimeException;

/**
 * Thrown when a database operation is not supported by the active platform.
 *
 * The schema builder ({@see SchemaBuilder}) offers verbs that only some engines
 * provide - {@see SchemaBuilder::createDatabase()} and
 * {@see SchemaBuilder::grant()} are MySQL/MariaDB-only, for instance. A platform
 * that cannot honour a verb throws this from its compile step so the gap
 * surfaces as a clear capability error rather than malformed SQL.
 *
 * It is a runtime capability gap, distinct from the InvalidArgumentException
 * family the builder raises for bad input.
 */
class UnsupportedException extends RuntimeException
{
}
