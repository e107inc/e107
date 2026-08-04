<?php

declare(strict_types=1);

namespace E107\SqliScan;

/**
 * The outcome of SafetyClassifier: the safety verdict, the dynamic parts that
 * informed it (reused by TierClassifier), and a human-readable reason.
 */
final class SafetyResult
{
    /**
     * @param list<DynamicPart> $dynamicParts
     */
    public function __construct(
        public readonly string $safety,
        public readonly array $dynamicParts,
        public readonly string $reason
    ) {
    }

    public function hasUnsafeIdentifier(): bool
    {
        foreach ($this->dynamicParts as $part) {
            if (!$part->safe && $part->identifierPosition) {
                return true;
            }
        }
        return false;
    }
}
