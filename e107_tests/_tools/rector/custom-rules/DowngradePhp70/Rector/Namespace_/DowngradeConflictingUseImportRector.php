<?php

declare(strict_types=1);

namespace E107\Rector\DowngradePhp70\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PhpParser\NodeVisitor;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Aliases an import whose short name is taken by a class of the importing namespace.
 *
 * @changelog https://bugs.php.net/bug.php?id=66773
 */
final class DowngradeConflictingUseImportRector extends AbstractRector
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly DocBlockUpdater $docBlockUpdater
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Alias a class import whose short name is already taken by a class in the importing namespace, which PHP below 7.0.13 refuses to compile',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
namespace Hybridauth\Provider;

use Hybridauth\Adapter\OpenID;

class AOLOpenID extends OpenID
{
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
namespace Hybridauth\Provider;

use Hybridauth\Adapter\OpenID as OpenIDAdapter;

class AOLOpenID extends OpenIDAdapter
{
}
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
        return [Namespace_::class];
    }

    /**
     * @param Namespace_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->name instanceof Name) {
            return null;
        }

        $hasChanged = false;

        foreach ($node->stmts as $stmt) {
            if (! $stmt instanceof Use_ || $stmt->type !== Use_::TYPE_NORMAL) {
                continue;
            }

            foreach ($stmt->uses as $useItem) {
                if (! $this->isConflicting($node->name, $useItem)) {
                    continue;
                }

                $alias = $this->createFreeAlias($node, $useItem->name);
                $this->renameReferences($node, $useItem->name->getLast(), $alias);
                $useItem->alias = new Identifier($alias);
                $hasChanged = true;
            }
        }

        if (! $hasChanged) {
            return null;
        }

        return $node;
    }

    private function isConflicting(Name $namespaceName, UseItem $useItem): bool
    {
        if ($useItem->alias instanceof Identifier) {
            return false;
        }

        $occupiedName = $namespaceName->toString() . '\\' . $useItem->name->getLast();
        if (strcasecmp($occupiedName, $useItem->name->toString()) === 0) {
            return false;
        }

        return $this->reflectionProvider->hasClass($occupiedName);
    }

    private function createFreeAlias(Namespace_ $namespace, Name $importedName): string
    {
        $parts = explode('\\', $importedName->toString());
        $shortName = array_pop($parts);
        $parentName = array_pop($parts);

        $candidate = $shortName . ($parentName ?? 'Import');
        $takenNames = $this->resolveTakenNames($namespace);
        $suffix = 1;

        while (in_array(strtolower($candidate), $takenNames, true)
            || $this->reflectionProvider->hasClass($namespace->name . '\\' . $candidate)
        ) {
            $candidate = $shortName . ($parentName ?? 'Import') . ++$suffix;
        }

        return $candidate;
    }

    /**
     * @return string[] lower-cased, because PHP resolves both imports and class names without case
     */
    private function resolveTakenNames(Namespace_ $namespace): array
    {
        $takenNames = [];

        foreach ($namespace->stmts as $stmt) {
            if ($stmt instanceof ClassLike && $stmt->name instanceof Identifier) {
                $takenNames[] = strtolower($stmt->name->toString());
                continue;
            }

            if (! $stmt instanceof Use_ || $stmt->type !== Use_::TYPE_NORMAL) {
                continue;
            }

            foreach ($stmt->uses as $useItem) {
                $takenNames[] = strtolower(
                    $useItem->alias instanceof Identifier ? $useItem->alias->toString() : $useItem->name->getLast()
                );
            }
        }

        return $takenNames;
    }

    private function renameReferences(Namespace_ $namespace, string $shortName, string $alias): void
    {
        $this->traverseNodesWithCallable($namespace->stmts, function (Node $node) use ($shortName, $alias) {
            if ($node instanceof Use_) {
                return NodeVisitor::DONT_TRAVERSE_CHILDREN;
            }

            $this->renameDocBlockReferences($node, $shortName, $alias);

            if (! $node instanceof Name) {
                return null;
            }

            $writtenName = $node->getAttribute(AttributeKey::ORIGINAL_NAME);
            if (! $writtenName instanceof Name) {
                $writtenName = $node;
            }

            if ($writtenName->isFullyQualified() || strcasecmp($writtenName->getFirst(), $shortName) !== 0) {
                return null;
            }

            $parts = $writtenName->getParts();
            $parts[0] = $alias;

            return new Name(implode('\\', $parts));
        });
    }

    private function renameDocBlockReferences(Node $node, string $shortName, string $alias): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return;
        }

        $hasChanged = false;
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable(
            $phpDocInfo->getPhpDocNode(),
            '',
            static function ($docNode) use ($shortName, $alias, &$hasChanged) {
                if (! $docNode instanceof IdentifierTypeNode) {
                    return $docNode;
                }

                $parts = explode('\\', $docNode->name);
                if (str_starts_with($docNode->name, '\\') || strcasecmp($parts[0], $shortName) !== 0) {
                    return $docNode;
                }

                $parts[0] = $alias;
                $hasChanged = true;

                return new IdentifierTypeNode(implode('\\', $parts));
            }
        );

        if (! $hasChanged) {
            return;
        }

        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
    }
}
