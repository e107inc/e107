<?php

declare(strict_types=1);

namespace E107\SqliScan;

use PhpParser\Node;
use PhpParser\Node\Expr;

/**
 * Decides whether the receiver of a method call is a genuine e107 e_db object.
 *
 * The taxonomy is the finalized list from the SQLi-elimination plan: a curated
 * set of variable names, $this-> property names, and the e107::getDb() factory
 * count as e_db; an explicit deny-list (forms, caches, prefs, models, the query
 * builder, PDOStatement, vendor objects) and a bare $this->method() do not.
 *
 * The classifier returns one of three verdicts so the caller can distinguish
 * "definitely e_db" (emit) from "definitely not" (skip) and "unknown receiver"
 * (skip, but countable for diagnostics).
 */
final class ReceiverTaxonomy
{
    public const VERDICT_INCLUDE = 'include';
    public const VERDICT_EXCLUDE = 'exclude';
    public const VERDICT_UNKNOWN = 'unknown';

    /**
     * Variable names that always hold an e_db handle.
     *
     * @var array<string, true>
     */
    private const INCLUDE_VARS = [
        'sql' => true, 'sql2' => true, 'sql3' => true, 'sql9' => true,
        'sqlt' => true, 'sqlp' => true, 'sqld' => true, 'sql_ue' => true,
        'u_sql' => true, 'uc_sql' => true, 'ul_sql' => true, 'aa_sql' => true,
        'nl_sql' => true, 'link_sql' => true, 'get_sql' => true, 'sqlrss' => true,
        'bcSql' => true, 'db' => true, 'db2' => true, 'ourDB' => true,
        'writeDb' => true,
    ];

    /**
     * $this-> property names that always hold an e_db handle.
     *
     * @var array<string, true>
     */
    private const INCLUDE_PROPS = [
        'sql' => true, '_db' => true, 'db' => true, 'db2' => true,
        'sql_r' => true, 'rldb' => true, 'pmDB' => true, 'ourDB' => true,
        'pageDB' => true, 'userDB' => true, 'newsDB' => true, 'ucdb' => true,
    ];

    /**
     * Variable names that are never an e_db handle.
     *
     * @var array<string, true>
     */
    private const EXCLUDE_VARS = [
        'frm' => true, 'form' => true,        // e_form
        'cache' => true, 'e107cache' => true, // e_cache
        'core' => true, 'core_pref' => true,  // prefs
        'config' => true, 'pref' => true, 'prefs' => true,
        'model' => true, 'tree' => true, 'ui' => true, 'xml' => true,
        'uploadObj' => true, 'che' => true,
        'statement' => true, // PDOStatement
        'qb' => true,        // the query builder itself
        'minifier' => true, 'command' => true, // vendor objects
    ];

    /**
     * Classify a method-call receiver expression.
     */
    public function classify(Expr $receiver): string
    {
        // e107::getDb(...) factory (any instance id, including empty) -> e_db.
        if ($this->isGetDbCall($receiver)) {
            return self::VERDICT_INCLUDE;
        }

        // A method-call chain whose base resolves to the query builder
        // (createQueryBuilder()) returns QueryBuilder, never e_db.
        if ($receiver instanceof Expr\MethodCall && $this->chainStartsWithBuilder($receiver)) {
            return self::VERDICT_EXCLUDE;
        }

        if ($receiver instanceof Expr\Variable && is_string($receiver->name)) {
            if (isset(self::INCLUDE_VARS[$receiver->name])) {
                return self::VERDICT_INCLUDE;
            }
            if (isset(self::EXCLUDE_VARS[$receiver->name])) {
                return self::VERDICT_EXCLUDE;
            }
            return self::VERDICT_UNKNOWN;
        }

        if ($receiver instanceof Expr\PropertyFetch && $receiver->var instanceof Expr\Variable
            && $receiver->var->name === 'this' && $receiver->name instanceof Node\Identifier) {
            $prop = $receiver->name->name;
            if (isset(self::INCLUDE_PROPS[$prop])) {
                return self::VERDICT_INCLUDE;
            }
            return self::VERDICT_EXCLUDE;
        }

        return self::VERDICT_UNKNOWN;
    }

    /**
     * A bare $this->method(...) is the class's own method, never an e_db call.
     */
    public function isBareThisCall(Expr\MethodCall $call): bool
    {
        return $call->var instanceof Expr\Variable && $call->var->name === 'this';
    }

    /**
     * Human-readable label for a receiver, for the catalog "receiver" column.
     */
    public function describe(Expr $receiver): string
    {
        if ($this->isGetDbCall($receiver)) {
            return 'e107::getDb()';
        }
        if ($receiver instanceof Expr\Variable && is_string($receiver->name)) {
            return '$' . $receiver->name;
        }
        if ($receiver instanceof Expr\PropertyFetch && $receiver->var instanceof Expr\Variable
            && $receiver->var->name === 'this' && $receiver->name instanceof Node\Identifier) {
            return '$this->' . $receiver->name->name;
        }
        if ($receiver instanceof Expr\MethodCall && $receiver->name instanceof Node\Identifier) {
            return '...->' . $receiver->name->name . '()';
        }
        return get_class($receiver);
    }

    /**
     * True for e107::getDb(...) (the e_db factory) regardless of arguments.
     */
    private function isGetDbCall(Expr $expr): bool
    {
        return $expr instanceof Expr\StaticCall
            && $expr->class instanceof Node\Name
            && $expr->class->toLowerString() === 'e107'
            && $expr->name instanceof Node\Identifier
            && $expr->name->toLowerString() === 'getdb';
    }

    /**
     * Walk a method-call chain back to its root and report whether that root is
     * a createQueryBuilder() call (so the whole chain is on the builder).
     */
    private function chainStartsWithBuilder(Expr\MethodCall $call): bool
    {
        $node = $call->var;
        while ($node instanceof Expr\MethodCall) {
            if ($node->name instanceof Node\Identifier
                && $node->name->toLowerString() === 'createquerybuilder') {
                return true;
            }
            $node = $node->var;
        }
        return false;
    }
}
