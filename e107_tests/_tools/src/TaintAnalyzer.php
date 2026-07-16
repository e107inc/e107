<?php

declare(strict_types=1);

namespace E107\SqliScan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;

/**
 * Taint analysis for a single SQL-bearing expression.
 *
 * Flattens an expression (string concatenation, double-quoted/heredoc
 * interpolation, casts, calls) into an ordered list of literal text chunks and
 * dynamic parts. Each dynamic part is judged safe/unsafe by SafetyRules and is
 * tagged with whether it lands in an SQL identifier position, inferred from the
 * literal text that immediately precedes it.
 *
 * The class is deliberately conservative: anything it cannot prove safe is
 * reported unsafe, matching the fail-closed posture of the detector.
 */
final class TaintAnalyzer
{
    public function __construct(private readonly SafetyRules $rules)
    {
    }

    /**
     * @return list<DynamicPart>
     */
    public function analyze(Expr $expr): array
    {
        $parts = [];
        // The literal buffer accumulates ALL literal SQL text left-to-right and
        // is never reset, so quote-parity stays correct across multiple dynamic
        // parts (a value still inside an open quote is judged a value position
        // even after an earlier interpolation). isIdentifierPosition() only
        // consults the tail for keyword/backtick context.
        $literalBuffer = '';
        $this->walk($expr, $parts, $literalBuffer);
        return $parts;
    }

    /**
     * Recursively flatten $expr in left-to-right order. $literalBuffer carries
     * the concatenated literal SQL text seen so far so identifier-position can
     * be inferred for each dynamic part.
     *
     * @param list<DynamicPart> $parts
     */
    private function walk(Expr $expr, array &$parts, string &$literalBuffer): void
    {
        // String concatenation: descend left then right, preserving order.
        if ($expr instanceof Expr\BinaryOp\Concat) {
            $this->walk($expr->left, $parts, $literalBuffer);
            $this->walk($expr->right, $parts, $literalBuffer);
            return;
        }

        // Plain string / numeric literals contribute only literal text.
        if ($expr instanceof Scalar\String_) {
            $literalBuffer .= $expr->value;
            return;
        }
        if ($expr instanceof Scalar\Int_ || $expr instanceof Scalar\Float_) {
            $literalBuffer .= (string) $expr->value;
            return;
        }

        // Double-quoted strings and heredocs: a mix of literal parts and
        // interpolated expressions.
        if ($expr instanceof Scalar\InterpolatedString) {
            foreach ($expr->parts as $segment) {
                if ($segment instanceof Node\InterpolatedStringPart) {
                    $literalBuffer .= $segment->value;
                } elseif ($segment instanceof Expr) {
                    $this->record($segment, $parts, $literalBuffer);
                }
            }
            return;
        }

        // Anything else is a single dynamic part.
        $this->record($expr, $parts, $literalBuffer);
    }

    /**
     * Classify one dynamic operand against the cumulative literal text and
     * append it. The buffer is preserved (not reset) so quote-parity stays
     * correct for later parts; a neutral marker keeps the textual gap without
     * disturbing quote or backtick counts.
     *
     * @param list<DynamicPart> $parts
     */
    private function record(Expr $expr, array &$parts, string &$literalBuffer): void
    {
        // A numeric literal reached here (e.g. via a cast already unwrapped) is
        // not dynamic; ignore. Handled in walk(), but guard anyway.
        if ($expr instanceof Scalar\Int_ || $expr instanceof Scalar\Float_) {
            $literalBuffer .= (string) $expr->value;
            return;
        }

        $safe = $this->rules->isSafe($expr);
        $identifierPosition = $this->isIdentifierPosition($literalBuffer);
        $parts[] = new DynamicPart($safe, $identifierPosition, $this->rules->describe($expr), $expr);

        // Stand-in for the dynamic value: a quote-neutral token so subsequent
        // parts see the running context but quote/backtick parity is unchanged.
        $literalBuffer .= 'X';
    }

    /**
     * Infer whether the dynamic part lands in an SQL IDENTIFIER position (a
     * table/column NAME spliced from a variable) as opposed to a value position
     * or an appended clause fragment.
     *
     * Identifier position is asserted ONLY for the three contexts where the
     * dynamic part is unambiguously the name token itself:
     *   - immediately after an opening backtick  (`{$col}` / `#{$table}`);
     *   - immediately after a dotted qualifier    (alias.{$col});
     *   - immediately after a name-introducing keyword with nothing in between
     *     (FROM/JOIN/INTO/UPDATE/TABLE/DATABASE {$name}).
     *
     * Everything else - inside a quote, after a value operator, or simply a
     * conditionally-assembled clause fragment (e.g. a variable holding
     * " ORDER BY x" or " AND col=val") concatenated after a complete value -
     * is NOT an identifier. Defaulting the unknown case to "value position"
     * keeps such clause fragments out of the T4 (blocked) bucket; they remain
     * value/clause concatenations (T2/T3) for conversion.
     */
    private function isIdentifierPosition(string $prefix): bool
    {
        if ($prefix === '') {
            return false;
        }

        // Inside an open quote -> value position.
        if ($this->insideQuote($prefix, "'") || $this->insideQuote($prefix, '"')) {
            return false;
        }

        $trimmedRight = rtrim($prefix);
        if ($trimmedRight === '') {
            return false;
        }

        // Inside an open backtick-quoted identifier: an odd backtick count means
        // the dynamic part sits within the quoting (`{$col}`, `#{$table}`,
        // `#`.$table.`` -> the prefix ends `...`#`). This is an identifier.
        if (substr_count($prefix, '`') % 2 === 1) {
            return true;
        }

        // Dotted qualifier (alias.column) -> identifier.
        if (str_ends_with($trimmedRight, '.')) {
            return true;
        }

        // Immediately after a keyword that introduces a table/column name, with
        // nothing between the keyword and the dynamic part.
        if (preg_match('/(?:\bFROM|\bJOIN|\bINTO|\bUPDATE|\bTABLE|\bDATABASE)\s*$/i', $prefix)) {
            return true;
        }

        // Default: not a provable identifier position. Treat as value/clause
        // concatenation so conditionally-assembled clauses are not mis-tiered.
        return false;
    }

    /**
     * Whether $prefix ends inside an open quote of the given char, counting
     * only unescaped delimiters.
     */
    private function insideQuote(string $prefix, string $quote): bool
    {
        $count = 0;
        $len = strlen($prefix);
        for ($i = 0; $i < $len; $i++) {
            if ($prefix[$i] !== $quote) {
                continue;
            }
            // Skip a backslash-escaped quote.
            if ($i > 0 && $prefix[$i - 1] === '\\') {
                continue;
            }
            $count++;
        }
        return ($count % 2) === 1;
    }
}
