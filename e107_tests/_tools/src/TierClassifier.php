<?php

declare(strict_types=1);

namespace E107\SqliScan;

/**
 * Best-effort convertibility tier for a classified call (secondary to safety).
 *
 *  - T1  static SQL that maps straight to execute() with no binds.
 *  - T2  builder-expressible CRUD (select/insert/update/delete/replace/count/
 *        retrieve/max) with no blocking dynamic identifier.
 *  - T3  needs execute()+binds: a raw gen()/execute() carrying safe dynamic
 *        VALUE parts (bindable), or any bound-safe value concatenation.
 *  - T4  blocked: an unsafe dynamic part sits in an identifier position
 *        (dynamic table/column name) or the statement is dynamic DDL; values
 *        can be bound, identifiers cannot.
 */
final class TierClassifier
{
    /**
     * CRUD methods that map onto the fluent builder.
     *
     * @var array<string, true>
     */
    private const BUILDER_METHODS = [
        'select' => true, 'insert' => true, 'update' => true, 'delete' => true,
        'replace' => true, 'count' => true, 'retrieve' => true, 'max' => true,
    ];

    public function classify(string $method, SafetyResult $safety, bool $isDdl): string
    {
        // T4: a dynamic identifier cannot be parameterized, and dynamic DDL has
        // no value-binding path either.
        if ($safety->hasUnsafeIdentifier()) {
            return CallSite::TIER_BLOCKED;
        }
        if ($isDdl && $safety->safety !== CallSite::SAFETY_STATIC) {
            return CallSite::TIER_BLOCKED;
        }

        // escape() is replaced by binding the value via execute()/builder.
        if ($method === 'escape') {
            return CallSite::TIER_EXECUTE_BINDS; // T3
        }

        // Raw SQL methods (gen/execute).
        if ($method === 'gen' || $method === 'execute') {
            if ($safety->safety === CallSite::SAFETY_STATIC) {
                return CallSite::TIER_STATIC_EXECUTE; // T1
            }
            return CallSite::TIER_EXECUTE_BINDS; // T3
        }

        // CRUD methods.
        if (isset(self::BUILDER_METHODS[$method])) {
            return CallSite::TIER_BUILDER; // T2
        }

        // Fallback (should not occur for methods of interest).
        return CallSite::TIER_EXECUTE_BINDS;
    }
}
