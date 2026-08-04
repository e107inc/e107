<?php

declare(strict_types=1);

namespace E107\SqliScan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\PrettyPrinter;

/**
 * The safe-value vocabulary. Given one dynamic operand of a SQL expression,
 * decide whether e107's escaping/binding conventions render it injection-safe.
 *
 * Safe forms (per the SQLi-elimination plan):
 *  - an (int)/(float) cast, or intval(...)/floatval(...);
 *  - escape(...) on an e_db receiver;
 *  - toDB(...) on the tagfilter/parser ($tp or e107::getParser());
 *  - a numeric literal;
 *  - a clearly non-user constant (MPREFIX, USERID, e_CLASS_REGEXP, ...).
 *
 * Everything else is unsafe. Nested-safe operands are handled recursively so
 * (int) $x . intval($y) collapses to "all safe".
 */
final class SafetyRules
{
    /**
     * Function names that sanitise their result to a safe SQL value.
     *
     * @var array<string, true>
     */
    private const SAFE_FUNCTIONS = [
        'intval' => true,
        'floatval' => true,
    ];

    /**
     * Method names that produce a safe SQL value (receiver-checked separately).
     *
     * @var array<string, true>
     */
    private const SAFE_METHODS = [
        'escape' => true, // e_db::escape()
        'todb' => true,   // e107::getParser()->toDB() / $tp->toDB()
    ];

    /**
     * Constant names known to be non-user-controlled.
     *
     * @var array<string, true>
     */
    private const SAFE_CONSTANTS = [
        'MPREFIX' => true,
        'USERID' => true,
        'e_CLASS_REGEXP' => true,
        'TRUE' => true,
        'FALSE' => true,
        'NULL' => true,
        'PHP_INT_MAX' => true,
        'PHP_INT_MIN' => true,
    ];

    private PrettyPrinter\Standard $printer;

    public function __construct()
    {
        $this->printer = new PrettyPrinter\Standard();
    }

    public function isSafe(Expr $expr): bool
    {
        // (int) / (float) casts.
        if ($expr instanceof Expr\Cast\Int_ || $expr instanceof Expr\Cast\Double) {
            return true;
        }

        // Numeric literals.
        if ($expr instanceof Scalar\Int_ || $expr instanceof Scalar\Float_) {
            return true;
        }

        // intval(...) / floatval(...).
        if ($expr instanceof Expr\FuncCall && $expr->name instanceof Node\Name) {
            $fn = strtolower($expr->name->toString());
            if (isset(self::SAFE_FUNCTIONS[$fn])) {
                return true;
            }
        }

        // escape(...) / toDB(...) method calls.
        if ($expr instanceof Expr\MethodCall && $expr->name instanceof Node\Identifier) {
            $method = strtolower($expr->name->name);
            if (isset(self::SAFE_METHODS[$method])) {
                return true;
            }
        }

        // Non-user constants.
        if ($expr instanceof Expr\ConstFetch && $expr->name instanceof Node\Name) {
            if (isset(self::SAFE_CONSTANTS[$expr->name->toString()])) {
                return true;
            }
        }

        // A ternary is safe only if both branches are safe.
        if ($expr instanceof Expr\Ternary) {
            $cond = $expr->if ?? $expr->cond;
            return $this->isSafe($cond) && $this->isSafe($expr->else);
        }

        // Coalesce: safe only if both operands are safe.
        if ($expr instanceof Expr\BinaryOp\Coalesce) {
            return $this->isSafe($expr->left) && $this->isSafe($expr->right);
        }

        // Parenthesised / nested concat: safe iff every dynamic operand is safe.
        if ($expr instanceof Expr\BinaryOp\Concat) {
            return $this->isSafe($expr->left) && $this->isSafe($expr->right);
        }

        // Plain string literal operand inside a nested expression.
        if ($expr instanceof Scalar\String_) {
            return true;
        }

        return false;
    }

    public function describe(Expr $expr): string
    {
        try {
            $code = $this->printer->prettyPrintExpr($expr);
        } catch (\Throwable) {
            return get_class($expr);
        }
        $code = preg_replace('/\s+/', ' ', $code) ?? $code;
        return mb_strlen($code) > 60 ? mb_substr($code, 0, 57) . '...' : $code;
    }
}
