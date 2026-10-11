<?php

declare(strict_types=1);

namespace E107\SqliScan;

use PhpParser\Node;
use PhpParser\Node\Expr;

/**
 * The unwraps every collaborator asks of a node: the name a variable, a function call or a method call carries, as written.
 */
final class Ast
{
    public static function variableName(Node $node): ?string
    {
        return $node instanceof Expr\Variable && is_string($node->name) ? $node->name : null;
    }

    public static function functionName(Node $node): ?string
    {
        return $node instanceof Expr\FuncCall && $node->name instanceof Node\Name ? $node->name->toString() : null;
    }

    public static function methodName(Node $node): ?string
    {
        return $node instanceof Expr\MethodCall && $node->name instanceof Node\Identifier ? $node->name->name : null;
    }

    public static function functionCall(Node $node, string $function): ?Expr\FuncCall
    {
        return $node instanceof Expr\FuncCall && strcasecmp(self::functionName($node) ?? '', $function) === 0 ? $node : null;
    }

    public static function methodCall(Node $node, string $method): ?Expr\MethodCall
    {
        return $node instanceof Expr\MethodCall && strcasecmp(self::methodName($node) ?? '', $method) === 0 ? $node : null;
    }
}
