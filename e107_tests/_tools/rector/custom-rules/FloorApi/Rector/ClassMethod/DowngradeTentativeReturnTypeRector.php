<?php

declare(strict_types=1);

namespace E107\Rector\FloorApi\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Php81\Enum\AttributeName;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Marks a method that carries no native return type and overrides a tentative one.
 *
 * @changelog https://php.watch/versions/8.1/ReturnTypeWillChange
 */
final class DowngradeTentativeReturnTypeRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, string[]>
     */
    private array $classesToMethods = [];

    public function __construct(
        private readonly ReflectionResolver $reflectionResolver,
        private readonly PhpAttributeGroupFactory $phpAttributeGroupFactory,
        private readonly PhpAttributeAnalyzer $phpAttributeAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add #[\ReturnTypeWillChange] where the floor leaves a method without the tentative return type its interface declares',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
class SomeUri implements \JsonSerializable
{
    public function jsonSerialize()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeUri implements \JsonSerializable
{
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
    }
}
CODE_SAMPLE
                    ,
                    [
                        'JsonSerializable' => ['jsonSerialize'],
                    ]
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->returnType instanceof Node) {
            return null;
        }

        if ($this->phpAttributeAnalyzer->hasPhpAttribute($node, AttributeName::RETURN_TYPE_WILL_CHANGE)) {
            return null;
        }

        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        if (! $this->isTentative($classReflection, $node)) {
            return null;
        }

        $node->attrGroups[] = $this->phpAttributeGroupFactory->createFromClass(
            AttributeName::RETURN_TYPE_WILL_CHANGE
        );

        return $node;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        foreach ($configuration as $className => $methodNames) {
            if (! is_string($className) || ! is_array($methodNames)) {
                throw new ShouldNotHappenException(self::class . ' takes a class name to method name list map.');
            }
        }

        $this->classesToMethods = $configuration;
    }

    private function isTentative(ClassReflection $classReflection, ClassMethod $classMethod): bool
    {
        foreach ($this->classesToMethods as $className => $methodNames) {
            if (! $this->isNames($classMethod, $methodNames)) {
                continue;
            }

            if ($classReflection->is($className)) {
                return true;
            }
        }

        return false;
    }
}
