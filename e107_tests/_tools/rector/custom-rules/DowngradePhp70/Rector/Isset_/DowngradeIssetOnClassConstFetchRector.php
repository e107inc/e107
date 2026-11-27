<?php

declare(strict_types=1);

namespace E107\Rector\DowngradePhp70\Rector\Isset_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * PHP 5.6 fatals at parse time on `isset(SomeClass::CONST[$key])` with
 * "Cannot use isset() on the result of an expression". The restriction
 * was lifted in PHP 7.0; before that, the only way to test membership
 * of a class-constant array is to call array_key_exists() directly.
 *
 * This rule rewrites the convertible parts of any isset() into
 * array_key_exists() calls and joins them (alongside any non-convertible
 * residual isset() arguments) with `&&`. Nested dim-fetches whose root
 * is a class constant (`isset(self::C[$a][$b])`) are not yet handled and
 * left unchanged: they need a temp-variable rewrite or array_key_exists
 * + nested array_key_exists, neither of which is needed by any e107
 * source today.
 *
 * @changelog https://wiki.php.net/rfc/uniform_variable_syntax
 */
final class DowngradeIssetOnClassConstFetchRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Isset_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rewrite isset() on a class-constant array subscript to array_key_exists()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    const MAP = ['a' => 1];
    public function has(string $key): bool
    {
        return isset(self::MAP[$key]);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    const MAP = ['a' => 1];
    public function has(string $key): bool
    {
        return array_key_exists($key, self::MAP);
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param Isset_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $rewritten = [];
        $residual = [];
        foreach ($node->vars as $var) {
            $arrayKeyExists = $this->toArrayKeyExists($var);
            if ($arrayKeyExists instanceof FuncCall) {
                $rewritten[] = $arrayKeyExists;
            } else {
                $residual[] = $var;
            }
        }

        if ($rewritten === []) {
            return null;
        }

        $parts = $rewritten;
        if ($residual !== []) {
            $parts[] = new Isset_($residual);
        }

        $expr = array_shift($parts);
        foreach ($parts as $part) {
            $expr = new BooleanAnd($expr, $part);
        }

        return $expr;
    }

    private function toArrayKeyExists(Expr $var): ?FuncCall
    {
        if (! $var instanceof ArrayDimFetch) {
            return null;
        }

        if (! $var->var instanceof ClassConstFetch) {
            return null;
        }

        // No dim means `isset(self::CONST[])`, which would be a parse error
        // upstream of us. Bail rather than emit a syntactically invalid call.
        if ($var->dim === null) {
            return null;
        }

        return new FuncCall(
            new Name('array_key_exists'),
            [
                new Arg($var->dim),
                new Arg($var->var),
            ]
        );
    }
}
