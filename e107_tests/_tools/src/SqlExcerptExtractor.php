<?php

declare(strict_types=1);

namespace E107\SqliScan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;

/**
 * Reconstructs a readable, single-line approximation of the SQL text a call
 * passes, substituting dynamic operands with a {expr} marker. Also reports
 * whether the reconstructed SQL begins with a DDL verb.
 */
final class SqlExcerptExtractor
{
    private const MAX_LEN = 160;

    private const DDL_KEYWORDS = [
        'CREATE', 'ALTER', 'DROP', 'TRUNCATE', 'RENAME',
    ];

    public function excerpt(Expr $expr): string
    {
        $text = $this->stringify($expr);
        $text = preg_replace('/\s+/', ' ', trim($text)) ?? $text;
        if (mb_strlen($text) > self::MAX_LEN) {
            $text = mb_substr($text, 0, self::MAX_LEN - 3) . '...';
        }
        return $text;
    }

    public function isDdl(Expr $expr): bool
    {
        $text = ltrim($this->stringify($expr));
        // Skip a leading backtick or paren that some queries start with.
        $text = ltrim($text, "`( \t\n\r");
        foreach (self::DDL_KEYWORDS as $kw) {
            if (stripos($text, $kw) === 0) {
                return true;
            }
        }
        return false;
    }

    private function stringify(Expr $expr): string
    {
        if ($expr instanceof Scalar\String_) {
            return $expr->value;
        }
        if ($expr instanceof Scalar\Int_ || $expr instanceof Scalar\Float_) {
            return (string) $expr->value;
        }
        if ($expr instanceof Expr\BinaryOp\Concat) {
            return $this->stringify($expr->left) . $this->stringify($expr->right);
        }
        if ($expr instanceof Scalar\InterpolatedString) {
            $out = '';
            foreach ($expr->parts as $segment) {
                if ($segment instanceof Node\InterpolatedStringPart) {
                    $out .= $segment->value;
                } elseif ($segment instanceof Expr\Variable && is_string($segment->name)) {
                    $out .= '{$' . $segment->name . '}';
                } else {
                    $out .= '{expr}';
                }
            }
            return $out;
        }
        if ($expr instanceof Expr\Array_) {
            return '[array]';
        }
        if ($expr instanceof Expr\Variable && is_string($expr->name)) {
            return '{$' . $expr->name . '}';
        }
        return '{expr}';
    }
}
