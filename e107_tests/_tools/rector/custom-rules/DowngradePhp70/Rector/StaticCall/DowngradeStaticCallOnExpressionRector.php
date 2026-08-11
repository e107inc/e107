<?php

declare(strict_types=1);

namespace E107\Rector\DowngradePhp70\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\NodeAnalyzer\ExprInTopStmtMatcher;
use Rector\NodeFactory\NamedVariableFactory;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * PHP 7.0 allows a static call whose class is an arbitrary expression, e.g.
 * `e107::getTheme()::getThemeInfo($x)` or `$this->e107::coreLan('x')`. PHP 5.6
 * does not, so hoist the class expression into a temporary variable first.
 *
 * A plain `$var::method()` is already valid on PHP 5.6 and is left untouched.
 */
final class DowngradeStaticCallOnExpressionRector extends AbstractRector
{
    public function __construct(
        private readonly NamedVariableFactory $namedVariableFactory,
        private readonly ExprInTopStmtMatcher $exprInTopStmtMatcher
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StmtsAwareInterface::class, Switch_::class, Return_::class, Expression::class, Echo_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Downgrade a static call whose class is an expression (PHP 7.0) to a hoisted temporary variable.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$info = e107::getTheme()::getThemeInfo($x);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$tmp = e107::getTheme();
$info = $tmp::getThemeInfo($x);
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param StmtsAwareInterface|Switch_|Return_|Expression|Echo_ $node
     * @return Node[]|null
     */
    public function refactor(Node $node): ?array
    {
        $expr = $this->exprInTopStmtMatcher->match(
            $node,
            function (Node $subNode): bool {
                if (! $subNode instanceof StaticCall) {
                    return false;
                }

                return $this->isClassExpression($subNode->class);
            }
        );

        if (! $expr instanceof StaticCall) {
            return null;
        }

        /** @var Stmt $node */
        $tempVariable = $this->namedVariableFactory->createVariable('tmp', $node);
        $assignExpression = new Expression(new Assign($tempVariable, $expr->class));

        $this->traverseNodesWithCallable($node, static function (Node $subNode) use ($expr, $tempVariable) {
            if ($subNode === $expr) {
                $subNode->class = $tempVariable;
                return $subNode;
            }

            return null;
        });

        return [$assignExpression, $node];
    }

    /**
     * @param Name|Expr $class
     */
    private function isClassExpression($class): bool
    {
        // A bare class name (Foo::method()) is legal on PHP 5.6.
        if ($class instanceof Name) {
            return false;
        }

        // A plain variable ($var::method()) is legal on PHP 5.6.
        if ($class instanceof Variable) {
            return false;
        }

        return $class instanceof Expr;
    }
}
