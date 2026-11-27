<?php

declare(strict_types=1);

namespace E107\Rector\DowngradePhp70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use E107\Rector\DowngradePhp70\Tokenizer\WrappedInParenthesesAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/uniform_variable_syntax
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeUncallableValueCallToCallUserFuncRector\DowngradeUncallableValueCallToCallUserFuncRectorTest
 */
final class DowngradeUncallableValueCallToCallUserFuncRector extends AbstractRector
{
    /**
     * @var array<class-string<Expr>>
     */
    private const INDIRECT_CALLABLE_EXPR = [
        // Interpreted as MethodCall without parentheses.
        PropertyFetch::class,
        // Interpreted as StaticCall without parentheses.
        StaticPropertyFetch::class,
        Closure::class,
        // The first function call does not even need to be wrapped in parentheses
        // but PHP 5 still does not like curried functions like `f($args)($moreArgs)`.
        FuncCall::class,
    ];

    public function __construct(
        private readonly WrappedInParenthesesAnalyzer $wrappedInParenthesesAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Downgrade calling a value that is not directly callable in PHP 5 (property, static property, closure, …) to call_user_func.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
final class Foo
{
    /** @var callable */
    public $handler;
    /** @var callable */
    public static $staticHandler;
}

$foo = new Foo;
($foo->handler)(/* args */);
($foo::$staticHandler)(41);

(function() { /* … */ })();

$callable = [$foo, 'method'];
($callable)(...$args);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class Foo
{
    /** @var callable */
    public $handler;
    /** @var callable */
    public static $staticHandler;
}

$foo = new Foo;
call_user_func($foo->handler, /* args */);
call_user_func($foo::$staticHandler, 41);

call_user_func(function() { /* … */ });

$callable = [$foo, 'method'];
call_user_func($callable, ...$args);
CODE_SAMPLE
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
    public function refactor(Node $node): ?FuncCall
    {
        if ($node->name instanceof Name) {
            return null;
        }

        if (! $this->isNotDirectlyCallableInPhp5($node->name)) {
            return null;
        }

        // Preserve spread (`...$args`) and by-reference flags by reusing the
        // original Arg nodes; only the callee is prepended as a new Arg.
        $args = array_merge([new Arg($node->name)], $node->getArgs());

        return new FuncCall(new Name('call_user_func'), $args);
    }

    private function isNotDirectlyCallableInPhp5(Expr $expr): bool
    {
        // Property / static-property / closure / func-call callees are never
        // directly callable on PHP 5 regardless of parentheses.
        if (in_array($expr::class, self::INDIRECT_CALLABLE_EXPR, true)) {
            return true;
        }

        // Any other callee expression (most notably a plain Variable holding an
        // array callable like `[$obj, $method]`) is only a PHP 5 problem when it
        // is invoked through parentheses, e.g. `($callable)(...$args)`. PHP 5
        // cannot parse the parenthesized callee, and even after stripping the
        // parentheses `$callable(...)` would fatal at runtime for an array
        // callable. Routing it through call_user_func fixes both.
        //
        // We deliberately leave a non-parenthesized `$fn($x)` untouched: it both
        // parses and runs on PHP 5 (for string/Closure callables), and rewriting
        // every such call would balloon the downgrade diff.
        return $this->wrappedInParenthesesAnalyzer->isParenthesized($this->file, $expr);
    }
}
