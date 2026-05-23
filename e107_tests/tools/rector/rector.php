<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\DowngradeSetList;
use Rector\ValueObject\PhpVersion;

// Vendored DowngradePhp70 rules (upstream removed in 0.15.1)
use E107\Rector\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector;
use E107\Rector\DowngradePhp70\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector;
use E107\Rector\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector;
use E107\Rector\DowngradePhp70\Rector\Declare_\DowngradeStrictTypeDeclarationRector;
use E107\Rector\DowngradePhp70\Rector\Expr\DowngradeUnnecessarilyParenthesizedExpressionRector;
use E107\Rector\DowngradePhp70\Rector\Expression\DowngradeDefineArrayConstantRector;
use E107\Rector\DowngradePhp70\Rector\FuncCall\DowngradeDirnameLevelsRector;
use E107\Rector\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector;
use E107\Rector\DowngradePhp70\Rector\FuncCall\DowngradeUncallableValueCallToCallUserFuncRector;
use E107\Rector\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector;
use E107\Rector\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector;
use E107\Rector\DowngradePhp70\Rector\GroupUse\SplitGroupedUseImportsRector;
use E107\Rector\DowngradePhp70\Rector\Instanceof_\DowngradeInstanceofThrowableRector;
use E107\Rector\DowngradePhp70\Rector\MethodCall\DowngradeClosureCallRector;
use E107\Rector\DowngradePhp70\Rector\MethodCall\DowngradeMethodCallOnCloneRector;
use E107\Rector\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector;
use E107\Rector\DowngradePhp70\Rector\Spaceship\DowngradeSpaceshipRector;
use E107\Rector\DowngradePhp70\Rector\TryCatch\DowngradeCatchThrowableRector;

// Vendored DowngradePhp71 rules (upstream removed after 0.15.1)
use E107\Rector\DowngradePhp71\Rector\Array_\SymmetricArrayDestructuringToListRector;
use E107\Rector\DowngradePhp71\Rector\ClassConst\DowngradeClassConstantVisibilityRector;
use E107\Rector\DowngradePhp71\Rector\ConstFetch\DowngradePhp71JsonConstRector;
use E107\Rector\DowngradePhp71\Rector\FuncCall\DowngradeIsIterableRector;
use E107\Rector\DowngradePhp71\Rector\FunctionLike\DowngradeIterablePseudoTypeDeclarationRector;
use E107\Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector;
use E107\Rector\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector;
use E107\Rector\DowngradePhp71\Rector\List_\DowngradeKeysInListRector;
use E107\Rector\DowngradePhp71\Rector\StaticCall\DowngradeClosureFromCallableRector;
use E107\Rector\DowngradePhp71\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector;
use E107\Rector\DowngradePhp71\Rector\TryCatch\DowngradePipeToMultiCatchExceptionRector;

return static function (RectorConfig $rectorConfig): void {
    // Prototype scope — start narrow, broaden once we trust the output.
    $rectorConfig->paths([
        __DIR__ . '/../../../e107_handlers/session_handler.php',
    ]);

    // 8.x → 7.2 from upstream rector/rector-downgrade-php 0.15.1.
    // We deliberately omit PHP_71 here — our vendored Php71 rules handle that step.
    $rectorConfig->sets([
        DowngradeSetList::PHP_82,
        DowngradeSetList::PHP_81,
        DowngradeSetList::PHP_80,
        DowngradeSetList::PHP_74,
        DowngradeSetList::PHP_73,
        DowngradeSetList::PHP_72,
    ]);

    // 7.1 → 5.6 from our vendored copies.
    // Excluded (use helper classes that don't exist in Rector 2.x core):
    //   - DowngradeClosureCallRector              (needs MethodCallTypeAnalyzer)
    //   - DowngradeAnonymousClassRector           (needs NamespacedNameDecorator)
    //   - DowngradeSessionStartArrayOptionsRector (needs NodesToAddCollector)
    //   - DowngradeClosureFromCallableRector      (needs StmtsAwareInterface)
    // These either need re-implementation against 2.x APIs, vendoring of the
    // missing helpers, or sourcing replacements from somewhere else.
    $rectorConfig->rules([
        DowngradeNullableTypeDeclarationRector::class,
        DowngradeVoidTypeDeclarationRector::class,
        DowngradeClassConstantVisibilityRector::class,
        DowngradePipeToMultiCatchExceptionRector::class,
        SymmetricArrayDestructuringToListRector::class,
        DowngradeNegativeStringOffsetToStrlenRector::class,
        DowngradeKeysInListRector::class,
        DowngradeIterablePseudoTypeDeclarationRector::class,
        DowngradeIsIterableRector::class,
        // DowngradePhp71JsonConstRector excluded: references
        // Rector\Enum\JsonConstant::UNESCAPED_LINE_TERMINATORS which doesn't
        // exist in 2.x's JsonConstant enum — needs porting to current API.

        DowngradeScalarTypeDeclarationRector::class,
        DowngradeNullCoalesceRector::class,
        DowngradeStrictTypeDeclarationRector::class,
        SplitGroupedUseImportsRector::class,
        DowngradeCatchThrowableRector::class,
        DowngradeInstanceofThrowableRector::class,
        DowngradeParentTypeDeclarationRector::class,
        DowngradeSelfTypeDeclarationRector::class,
        // DowngradeSpaceshipRector excluded: calls NamedVariableFactory::createVariable()
        // with an If_ node, but the 2.x API requires a string. Needs porting.
        // (Low impact: e107 audit showed 0 occurrences of the spaceship operator.)
        DowngradeThrowableTypeDeclarationRector::class,
        DowngradeUnnecessarilyParenthesizedExpressionRector::class,
        DowngradeDefineArrayConstantRector::class,
        DowngradeDirnameLevelsRector::class,
        DowngradeUncallableValueCallToCallUserFuncRector::class,
        DowngradeMethodCallOnCloneRector::class,
    ]);

    $rectorConfig->phpVersion(PhpVersion::PHP_56);

    // Skip upstream rules that crash at class-load or need missing services:
    $rectorConfig->skip([
        // DowngradeHashAlgorithmXxHashRector references MHASH_XXH32, only
        // defined if the long-removed mhash extension is loaded.
        \Rector\DowngradePhp81\Rector\FuncCall\DowngradeHashAlgorithmXxHashRector::class,
        // DowngradeStreamIsattyRector needs StmtsAwareInterface (missing in 2.x core).
        \Rector\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector::class,
    ]);

    $rectorConfig->cacheDirectory(__DIR__ . '/.rector-cache');
};
