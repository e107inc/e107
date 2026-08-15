<?php

declare(strict_types=1);

namespace E107\Rector\DowngradePhp71\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeCombinator;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * The implicit half of {@see DowngradeNullableTypeDeclarationRector}: a
 * parameter that is nullable only because its default is null loses its type
 * declaration and gains an `@param T|null` tag.
 *
 * Native enforcement goes with the declaration, so check the value in the body
 * wherever it matters.
 */
final class DowngradeImplicitlyNullableTypeDeclarationRector extends AbstractRector
{
    public function __construct(
        private readonly PhpDocTypeChanger $phpDocTypeChanger,
        private readonly StaticTypeMapper $staticTypeMapper,
        private readonly PhpDocInfoFactory $phpDocInfoFactory
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Function_::class, ClassMethod::class, Closure::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove a type declaration that PHP only reads as nullable because the default is null, add a @param tag instead',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $input = null)
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param mixed[]|null $input
     */
    public function run($input = null)
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = false;
        foreach ($node->params as $param) {
            if ($this->refactorParam($param, $node)) {
                $hasChanged = true;
            }
        }

        return $hasChanged ? $node : null;
    }

    private function refactorParam(Param $param, ClassMethod | Function_ | Closure $functionLike): bool
    {
        if (! $this->isImplicitlyNullable($param)) {
            return false;
        }

        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        if ($type instanceof MixedType) {
            // mixed already includes null.
            return false;
        }

        $paramName = $this->getName($param->var);
        if ($paramName === null) {
            return false;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $this->phpDocTypeChanger->changeParamType(
            $functionLike,
            $phpDocInfo,
            TypeCombinator::addNull($type),
            $param,
            $paramName
        );

        $param->type = null;

        return true;
    }

    private function isImplicitlyNullable(Param $param): bool
    {
        if (! $param->type instanceof Node) {
            return false;
        }

        // The sibling rule owns the explicit spelling.
        if ($param->type instanceof NullableType) {
            return false;
        }

        // A promoted parameter is still a property declaration until the PHP 8.0
        // downgrade splits it; this rule takes the plain parameter it leaves.
        if ($param->variadic || $param->isPromoted()) {
            return false;
        }

        return $param->default instanceof ConstFetch
            && $this->getName($param->default) !== null
            && strtolower((string) $this->getName($param->default)) === 'null';
    }
}
