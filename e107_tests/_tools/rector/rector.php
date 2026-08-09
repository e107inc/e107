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
use E107\Rector\DowngradePhp70\Rector\Isset_\DowngradeIssetOnClassConstFetchRector;
use E107\Rector\DowngradePhp70\Rector\MethodCall\DowngradeClosureCallRector;
use E107\Rector\DowngradePhp70\Rector\MethodCall\DowngradeMethodCallOnCloneRector;
use E107\Rector\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector;
use E107\Rector\DowngradePhp70\Rector\Spaceship\DowngradeSpaceshipRector;
use E107\Rector\DowngradePhp70\Rector\StaticCall\DowngradeStaticCallOnExpressionRector;
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

// Upstream rules re-vendored for Rector 2.x (their stock copies are skipped below)
use E107\Rector\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector;
use E107\Rector\DowngradePhp74\Rector\FuncCall\DowngradeProcOpenArrayCommandArgRector;
use E107\Rector\DowngradePhp81\Rector\FuncCall\DowngradeHashAlgorithmXxHashRector;

return static function (RectorConfig $rectorConfig): void {
    $root = __DIR__ . '/../../..';
    $rectorConfig->paths([
        $root,
    ]);

    // 8.x → 7.2 from upstream rector/rector-downgrade-php 0.15.1.
    // We deliberately omit PHP_71 here; our vendored Php71 rules handle that step.
    $rectorConfig->sets([
        DowngradeSetList::PHP_82,
        DowngradeSetList::PHP_81,
        DowngradeSetList::PHP_80,
        DowngradeSetList::PHP_74,
        DowngradeSetList::PHP_73,
        DowngradeSetList::PHP_72,
    ]);

    // 7.1 → 5.6 from our vendored copies. The three Php72/74/81 rules at the
    // end are re-vendored stock rules whose upstream copies are skipped below.
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
        DowngradePhp71JsonConstRector::class,
        DowngradeClosureFromCallableRector::class,
        DowngradeScalarTypeDeclarationRector::class,
        DowngradeNullCoalesceRector::class,
        DowngradeStrictTypeDeclarationRector::class,
        SplitGroupedUseImportsRector::class,
        DowngradeCatchThrowableRector::class,
        DowngradeInstanceofThrowableRector::class,
        DowngradeParentTypeDeclarationRector::class,
        DowngradeSelfTypeDeclarationRector::class,
        DowngradeSpaceshipRector::class,
        DowngradeThrowableTypeDeclarationRector::class,
        DowngradeUnnecessarilyParenthesizedExpressionRector::class,
        DowngradeDefineArrayConstantRector::class,
        DowngradeDirnameLevelsRector::class,
        DowngradeUncallableValueCallToCallUserFuncRector::class,
        DowngradeMethodCallOnCloneRector::class,
        DowngradeClosureCallRector::class,
        DowngradeAnonymousClassRector::class,
        DowngradeSessionStartArrayOptionsRector::class,
        DowngradeStaticCallOnExpressionRector::class,
        // PHP 5.6 fatals on isset(self::CONST[$x]); our rule emits
        // array_key_exists($x, self::CONST). Needed for firebase/php-jwt JWK.
        DowngradeIssetOnClassConstFetchRector::class,
        DowngradeStreamIsattyRector::class,
        DowngradeProcOpenArrayCommandArgRector::class,
        DowngradeHashAlgorithmXxHashRector::class,
    ]);

    $rectorConfig->phpVersion(PhpVersion::PHP_56);

    $rectorConfig->skip([
        // Skip the stock copies of the three rules re-vendored above (registered
        // from the E107\Rector namespace). The upstream versions crash at
        // class-load or are non-idempotent under Rector 2.x:
        //   - xxhash references MHASH_XXH32, defined only with the removed mhash
        //     extension, so it fatals just on autoload.
        //   - stream_isatty needs StmtsAwareInterface, gone in 2.x.
        //   - proc_open re-wraps its own is_array() output every pass and
        //     balloons exponentially; our copy adds an idempotence guard.
        \Rector\DowngradePhp81\Rector\FuncCall\DowngradeHashAlgorithmXxHashRector::class,
        \Rector\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector::class,
        \Rector\DowngradePhp74\Rector\FuncCall\DowngradeProcOpenArrayCommandArgRector::class,

        // PHP 5.6 already parses array, class and callable parameter hints, so
        // nothing about the floor requires stripping them. This rule (pulled in
        // by DowngradeSetList::PHP_72) removes roughly 116 of them across the
        // tree to guard against a pre-7.2 contravariance hazard this codebase
        // does not have, and the legacy unit cells catch any real widening the
        // moment the class loads. Keep the hints.
        \Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector::class,

        // Quarantined. A later commit removes this skip entry and carries the
        // (string) casts the rule produces, so that commit reverts cleanly on
        // its own and leaves the tree at a consistent fixed point.
        \Rector\DowngradePhp80\Rector\FuncCall\DowngradeSubstrFalsyRector::class,

        // e107 v2 commits its vendored dependencies and serves the tree as-is,
        // so every shipped package under e107_handlers/vendor must parse on PHP
        // 5.6 and is downgraded in place. (e107 v3's build chain, which does not
        // exist yet, will move third-party code to a separate flow.)
        // e107_tests/vendor is dev-only tooling (Codeception, PHPUnit) and is
        // never shipped to a runtime, so it stays out of scope.
        $root . '/e107_tests/vendor', // correctness

        // Developer tooling, intentionally modern PHP: this Rector setup and
        // the PHP 8.1-only SQLi scanner both live here. Never downgrade it, and
        // never let it try to downgrade its own vendor tree or caches.
        $root . '/e107_tests/_tools', // correctness

        // Generated artefacts and per-environment scratch. Codeception rewrites
        // them from its own templates, so a downgrade here would be undone.
        $root . '/e107_tests/tests/_output', // correctness
        $root . '/e107_tests/tests/_support/_generated', // correctness

        // Docs and media directories have zero tracked PHP files, so skipping
        // them only saves Rector the walk over the asset trees. (e107_web and
        // e107_images DO contain shipping PHP scripts and stay in scope.)
        $root . '/e107_docs', // enumeration
        $root . '/e107_media', // enumeration

        // CI release-build helpers, also intentionally modern PHP.
        $root . '/.github', // correctness

        // Repository internals and agent workspace metadata. Not source.
        $root . '/.git', // enumeration
        $root . '/.claude', // enumeration
    ]);

    $rectorConfig->cacheDirectory(__DIR__ . '/.rector-cache');
};
