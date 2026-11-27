<?php

declare(strict_types=1);

namespace E107\Rector\Php86\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rewrites `is_a($x, 'Literal\Class')` as `$x instanceof \Literal\Class`, which PHP 8.6 does not deprecate for a string `$x`.
 *
 * Leaves alone a call with `$allow_string` other than a literal false, a class name that is not a literal or has a reserved word in it, and a subject other than a variable, property, array element, method call or assignment.
 */
final class IsAOfAClassNameToInstanceofRector extends AbstractRector
{
    private const RESERVED = [
        '__halt_compiler', 'abstract', 'and', 'array', 'as', 'bool', 'break', 'callable', 'case', 'catch', 'class',
        'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty',
        'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'enum', 'eval', 'exit', 'extends',
        'false', 'final', 'finally', 'float', 'fn', 'for', 'foreach', 'function', 'global', 'goto', 'if',
        'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'int', 'interface', 'isset', 'iterable',
        'list', 'match', 'mixed', 'namespace', 'never', 'new', 'null', 'numeric', 'object', 'or', 'parent', 'print',
        'private', 'protected', 'public', 'readonly', 'require', 'require_once', 'resource', 'return', 'self',
        'static', 'string', 'switch', 'throw', 'trait', 'true', 'try', 'unset', 'use', 'var', 'void', 'while', 'xor',
        'yield',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace is_a() of a literal class name with instanceof',
            [
                new CodeSample(
                    "return is_a(\$this->data, 'Imagick');",
                    'return $this->data instanceof \Imagick;'
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable() || ! $this->isName($node, 'is_a')) {
            return null;
        }

        $args = $node->getArgs();
        if (count($args) < 2 || count($args) > 3 || $args[0]->unpack || $args[0]->name !== null || $args[1]->name !== null) {
            return null;
        }

        if (isset($args[2]) && ! ($args[2]->value instanceof ConstFetch && $this->isName($args[2]->value, 'false'))) {
            return null;
        }

        $subject = $args[0]->value;
        if (! $this->isRuntimeValue($subject)) {
            return null;
        }

        $class = $this->className($args[1]->value);
        if ($class === null) {
            return null;
        }

        return new Instanceof_($subject, $class);
    }

    private function isRuntimeValue(Expr $expr): bool
    {
        if ($expr instanceof ArrayDimFetch) {
            return $this->isRuntimeValue($expr->var);
        }

        return $expr instanceof Variable || $expr instanceof PropertyFetch || $expr instanceof StaticPropertyFetch
            || $expr instanceof MethodCall || $expr instanceof StaticCall || $expr instanceof Assign;
    }

    private function className(Expr $expr): ?Name
    {
        if ($expr instanceof String_ && preg_match('/^\\\\?[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)*$/', $expr->value) === 1) {
            $name = ltrim($expr->value, '\\');
            $segments = array_map('strtolower', explode('\\', $name));

            return array_intersect($segments, self::RESERVED) === [] ? new FullyQualified($name) : null;
        }

        if ($expr instanceof ClassConstFetch && $expr->class instanceof Name && $expr->name instanceof Identifier
            && $expr->name->toLowerString() === 'class' && ! $this->isNames($expr->class, ['self', 'static', 'parent'])) {
            return new FullyQualified($this->getName($expr->class));
        }

        return null;
    }
}
