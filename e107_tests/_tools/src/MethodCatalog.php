<?php

declare(strict_types=1);

namespace E107\SqliScan;

/**
 * Static description of the e_db raw-SQL methods of interest and which of their
 * positional arguments carry SQL fragments.
 *
 * Argument roles drive how SafetyClassifier inspects each call:
 *  - "where"      a WHERE/ORDER/LIMIT clause string (value-position taint).
 *  - "fields"     a comma-separated field list; concatenation here is an
 *                 identifier-position concern (T4) but still flagged.
 *  - "sql"        a full hand-written SQL string (gen / execute arg0).
 *  - "crud_arg"   insert/update/replace/delete payload: an array means values
 *                 are bound by e107 internally (bound-safe); a string is raw SQL.
 *  - "params"     execute() bound-parameter array (presence => binds available).
 */
final class MethodCatalog
{
    /**
     * method => [argIndex => role]. Only SQL-bearing arguments are listed.
     *
     * @var array<string, array<int, string>>
     */
    private const METHODS = [
        'gen'      => [0 => 'sql'],
        'execute'  => [0 => 'sql', 1 => 'params'],
        // db_Query / db_Query_all are the lower-level raw-query primitives that
        // gen()/execute() ultimately call, and are also used directly (multi-
        // statement DDL imports, plugin installers). They were a historical blind
        // spot in the catalog; arg 0 is hand-written SQL, exactly like gen().
        'db_Query'     => [0 => 'sql'],
        'db_Query_all' => [0 => 'sql'],
        'select'   => [1 => 'fields', 2 => 'where'],
        'count'    => [1 => 'fields', 2 => 'where'],
        'retrieve' => [1 => 'fields', 2 => 'where'],
        'max'      => [2 => 'where'],
        'delete'   => [1 => 'where'],
        'update'   => [1 => 'crud_arg'],
        'insert'   => [1 => 'crud_arg'],
        'replace'  => [1 => 'crud_arg'],
        // escape() is the sanitizer itself; enumerated for the catalog, but a
        // call site of escape() is the safe pattern by construction (its result
        // is what gets concatenated, judged at the enclosing call's argument).
        'escape'   => [],
    ];

    public function isMethodOfInterest(string $method): bool
    {
        return isset(self::METHODS[$method]);
    }

    /**
     * @return array<int, string> argIndex => role
     */
    public function sqlArguments(string $method): array
    {
        return self::METHODS[$method] ?? [];
    }

    /**
     * @return list<string>
     */
    public function methods(): array
    {
        return array_keys(self::METHODS);
    }
}
