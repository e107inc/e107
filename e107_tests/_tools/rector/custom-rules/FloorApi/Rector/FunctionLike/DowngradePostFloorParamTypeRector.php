<?php

declare(strict_types=1);

namespace E107\Rector\FloorApi\Rector\FunctionLike;

use E107\Rector\DowngradePhp72\PhpDoc\NativeParamToPhpDocDecorator;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Exception\ShouldNotHappenException;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Moves a parameter type naming a class the floor lacks into the docblock.
 *
 * @changelog https://www.php.net/manual/en/migration80.incompatible.php#migration80.incompatible.resource2object
 */
final class DowngradePostFloorParamTypeRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string[]
     */
    private array $postFloorClasses = [];

    public function __construct(
        private readonly NativeParamToPhpDocDecorator $nativeParamToPhpDocDecorator
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Move a parameter type that names a class the floor does not have into a @param docblock',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
function validateRsaKeyLength(OpenSSLAsymmetricKey $key)
{
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
/**
 * @param \OpenSSLAsymmetricKey $key
 */
function validateRsaKeyLength($key)
{
}
CODE_SAMPLE
                    ,
                    ['OpenSSLAsymmetricKey']
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = false;

        foreach ($node->params as $param) {
            if (! $this->isPostFloorClass($param->type)) {
                continue;
            }

            $this->nativeParamToPhpDocDecorator->decorate($node, $param);
            $param->type = null;
            $hasChanged = true;
        }

        if (! $hasChanged) {
            return null;
        }

        return $node;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        foreach ($configuration as $className) {
            if (! is_string($className)) {
                throw new ShouldNotHappenException(self::class . ' takes a list of class names.');
            }
        }

        $this->postFloorClasses = array_values($configuration);
    }

    private function isPostFloorClass(?Node $type): bool
    {
        if ($this->postFloorClasses === []) {
            return false;
        }

        if ($type instanceof NullableType) {
            $type = $type->type;
        }

        if (! $type instanceof Name) {
            return false;
        }

        return $this->isNames($type, $this->postFloorClasses);
    }
}
