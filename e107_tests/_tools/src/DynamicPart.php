<?php

declare(strict_types=1);

namespace E107\SqliScan;

use PhpParser\Node\Expr;

/**
 * One non-literal fragment of a SQL-bearing expression, tagged with whether the
 * taint analysis judged it safe and whether it lands in an SQL identifier
 * position (table/column name) rather than a value position. The originating
 * AST node is retained so downstream analysis (e.g. request-taint tracing) can
 * inspect the actual expression.
 */
final class DynamicPart
{
    public function __construct(
        public readonly bool $safe,
        public readonly bool $identifierPosition,
        public readonly string $description,
        public readonly Expr $expr
    ) {
    }
}
