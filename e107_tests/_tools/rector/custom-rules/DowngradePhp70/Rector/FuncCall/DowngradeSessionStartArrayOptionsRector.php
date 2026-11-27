<?php

declare(strict_types=1);

namespace E107\Rector\DowngradePhp70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector\DowngradeSessionStartArrayOptionsRectorTest
 */
final class DowngradeSessionStartArrayOptionsRector extends AbstractRector
{
    public function __construct(
        private readonly ValueResolver $valueResolver,
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Move array option of session_start($options) to before statement\'s ini_set()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
session_start([
    'cache_limiter' => 'private',
]);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
ini_set('session.cache_limiter', 'private');
session_start();
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }

        $hasChanged = false;

        // Iterate from the end so earlier splices do not shift the indexes of
        // statements we have yet to visit.
        for ($key = count($node->stmts) - 1; $key >= 0; --$key) {
            $stmt = $node->stmts[$key];

            if (! $stmt instanceof Expression) {
                continue;
            }

            if (! $stmt->expr instanceof FuncCall) {
                continue;
            }

            $funcCall = $stmt->expr;
            if ($this->shouldSkip($funcCall)) {
                continue;
            }

            /** @var Array_ $options */
            $options = $funcCall->getArgs()[0]->value;

            $iniSetExpressions = $this->createIniSetExpressions($options);
            if ($iniSetExpressions === null) {
                continue;
            }

            // Drop the array argument from session_start(...).
            unset($funcCall->args[0]);

            // Inject the ini_set() statements immediately before session_start().
            array_splice($node->stmts, $key, 0, $iniSetExpressions);

            $hasChanged = true;
        }

        if ($hasChanged) {
            return $node;
        }

        return null;
    }

    /**
     * @return Expression[]|null
     */
    private function createIniSetExpressions(Array_ $options): ?array
    {
        $expressions = [];

        foreach ($options->items as $option) {
            if (! $option instanceof ArrayItem) {
                return null;
            }

            if (! $option->key instanceof String_) {
                return null;
            }

            if (
                ! $this->valueResolver->isTrueOrFalse($option->value)
                && ! $option->value instanceof String_
                && ! $option->value instanceof Int_
                && ! $option->value instanceof Float_
            ) {
                return null;
            }

            $sessionKey = new String_('session.' . $option->key->value);
            $sessionValue = new String_((string) $this->valueResolver->getValue($option->value));
            $funcName = new Name('ini_set');
            $iniSet = new FuncCall($funcName, [new Arg($sessionKey), new Arg($sessionValue)]);

            $expressions[] = new Expression($iniSet);
        }

        return $expressions;
    }

    private function shouldSkip(FuncCall $funcCall): bool
    {
        if ($funcCall->isFirstClassCallable()) {
            return true;
        }

        if (! $this->isName($funcCall, 'session_start')) {
            return true;
        }

        if (! isset($funcCall->getArgs()[0])) {
            return true;
        }

        return ! $funcCall->getArgs()[0]->value instanceof Array_;
    }
}
