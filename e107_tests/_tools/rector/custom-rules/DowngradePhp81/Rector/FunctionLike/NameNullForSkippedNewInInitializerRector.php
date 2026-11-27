<?php

declare(strict_types=1);

namespace E107\Rector\DowngradePhp81\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\PhpParser\AstResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Names null for each parameter a named-argument call skips whose default holds a `new` expression.
 *
 * Only valid beside {@see \Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector}.
 */
final class NameNullForSkippedNewInInitializerRector extends AbstractRector
{
    public function __construct(
        private readonly ReflectionResolver $reflectionResolver,
        private readonly AstResolver $astResolver,
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly ArgsAnalyzer $argsAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Name null for a skipped parameter whose default is a new expression',
            [
                new CodeSample(
                    'new Factory(init: $init);',
                    'new Factory(init: $init, pivot: null);'
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class, New_::class, FuncCall::class];
    }

    /**
     * @param MethodCall|StaticCall|New_|FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable() || ! $this->argsAnalyzer->hasNamedArg($node->getArgs())) {
            return null;
        }

        $params = $this->calleeParams($node);
        if ($params === null) {
            return null;
        }

        $supplied = [];
        $highest = -1;
        foreach ($node->getArgs() as $position => $arg) {
            $index = $arg->name instanceof Identifier ? $this->positionOf($params, $arg->name->toString()) : $position;
            if ($index === null) {
                return null;
            }

            $supplied[$index] = true;
            $highest = max($highest, $index);
        }

        $added = false;
        for ($index = 0; $index < $highest; ++$index) {
            if (isset($supplied[$index]) || ! isset($params[$index]) || ! $this->defaultHoldsNew($params[$index])) {
                continue;
            }

            $node->args[] = new Arg(new ConstFetch(new Name('null')), false, false, [], new Identifier($this->getName($params[$index]->var)));
            $added = true;
        }

        return $added ? $node : null;
    }

    /**
     * @param MethodCall|StaticCall|New_|FuncCall $node
     * @return Param[]|null
     */
    private function calleeParams(Node $node): ?array
    {
        $reflection = $node instanceof New_
            ? $this->reflectionResolver->resolveMethodReflectionFromNew($node)
            : $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);

        $callee = null;
        if ($reflection instanceof MethodReflection) {
            $callee = $this->astResolver->resolveClassMethodFromMethodReflection($reflection);
        } elseif ($reflection instanceof FunctionReflection) {
            $callee = $this->astResolver->resolveFunctionFromFunctionReflection($reflection);
        }

        return $callee === null ? null : $callee->params;
    }

    private function defaultHoldsNew(Param $param): bool
    {
        return $param->default instanceof Expr
            && $param->var instanceof Variable
            && is_string($param->var->name)
            && ! $param->type instanceof IntersectionType
            && $this->betterNodeFinder->findFirstInstanceOf($param->default, New_::class) instanceof New_;
    }

    /**
     * @param Param[] $params
     */
    private function positionOf(array $params, string $name): ?int
    {
        foreach ($params as $index => $param) {
            if ($param->var instanceof Variable && $this->isName($param->var, $name)) {
                return $index;
            }
        }

        return null;
    }
}
