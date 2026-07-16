<?php

declare(strict_types=1);

namespace E107\SqliScan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\FunctionLike;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * Best-effort, intra-procedural request-taint analysis.
 *
 * A SQL call site is "request tainted" when one of its UNSAFE dynamic parts
 * references HTTP request data: a superglobal ($_GET/$_POST/$_REQUEST/$_COOKIE/
 * $_SERVER/$_FILES) directly, or a local variable that, somewhere in the same
 * scope (function body or file-level code), is assigned from such a superglobal
 * (transitively, e.g. $a = $_GET['x']; $b = $a;).
 *
 * Scope taint sets are computed once per scope node and cached. This is a
 * heuristic prioritiser, not a sound dataflow engine: it does not model
 * branches, aliasing through arrays/objects, or cross-function flow.
 */
final class RequestTaintTracker
{
    private const SUPERGLOBALS = [
        '_GET' => true, '_POST' => true, '_REQUEST' => true,
        '_COOKIE' => true, '_SERVER' => true, '_FILES' => true,
    ];

    private NodeFinder $finder;

    /** @var \WeakMap<Node, array<string, true>> scope node => tainted var names */
    private \WeakMap $cache;

    public function __construct()
    {
        $this->finder = new NodeFinder();
        $this->cache = new \WeakMap();
    }

    /**
     * @param Node[]            $scopeStatements statements of the enclosing scope
     * @param Node              $scopeKey        node used to cache the scope's taint set
     * @param list<DynamicPart> $unsafeParts     the call's unsafe dynamic parts
     */
    public function isTainted(array $scopeStatements, Node $scopeKey, array $unsafeParts): bool
    {
        if (empty($unsafeParts)) {
            return false;
        }

        $tainted = $this->cache[$scopeKey] ?? $this->buildTaintSet($scopeStatements, $scopeKey);

        foreach ($unsafeParts as $part) {
            if ($this->exprReferencesRequest($part->expr, $tainted)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build the set of local variable names tainted by request data within the
     * scope, iterating to a fixpoint so transitive assignments propagate.
     *
     * @param Node[] $statements
     * @return array<string, true>
     */
    private function buildTaintSet(array $statements, Node $scopeKey): array
    {
        /** @var array<string, true> $tainted */
        $tainted = [];

        // Collect assignments within THIS scope only, without descending into
        // nested function-like scopes (their locals belong to other scopes).
        $assignments = $this->collectScopeAssignments($statements);

        do {
            $changed = false;
            foreach ($assignments as $assign) {
                if (!$assign->var instanceof Expr\Variable || !is_string($assign->var->name)) {
                    continue;
                }
                $name = $assign->var->name;
                if (isset($tainted[$name])) {
                    continue;
                }
                if ($this->exprReferencesRequest($assign->expr, $tainted)) {
                    $tainted[$name] = true;
                    $changed = true;
                }
            }
        } while ($changed);

        $this->cache[$scopeKey] = $tainted;
        return $tainted;
    }

    /**
     * Find every assignment in $statements that belongs to this scope, pruning
     * nested function-like bodies so their locals are not mistaken for ours.
     *
     * @param Node[] $statements
     * @return list<Expr\Assign>
     */
    private function collectScopeAssignments(array $statements): array
    {
        $collector = new class extends NodeVisitorAbstract {
            /** @var list<Expr\Assign> */
            public array $assignments = [];

            public function enterNode(Node $node)
            {
                // Do not descend into nested scopes (their assignments are not
                // part of the current scope's local variables).
                if ($node instanceof FunctionLike) {
                    return NodeTraverser::DONT_TRAVERSE_CHILDREN;
                }
                if ($node instanceof Expr\Assign) {
                    $this->assignments[] = $node;
                }
                return null;
            }
        };

        $traverser = new NodeTraverser();
        $traverser->addVisitor($collector);
        $traverser->traverse($statements);
        return $collector->assignments;
    }

    /**
     * Whether $expr reads request data: a superglobal access, or a variable in
     * the supplied tainted set, anywhere within the expression.
     *
     * @param array<string, true> $tainted
     */
    private function exprReferencesRequest(Expr $expr, array $tainted): bool
    {
        foreach ($this->finder->findInstanceOf($expr, Expr\Variable::class) as $var) {
            /** @var Expr\Variable $var */
            if (!is_string($var->name)) {
                continue;
            }
            if (isset(self::SUPERGLOBALS[$var->name])) {
                return true;
            }
            if (isset($tainted[$var->name])) {
                return true;
            }
        }
        return false;
    }
}
