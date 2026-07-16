<?php

declare(strict_types=1);

namespace E107\SqliScan;

use PhpParser\Node\ArrayItem;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;

/**
 * Decides the SAFETY verdict for one e_db call by inspecting the SQL-bearing
 * argument(s) named by MethodCatalog. Verdicts:
 *
 *  - static              no dynamic parts in the SQL string.
 *  - bound-safe          every dynamic part is provably safe (cast/escape/toDB/
 *                        const/numeric), or it is an array-form CRUD payload
 *                        whose values e107 binds and whose WHERE element (if any)
 *                        is itself safe.
 *  - assumed-safe-array  insert/update/replace whose payload is a plain variable
 *                        (e.g. $insert, $data): e107 binds array-form data, but
 *                        the array contents are not visible here, so this is
 *                        surfaced distinctly (spot-checkable) rather than treated
 *                        as either proven-safe or unsafe.
 *  - unsafe-concat       >=1 dynamic part is unsafe (string-form CRUD payload,
 *                        an unsafe WHERE element, or any unsafe concatenation in
 *                        a SQL/WHERE string).
 *
 * The verdict precisely tracks ConnectionTrait behaviour: insert/replace bind
 * array data and run a string payload as raw VALUES(...); update binds array
 * field values by type but appends the 'WHERE' element raw (and leaves 'cmd'
 * field values raw), and runs a string payload as raw SET ....
 *
 * The result carries the dynamic parts so TierClassifier and request-taint
 * analysis can reuse them.
 */
final class SafetyClassifier
{
    private const CRUD_METHODS = ['insert', 'update', 'replace'];

    public function __construct(
        private readonly MethodCatalog $catalog,
        private readonly TaintAnalyzer $taint
    ) {
    }

    public function classify(string $method, array $args): SafetyResult
    {
        // escape() is the sanitizer itself: a call site is the canonical safe
        // pattern. Its result still needs binding/concatenation review at the
        // enclosing statement, but the escape() call alone is not unsafe.
        if ($method === 'escape') {
            return new SafetyResult(
                CallSite::SAFETY_BOUND,
                [],
                'escape() sanitizer call; replace with bound parameter'
            );
        }

        if (in_array($method, self::CRUD_METHODS, true)) {
            return $this->classifyCrud($method, $args);
        }

        return $this->classifyTainted($method, $args);
    }

    /**
     * insert/update/replace: the verdict depends on the SHAPE of the payload
     * argument (array literal vs. string/concat vs. plain variable).
     */
    private function classifyCrud(string $method, array $args): SafetyResult
    {
        $arg = $this->argExpr($args, 1);

        if ($arg === null) {
            // Unresolvable (variadic/named arg): conservatively assume array.
            return new SafetyResult(
                CallSite::SAFETY_ASSUMED_ARRAY,
                [],
                'payload argument not positionally resolvable; assumed array-form'
            );
        }

        // Array literal: data values are bound by e107. The only raw path is the
        // 'WHERE' element, whose value is appended as raw SQL by update() (and
        // ignored by insert/replace) -> analyze it for unsafe concatenation.
        if ($arg instanceof Expr\Array_) {
            $whereExpr = $this->arrayElementValue($arg, 'WHERE');
            if ($whereExpr === null || $method !== 'update') {
                return new SafetyResult(
                    CallSite::SAFETY_BOUND,
                    [],
                    'array payload, values bound by e107'
                );
            }

            $parts = $this->taint->analyze($whereExpr);
            $unsafe = $this->unsafeParts($parts);
            if (empty($unsafe)) {
                return new SafetyResult(
                    CallSite::SAFETY_BOUND,
                    $parts,
                    "array payload bound; 'WHERE' element safe"
                );
            }
            return new SafetyResult(
                CallSite::SAFETY_UNSAFE,
                $parts,
                "array payload bound, but raw 'WHERE' element has " . $this->describeUnsafe($unsafe)
            );
        }

        // String literal or concatenation: raw VALUES(...) / SET ... -> taint it.
        if ($this->isStringValued($arg)) {
            $parts = $this->taint->analyze($arg);
            $unsafe = $this->unsafeParts($parts);
            if (empty($parts)) {
                // A bare string literal payload is itself raw SQL but static.
                return new SafetyResult(
                    CallSite::SAFETY_STATIC,
                    $parts,
                    'string-form payload, fully static'
                );
            }
            if (empty($unsafe)) {
                return new SafetyResult(
                    CallSite::SAFETY_BOUND,
                    $parts,
                    'string-form payload, all dynamic parts safe'
                );
            }
            return new SafetyResult(
                CallSite::SAFETY_UNSAFE,
                $parts,
                'string-form payload with ' . $this->describeUnsafe($unsafe)
            );
        }

        // Plain variable / other expression: e107 binds array-form data, but the
        // contents are invisible here. Surface as assumed-safe-array.
        return new SafetyResult(
            CallSite::SAFETY_ASSUMED_ARRAY,
            [],
            'payload is a variable (assumed array-form, values bound by e107)'
        );
    }

    /**
     * gen/execute and select/count/delete/retrieve/max: taint every SQL-bearing
     * string argument and combine.
     */
    private function classifyTainted(string $method, array $args): SafetyResult
    {
        $roles = $this->catalog->sqlArguments($method);

        /** @var list<DynamicPart> $allParts */
        $allParts = [];
        $hasParams = false;

        foreach ($roles as $index => $role) {
            if ($role === 'params') {
                $hasParams = $this->argExpr($args, $index) !== null;
                continue;
            }
            $expr = $this->argExpr($args, $index);
            if ($expr === null) {
                continue;
            }
            foreach ($this->taint->analyze($expr) as $part) {
                $allParts[] = $part;
            }
        }

        $unsafe = $this->unsafeParts($allParts);

        if (empty($allParts)) {
            $reason = $method === 'execute'
                ? ($hasParams ? 'static SQL with bound params' : 'fully static SQL')
                : 'no dynamic parts';
            return new SafetyResult(CallSite::SAFETY_STATIC, $allParts, $reason);
        }

        if (empty($unsafe)) {
            return new SafetyResult(
                CallSite::SAFETY_BOUND,
                $allParts,
                'all dynamic parts safe (cast/escape/toDB/const/numeric)'
            );
        }

        return new SafetyResult(
            CallSite::SAFETY_UNSAFE,
            $allParts,
            $this->describeUnsafe($unsafe)
        );
    }

    /**
     * @param list<DynamicPart> $parts
     * @return list<DynamicPart>
     */
    private function unsafeParts(array $parts): array
    {
        return array_values(array_filter($parts, static fn (DynamicPart $p) => !$p->safe));
    }

    /**
     * @param list<DynamicPart> $unsafe
     */
    private function describeUnsafe(array $unsafe): string
    {
        $descr = array_map(
            static fn (DynamicPart $p) => $p->description,
            array_slice($unsafe, 0, 3)
        );
        return count($unsafe) . ' unsafe dynamic part(s): ' . implode('; ', $descr);
    }

    /**
     * The value expression of an array literal element with the given string
     * key, or null if absent.
     */
    private function arrayElementValue(Expr\Array_ $array, string $key): ?Expr
    {
        foreach ($array->items as $item) {
            if (!$item instanceof ArrayItem || $item->key === null) {
                continue;
            }
            if ($item->key instanceof Scalar\String_ && $item->key->value === $key) {
                return $item->value;
            }
        }
        return null;
    }

    /**
     * True when the expression evaluates to a string-typed SQL fragment: a
     * string literal, a double-quoted/heredoc interpolation, or a concatenation.
     */
    private function isStringValued(Expr $expr): bool
    {
        return $expr instanceof Scalar\String_
            || $expr instanceof Scalar\InterpolatedString
            || $expr instanceof Expr\BinaryOp\Concat;
    }

    private function argExpr(array $args, int $index): ?Expr
    {
        if (!isset($args[$index])) {
            return null;
        }
        $arg = $args[$index];
        // Skip variadic spreads / named-arg edge cases we cannot position-map.
        if ($arg instanceof Arg && $arg->unpack) {
            return null;
        }
        return $arg instanceof Arg ? $arg->value : null;
    }
}
