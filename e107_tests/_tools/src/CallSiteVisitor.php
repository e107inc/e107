<?php

declare(strict_types=1);

namespace E107\SqliScan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\FunctionLike;
use PhpParser\NodeVisitorAbstract;

/**
 * AST visitor that finds every method call to an e_db raw-SQL method on a
 * genuine e_db receiver and turns it into a classified CallSite.
 *
 * Receiver resolution, method filtering, safety/tier classification and
 * request-taint tracing are delegated to focused collaborators; this class
 * orchestrates them per node, records the file context, and maintains the
 * enclosing-scope stack that request-taint analysis needs.
 */
final class CallSiteVisitor extends NodeVisitorAbstract
{
    /** @var list<CallSite> */
    private array $callSites = [];

    private string $file = '';

    /** @var Node[] top-level statements of the current file (global scope) */
    private array $fileStmts = [];

    /** A stable per-file sentinel keying the file-level (global) scope. */
    private ?Node $globalScopeKey = null;

    /** @var list<FunctionLike> stack of enclosing function-like scopes */
    private array $scopeStack = [];

    public function __construct(
        private readonly ReceiverTaxonomy $taxonomy,
        private readonly MethodCatalog $catalog,
        private readonly SafetyClassifier $safetyClassifier,
        private readonly TierClassifier $tierClassifier,
        private readonly SqlExcerptExtractor $excerpter,
        private readonly RequestTaintTracker $taintTracker
    ) {
    }

    /**
     * @param Node[] $stmts top-level statements of the file (for global scope)
     */
    public function setFile(string $file, array $stmts): void
    {
        $this->file = $file;
        $this->fileStmts = $stmts;
        $this->scopeStack = [];
        // Use the first top-level node as the global-scope cache key; fall back
        // to a throwaway node for empty files.
        $this->globalScopeKey = $stmts[0] ?? new Node\Stmt\Nop();
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof FunctionLike) {
            $this->scopeStack[] = $node;
            return null;
        }

        if (!$node instanceof Expr\MethodCall || !$node->name instanceof Node\Identifier) {
            return null;
        }

        $method = $node->name->name;
        if (!$this->catalog->isMethodOfInterest($method)) {
            return null;
        }

        // A bare $this->method() is the class's own method, never e_db.
        if ($this->taxonomy->isBareThisCall($node)) {
            return null;
        }

        if ($this->taxonomy->classify($node->var) !== ReceiverTaxonomy::VERDICT_INCLUDE) {
            return null;
        }

        $this->callSites[] = $this->buildCallSite($node, $method);
        return null;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof FunctionLike && !empty($this->scopeStack)
            && end($this->scopeStack) === $node) {
            array_pop($this->scopeStack);
        }
        return null;
    }

    private function buildCallSite(Expr\MethodCall $node, string $method): CallSite
    {
        $args = $node->args;
        $safety = $this->safetyClassifier->classify($method, $args);

        $sqlExpr = $this->primarySqlExpr($method, $args);
        $excerpt = $sqlExpr !== null ? $this->excerpter->excerpt($sqlExpr) : '';
        $isDdl = $sqlExpr !== null && $this->excerpter->isDdl($sqlExpr);

        $tier = $this->tierClassifier->classify($method, $safety, $isDdl);
        $requestTainted = $this->isRequestTainted($safety);

        return new CallSite(
            $this->file,
            $node->getStartLine(),
            $method,
            $this->taxonomy->describe($node->var),
            $safety->safety,
            $tier,
            $excerpt,
            $safety->reason,
            $requestTainted
        );
    }

    /**
     * Whether an unsafe dynamic part of this call traces back to request data
     * within the enclosing scope.
     */
    private function isRequestTainted(SafetyResult $safety): bool
    {
        if ($safety->safety !== CallSite::SAFETY_UNSAFE) {
            return false;
        }
        $unsafeParts = array_values(array_filter(
            $safety->dynamicParts,
            static fn (DynamicPart $p) => !$p->safe
        ));
        if (empty($unsafeParts)) {
            return false;
        }

        $scope = end($this->scopeStack);
        if ($scope instanceof FunctionLike) {
            $stmts = $scope->getStmts() ?? [];
            return $this->taintTracker->isTainted($stmts, $scope, $unsafeParts);
        }

        // Global / file-level scope.
        return $this->taintTracker->isTainted(
            $this->fileStmts,
            $this->globalScopeKey ?? new Node\Stmt\Nop(),
            $unsafeParts
        );
    }

    /**
     * The argument whose text best represents the statement, for excerpt/DDL.
     */
    private function primarySqlExpr(string $method, array $args): ?Expr
    {
        $roles = $this->catalog->sqlArguments($method);
        // Prefer the full-SQL ("sql") role, else the WHERE, else the payload.
        foreach (['sql', 'where', 'crud_arg', 'fields'] as $wanted) {
            foreach ($roles as $index => $role) {
                if ($role === $wanted && isset($args[$index]) && $args[$index] instanceof Node\Arg) {
                    return $args[$index]->value;
                }
            }
        }
        return null;
    }

    /**
     * @return list<CallSite>
     */
    public function callSites(): array
    {
        return $this->callSites;
    }
}
