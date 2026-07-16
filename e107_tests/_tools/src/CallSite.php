<?php

declare(strict_types=1);

namespace E107\SqliScan;

/**
 * Immutable record of one classified e_db raw-SQL call site.
 *
 * This is the unit the catalog is built from and the shape that is emitted to
 * catalog.json. It holds only data; classification lives in the dedicated
 * classifier services.
 */
final class CallSite
{
    public const SAFETY_STATIC = 'static';
    public const SAFETY_BOUND = 'bound-safe';
    public const SAFETY_ASSUMED_ARRAY = 'assumed-safe-array';
    public const SAFETY_UNSAFE = 'unsafe-concat';

    public const TIER_STATIC_EXECUTE = 'T1';
    public const TIER_BUILDER = 'T2';
    public const TIER_EXECUTE_BINDS = 'T3';
    public const TIER_BLOCKED = 'T4';

    public function __construct(
        public readonly string $file,
        public readonly int $line,
        public readonly string $method,
        public readonly string $receiver,
        public readonly string $safety,
        public readonly string $tier,
        public readonly string $sqlExcerpt,
        public readonly string $reason,
        public readonly bool $requestTainted = false
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'file' => $this->file,
            'line' => $this->line,
            'method' => $this->method,
            'receiver' => $this->receiver,
            'safety' => $this->safety,
            'tier' => $this->tier,
            'sql_excerpt' => $this->sqlExcerpt,
            'reason' => $this->reason,
            'request_tainted' => $this->requestTainted,
        ];
    }
}
