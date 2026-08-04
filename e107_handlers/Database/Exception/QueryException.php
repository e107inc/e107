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

use e107\Database\ConnectionInterface;
use e107\Database\QueryBuilder;
use RuntimeException;

/**
 * Thrown by a query-builder read terminal ({@see QueryBuilder::fetchAll()},
 * {@see QueryBuilder::fetchRow()}, and siblings) when the underlying
 * {@see ConnectionInterface::execute()} reports a database error.
 *
 * It separates a genuine failure from an empty result: a read terminal returns
 * an empty array() (or null for {@see QueryBuilder::fetchOne()}) only when the
 * query ran and matched no rows, and throws this when the query itself failed,
 * so callers no longer have to conflate the two.
 */
class QueryException extends RuntimeException
{
}
