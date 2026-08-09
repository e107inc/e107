<?php

declare(strict_types=1);

namespace E107\Rector\DowngradePhp70\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Namespace_;
use Rector\NodeAnalyzer\ClassAnalyzer;
use Rector\PhpParser\Node\FileNode;
use Rector\Rector\AbstractRector;
use E107\Rector\DowngradePhp70\NodeFactory\ClassFromAnonymousFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Replaces each anonymous class with a named class whose declaration is
 * DEFERRED to first instantiation via an spl_autoload_register() closure
 * emitted at the top of the file (or namespace block).
 *
 * Two constraints force this shape:
 *
 * - The declaration must not execute at file-load time. An anonymous
 *   class's parent only has to be loadable when the `new` runs (test
 *   files require_once their subject inside the test method), so a
 *   hoisted `class X extends P` at file scope fatals on load.
 * - The declaration cannot be placed next to the `new` either: PHP
 *   forbids class declarations anywhere lexically inside a class body,
 *   and that includes method bodies ("Class declarations may not be
 *   nested").
 *
 * A closure body compiles outside the enclosing class context, so
 * declaring the class inside an autoloader closure is legal, and the
 * autoloader only fires when the `new` executes, which reproduces the
 * anonymous class's timing exactly.
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\DowngradeAnonymousClassRectorTest
 */
final class DowngradeAnonymousClassRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ANONYMOUS_CLASS_PREFIX = 'Anonymous__';

    /**
     * Per-file counters so generated names are deterministic for a given
     * file regardless of the order Rector processes files in.
     *
     * @var array<string, int>
     */
    private array $countPerFile = [];

    public function __construct(
        private readonly ClassAnalyzer $classAnalyzer,
        private readonly ClassFromAnonymousFactory $classFromAnonymousFactory
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FileNode::class, Namespace_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace anonymous class with a named class declared on first use via a file-scope autoloader',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return new class {
            public function execute()
            {
            }
        };
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
spl_autoload_register(function ($class) {
    if ($class === 'Anonymous__abc__0') {
        class Anonymous__abc__0
        {
            public function execute()
            {
            }
        }
    }
});
class SomeClass
{
    public function run()
    {
        return new Anonymous__abc__0();
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param FileNode|Namespace_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // Namespaced files are handled at their Namespace_ node so the
        // autoloader lands inside the namespace block and the declared
        // name carries the right prefix.
        if ($node instanceof FileNode && $this->containsNamespaceStmt($node)) {
            return null;
        }

        $namespacePrefix = '';
        if ($node instanceof Namespace_ && $node->name instanceof Name) {
            $namespacePrefix = $node->name->toString() . '\\';
        }

        $news = [];
        foreach ($node->stmts as $stmt) {
            $news = array_merge($news, $this->collectAnonymousNews($stmt));
        }

        if ($news === []) {
            return null;
        }

        $registrations = [];
        foreach ($news as $new) {
            /** @var Class_ $anonymousClass */
            $anonymousClass = $new->class;

            $className = $this->createAnonymousClassName();
            $class = $this->classFromAnonymousFactory->create($className, $anonymousClass);
            // php-parser v5 typed this property; later visitors read it on
            // ClassLike nodes, and a factory-built class never met the
            // NameResolver.
            $class->namespacedName = new Name($namespacePrefix . $className);
            // Rector refreshes PHPStan scope over the modified AST in the
            // same pass, and BetterReflection's ReflectionClass asserts
            // getStartLine() > 0 on every class-like it meets. The factory
            // builds this node without attributes, so hand it the anonymous
            // class's own source span; the body statements are the original
            // nodes and already carry theirs.
            foreach (['startLine', 'endLine', 'startFilePos', 'endFilePos', 'startTokenPos', 'endTokenPos'] as $positionAttribute) {
                if ($anonymousClass->hasAttribute($positionAttribute)) {
                    $class->setAttribute($positionAttribute, $anonymousClass->getAttribute($positionAttribute));
                }
            }

            $autoloadClosure = new Closure([
                'params' => [new Param(new Variable('class'))],
                'stmts' => [
                    new If_(
                        new Identical(
                            new Variable('class'),
                            new String_($namespacePrefix . $className)
                        ),
                        ['stmts' => [$class]]
                    ),
                ],
            ]);

            $registrations[] = new Expression(
                new FuncCall(new Name('spl_autoload_register'), [new Arg($autoloadClosure)])
            );

            $new->class = new Name($className);
        }

        array_splice($node->stmts, $this->insertionIndex($node->stmts), 0, $registrations);

        return $node;
    }

    private function containsNamespaceStmt(FileNode $fileNode): bool
    {
        foreach ($fileNode->stmts as $stmt) {
            if ($stmt instanceof Namespace_) {
                return true;
            }
        }

        return false;
    }

    /**
     * First statement position that is not a declare() or inline HTML;
     * declare(strict_types=1) must stay the first statement in the file.
     *
     * @param Node[] $stmts
     */
    private function insertionIndex(array $stmts): int
    {
        foreach ($stmts as $index => $stmt) {
            if (! $stmt instanceof Declare_ && ! $stmt instanceof InlineHTML) {
                return $index;
            }
        }

        return count($stmts);
    }

    /**
     * Every anonymous-class instantiation under this node, wherever it
     * sits (method bodies included; the declaration is deferred, so scope
     * no longer matters). The body of a found anonymous class is not
     * descended into: if it contains another anonymous class, the next
     * convergence pass picks it up once the outer one has become named.
     *
     * @return New_[]
     */
    private function collectAnonymousNews(Node $node): array
    {
        $result = [];

        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNode = $node->$subNodeName;

            foreach (is_array($subNode) ? $subNode : [$subNode] as $child) {
                if (! $child instanceof Node) {
                    continue;
                }

                if ($child instanceof New_
                    && $child->class instanceof Class_
                    && $this->classAnalyzer->isAnonymousClass($child->class)) {
                    $result[] = $child;

                    // Constructor arguments may hold further instantiations.
                    foreach ($child->getArgs() as $arg) {
                        $result = array_merge($result, $this->collectAnonymousNews($arg));
                    }

                    continue;
                }

                $result = array_merge($result, $this->collectAnonymousNews($child));
            }
        }

        return $result;
    }

    private function createAnonymousClassName(): string
    {
        $filePathHash = md5($this->file->getFilePath());

        $count = $this->countPerFile[$filePathHash] ?? 0;
        $this->countPerFile[$filePathHash] = $count + 1;

        return self::ANONYMOUS_CLASS_PREFIX . $filePathHash . '__' . $count;
    }
}
