<?php

declare(strict_types=1);

namespace E107\Rector\DowngradePhp72\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\LNumber;
use Rector\Enum\JsonConstant;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final readonly class JsonConstCleaner
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
    ) {
    }

    /**
     * @param array<JsonConstant::*> $constants
     */
    public function clean(ConstFetch|BitwiseOr $node, array $constants): Expr|null
    {
        if ($node instanceof BitwiseOr) {
            return $this->cleanByBitwiseOr($node, $constants);
        }

        return $this->cleanByConstFetch($node, $constants);
    }

    /**
     * @param array<JsonConstant::*> $constants
     */
    private function cleanByConstFetch(ConstFetch $constFetch, array $constants): ?LNumber
    {
        if (! $this->nodeNameResolver->isNames($constFetch, $constants)) {
            return null;
        }

        return new LNumber(0);
    }

    /**
     * @param array<JsonConstant::*> $constants
     */
    private function cleanByBitwiseOr(BitwiseOr $bitwiseOr, array $constants): null|Expr|LNumber
    {
        $isLeftTransformed = $this->isTransformed($bitwiseOr->left, $constants);
        $isRightTransformed = $this->isTransformed($bitwiseOr->right, $constants);

        if (! $isLeftTransformed && ! $isRightTransformed) {
            return null;
        }

        if (! $isLeftTransformed) {
            return $bitwiseOr->left;
        }

        if (! $isRightTransformed) {
            return $bitwiseOr->right;
        }

        return new LNumber(0);
    }

    /**
     * @param string[] $constants
     */
    private function isTransformed(Expr $expr, array $constants): bool
    {
        if ($expr instanceof ConstFetch && $this->nodeNameResolver->isNames($expr, $constants)) {
            return true;
        }

        return ! $expr->getAttribute(AttributeKey::ORIGINAL_NODE) instanceof Node;
    }
}
