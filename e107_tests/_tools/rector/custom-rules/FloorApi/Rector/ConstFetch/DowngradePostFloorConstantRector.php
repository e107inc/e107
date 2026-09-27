<?php

declare(strict_types=1);

namespace E107\Rector\FloorApi\Rector\ConstFetch;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Exception\ShouldNotHappenException;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Replaces a read of a constant that postdates the floor with its literal value.
 *
 * @changelog https://www.php.net/manual/en/language.constants.php
 */
final class DowngradePostFloorConstantRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, bool|float|int|string>
     */
    private array $constantsToValues = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace a read of a constant the floor does not define with the value that constant holds',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
if (preg_last_error() === \PREG_JIT_STACKLIMIT_ERROR) {
    return 'JIT stack limit exhausted';
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
if (preg_last_error() === 6) {
    return 'JIT stack limit exhausted';
}
CODE_SAMPLE
                    ,
                    [
                        'PREG_JIT_STACKLIMIT_ERROR' => 6,
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
        return [ConstFetch::class];
    }

    /**
     * @param ConstFetch $node
     */
    public function refactor(Node $node): ?Expr
    {
        if (! $node->name->isFullyQualified()) {
            return null;
        }

        foreach ($this->constantsToValues as $constantName => $value) {
            if ($node->name->toString() !== $constantName) {
                continue;
            }

            return BuilderHelpers::normalizeValue($value);
        }

        return null;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        foreach ($configuration as $constantName => $value) {
            if (! is_string($constantName) || ! is_scalar($value)) {
                throw new ShouldNotHappenException(self::class . ' takes a constant name to scalar value map.');
            }
        }

        $this->constantsToValues = $configuration;
    }
}
