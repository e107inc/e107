<?php

declare(strict_types=1);

namespace E107\Rector\FloorApi\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Eval_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\MagicConst\Line;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeVisitor;
use PHPStan\Analyser\Scope;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\FileNode;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Hoists a literal bound to a by-reference parameter into a variable, and refuses the file where that cannot be done safely.
 */
final class HoistLiteralByReferenceArgumentRector extends AbstractRector
{
    private const PURE = 'pure';

    private const CONSTANT = 'constant';

    private const EXPRESSION = 'expression';

    private const SCOPE_READERS = ['compact', 'extract', 'get_defined_vars', 'mb_parse_str', 'parse_str'];

    public function __construct(
        private readonly ReflectionResolver $reflectionResolver,
        private readonly BetterNodeFinder $betterNodeFinder,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Pass a variable holding the literal where a by-reference parameter was given the literal itself',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$payload = $legacy ?
    JWT::decode($token, $pem, ['RS256']) :
    JWT::decode($token, new Key($pem, 'RS256'));
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$headers = ['RS256'];
$payload = $legacy ?
    JWT::decode($token, $pem, $headers) :
    JWT::decode($token, new Key($pem, 'RS256'));
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FileNode::class, Function_::class, ClassMethod::class, Closure::class];
    }

    /**
     * @param FileNode|Function_|ClassMethod|Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }

        $scopeRefusal = $this->scopeRefusal($node);
        $takenNames = $this->collectVariableNames($node);
        $hasChanged = false;

        $node->stmts = $this->hoistInStmts($node->stmts, $scopeRefusal, $takenNames, $hasChanged);
        $this->traverseNodesWithCallable($node->stmts, function (Node $subNode) use ($scopeRefusal, &$takenNames, &$hasChanged): ?int {
            if ($this->opensAnotherScope($subNode)) {
                return NodeVisitor::DONT_TRAVERSE_CHILDREN;
            }

            if (NodeGroup::isStmtAwareNode($subNode) && is_array($subNode->stmts)) {
                $subNode->stmts = $this->hoistInStmts($subNode->stmts, $scopeRefusal, $takenNames, $hasChanged);
            }

            return null;
        });

        return $hasChanged ? $node : null;
    }

    /**
     * @param Stmt[] $stmts
     * @param array<string, true> $takenNames
     * @return Stmt[]
     */
    private function hoistInStmts(array $stmts, ?string $scopeRefusal, array &$takenNames, bool &$hasChanged): array
    {
        $newStmts = [];
        foreach ($stmts as $stmt) {
            foreach ($this->loopControlExprs($stmt) as $expr) {
                foreach ($this->findSites($expr) as [$arg]) {
                    $this->refuse($arg, $stmt, 'it sits in a loop condition, where the callee\'s write would carry into the next iteration');
                }
            }

            $sites = [];
            foreach ($this->evaluatedOnceExprs($stmt) as $expr) {
                foreach ($this->findSites($expr) as $site) {
                    $sites[] = $site;
                }
            }

            foreach ($sites as [$arg, $parameterName, $kind]) {
                if ($kind === self::EXPRESSION) {
                    $this->refuse($arg, $stmt, 'it is not a plain literal, so evaluating it earlier could give a different value');
                }

                if ($kind === self::CONSTANT) {
                    $this->refuse($arg, $stmt, 'a constant may be the very thing the surrounding code guards, so reading it earlier is not safe');
                }

                if ($arg->getStartLine() < 0) {
                    $this->refuse($arg, $stmt, 'another downgrade rule wrote this call, and the literals it wrote need not be independent values');
                }

                if ($scopeRefusal !== null) {
                    $this->refuse($arg, $stmt, $scopeRefusal);
                }

                $variable = new Variable($this->claimName($parameterName, $takenNames));
                $newStmts[] = new Expression(new Assign($variable, $arg->value));
                $arg->value = new Variable($variable->name);
                $hasChanged = true;
            }

            $newStmts[] = $stmt;
        }

        return $newStmts;
    }

    /**
     * @return Expr[]
     */
    private function evaluatedOnceExprs(Stmt $stmt): array
    {
        if ($stmt instanceof Expression || $stmt instanceof Return_) {
            return $stmt->expr instanceof Expr ? [$stmt->expr] : [];
        }

        if ($stmt instanceof Echo_) {
            return $stmt->exprs;
        }

        if ($stmt instanceof If_) {
            $exprs = [$stmt->cond];
            foreach ($stmt->elseifs as $elseIf) {
                $exprs[] = $elseIf->cond;
            }

            return $exprs;
        }

        if ($stmt instanceof Switch_) {
            $exprs = [$stmt->cond];
            foreach ($stmt->cases as $case) {
                if ($case->cond instanceof Expr) {
                    $exprs[] = $case->cond;
                }
            }

            return $exprs;
        }

        if ($stmt instanceof Foreach_) {
            return [$stmt->expr];
        }

        return [];
    }

    /**
     * @return Expr[]
     */
    private function loopControlExprs(Stmt $stmt): array
    {
        if ($stmt instanceof While_ || $stmt instanceof Do_) {
            return [$stmt->cond];
        }

        if ($stmt instanceof For_) {
            return array_merge($stmt->init, $stmt->cond, $stmt->loop);
        }

        return [];
    }

    /**
     * @return list<array{Arg, string, string}>
     */
    private function findSites(Expr $expr): array
    {
        $sites = [];
        $this->traverseNodesWithCallable($expr, function (Node $subNode) use (&$sites): ?int {
            if ($this->opensAnotherScope($subNode)) {
                return NodeVisitor::DONT_TRAVERSE_CHILDREN;
            }

            if ($subNode instanceof CallLike) {
                foreach ($this->findSitesInCall($subNode) as $site) {
                    $sites[] = $site;
                }
            }

            return null;
        });

        return $sites;
    }

    /**
     * @return list<array{Arg, string, string}>
     */
    private function findSitesInCall(CallLike $callLike): array
    {
        if ($callLike->isFirstClassCallable()) {
            return [];
        }

        $args = $callLike->getArgs();
        foreach ($args as $arg) {
            if ($arg->unpack || $arg->name instanceof Identifier) {
                return [];
            }
        }

        $scope = $callLike->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return [];
        }

        $reflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($callLike);
        if ($reflection === null) {
            return [];
        }

        $parameters = ParametersAcceptorSelectorVariantsWrapper::select($reflection, $callLike, $scope)->getParameters();

        $sites = [];
        foreach ($args as $position => $arg) {
            $parameter = $parameters[$position] ?? null;
            if ($parameter === null || $parameter->isVariadic() || ! $parameter->passedByReference()->yes()) {
                continue;
            }

            if ($this->isPassableByReference($arg->value)) {
                continue;
            }

            $sites[] = [$arg, $parameter->getName(), $this->literalKind($arg->value) ?? self::EXPRESSION];
        }

        return $sites;
    }

    private function literalKind(Expr $expr): ?string
    {
        if ($expr instanceof Scalar) {
            return $expr instanceof InterpolatedString || $expr instanceof Line ? null : self::PURE;
        }

        if ($expr instanceof ConstFetch) {
            return in_array($expr->name->toLowerString(), ['true', 'false', 'null'], true) ? self::PURE : self::CONSTANT;
        }

        if ($expr instanceof ClassConstFetch) {
            return self::CONSTANT;
        }

        if ($expr instanceof UnaryMinus || $expr instanceof UnaryPlus) {
            return $this->literalKind($expr->expr);
        }

        if (! $expr instanceof Array_) {
            return null;
        }

        $kind = self::PURE;
        foreach ($expr->items as $item) {
            if ($item === null || $item->byRef || $item->unpack) {
                return null;
            }

            foreach ([$item->key, $item->value] as $part) {
                if (! $part instanceof Expr) {
                    continue;
                }

                $partKind = $this->literalKind($part);
                if ($partKind === null) {
                    return null;
                }

                if ($partKind === self::CONSTANT) {
                    $kind = self::CONSTANT;
                }
            }
        }

        return $kind;
    }

    private function isPassableByReference(Expr $expr): bool
    {
        return $expr instanceof Variable
            || $expr instanceof ArrayDimFetch
            || $expr instanceof PropertyFetch
            || $expr instanceof NullsafePropertyFetch
            || $expr instanceof StaticPropertyFetch
            || $expr instanceof CallLike;
    }

    private function scopeRefusal(FileNode|Function_|ClassMethod|Closure $node): ?string
    {
        if ($node instanceof FileNode || $this->readsItsScopeDynamically($node->stmts)) {
            return 'the variable it would need is shared with code this file cannot see';
        }

        if ($this->leavesPhpMode($node)) {
            return 'the function leaves PHP mode, where the assignment could be printed as text or break the parse';
        }

        return null;
    }

    private function leavesPhpMode(Function_|ClassMethod|Closure $node): bool
    {
        $tokens = $this->file->getOldTokens();
        for ($position = $node->getStartTokenPos(); $position >= 0 && $position <= $node->getEndTokenPos(); ++$position) {
            if (isset($tokens[$position]) && $tokens[$position]->is([T_CLOSE_TAG, T_OPEN_TAG_WITH_ECHO, T_INLINE_HTML])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Stmt[] $stmts
     */
    private function readsItsScopeDynamically(array $stmts): bool
    {
        $readsDynamically = false;
        $this->traverseNodesWithCallable($stmts, function (Node $subNode) use (&$readsDynamically): ?int {
            if ($this->opensAnotherScope($subNode)) {
                return NodeVisitor::DONT_TRAVERSE_CHILDREN;
            }

            if (
                ($subNode instanceof Variable && ! is_string($subNode->name))
                || $subNode instanceof Include_
                || $subNode instanceof Eval_
                || ($subNode instanceof FuncCall && $this->isNames($subNode, self::SCOPE_READERS))
            ) {
                $readsDynamically = true;

                return NodeVisitor::STOP_TRAVERSAL;
            }

            return null;
        });

        return $readsDynamically;
    }

    private function opensAnotherScope(Node $node): bool
    {
        return $node instanceof Closure
            || $node instanceof ArrowFunction
            || $node instanceof Function_
            || $node instanceof ClassMethod
            || $node instanceof ClassLike;
    }

    /**
     * @return array<string, true>
     */
    private function collectVariableNames(Node $node): array
    {
        $names = [];
        foreach ($this->betterNodeFinder->findInstanceOf($node, Variable::class) as $variable) {
            if (is_string($variable->name)) {
                $names[$variable->name] = true;
            }
        }

        return $names;
    }

    /**
     * @param array<string, true> $takenNames
     */
    private function claimName(string $baseName, array &$takenNames): string
    {
        $name = $baseName;
        for ($suffix = 2; isset($takenNames[$name]); ++$suffix) {
            $name = $baseName . $suffix;
        }

        $takenNames[$name] = true;

        return $name;
    }

    private function refuse(Arg $arg, Stmt $stmt, string $reason): never
    {
        $line = max($arg->getStartLine(), $stmt->getStartLine());

        throw new ShouldNotHappenException(sprintf(
            '%s passes something other than a variable to a by-reference parameter, which PHP 7.1 to 7.4 refuse while compiling the call. It is not hoisted because %s.',
            $line > 0 ? $this->file->getFilePath() . ':' . $line : $this->file->getFilePath() . ', in code another downgrade rule wrote,',
            $reason
        ));
    }
}
