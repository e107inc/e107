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
use E107\Rector\DowngradePhp71\Rector\FunctionLike\DowngradeImplicitlyNullableTypeDeclarationRector;
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

// API-floor rules: what the floor *has*, where the sets above only rewrite syntax
use E107\Rector\FloorApi\Rector\ClassMethod\DowngradeTentativeReturnTypeRector;
use E107\Rector\FloorApi\Rector\ConstFetch\DowngradePostFloorConstantRector;
use E107\Rector\FloorApi\Rector\FunctionLike\DowngradePostFloorParamTypeRector;

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
        // The rule above, for the implicit spelling `T $x = null`.
        DowngradeImplicitlyNullableTypeDeclarationRector::class,
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

    // Everything above this line rewrites syntax. PHP 5.6 parses a class type
    // hint, a constant read and a method signature whatever they name, so a
    // vendored package can clear the whole downgrade and still fatal on the
    // floor the moment it runs. The three rules below are the API floor, and
    // each one replaces a patch to vendored source that a re-vendor rolled
    // back: firebase/php-jwt 7.x, symfony/polyfill-php80 and guzzlehttp/psr7.

    // PHP 8.0 and 8.1 turned these resources into classes. A parameter typed
    // against one accepts nothing the floor can produce, because below 8.0 the
    // matching extension function still returns a resource, so the call is a
    // TypeError on 7.x and a catchable fatal on 5.6.
    $rectorConfig->ruleWithConfiguration(DowngradePostFloorParamTypeRector::class, [
        'AddressInfo',
        'CurlHandle',
        'CurlMultiHandle',
        'CurlShareHandle',
        'DeflateContext',
        'EnchantBroker',
        'EnchantDictionary',
        'FTP\Connection',
        'GdImage',
        'IMAP\Connection',
        'InflateContext',
        'LDAP\Connection',
        'LDAP\Result',
        'LDAP\ResultEntry',
        'OpenSSLAsymmetricKey',
        'OpenSSLCertificate',
        'OpenSSLCertificateSigningRequest',
        'PgSql\Connection',
        'PgSql\Lob',
        'PgSql\Result',
        'PSpell\Config',
        'PSpell\Dictionary',
        'Shmop',
        'Socket',
        'SysvMessageQueue',
        'SysvSemaphore',
        'SysvSharedMemory',
        'XMLParser',
    ]);

    // A fully qualified undefined constant is a fatal below PHP 7.0, not the
    // notice the unqualified spelling gets, and a switch arm holding one is
    // evaluated on every call.
    $rectorConfig->ruleWithConfiguration(DowngradePostFloorConstantRector::class, [
        'PREG_JIT_STACKLIMIT_ERROR' => 6,
    ]);

    // Stripping a return type to reach the floor leaves the method violating
    // the tentative return type its interface declares, which PHP 8.1 and
    // above report at class-link time on every request that loads the class.
    $rectorConfig->ruleWithConfiguration(DowngradeTentativeReturnTypeRector::class, [
        'ArrayAccess' => ['offsetExists', 'offsetGet', 'offsetSet', 'offsetUnset'],
        'Countable' => ['count'],
        'Iterator' => ['current', 'key', 'next', 'rewind', 'valid'],
        'IteratorAggregate' => ['getIterator'],
        'JsonSerializable' => ['jsonSerialize'],
        'SessionHandlerInterface' => ['close', 'destroy', 'gc', 'open', 'read', 'write'],
        'SessionIdInterface' => ['create_sid'],
        'SessionUpdateTimestampHandlerInterface' => ['updateTimestamp', 'validateId'],
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

        // PHP 5.6 parses an array, callable or class parameter hint whatever it
        // names, so the syntax never requires stripping one. This rule (pulled
        // in by DowngradeSetList::PHP_72) removes roughly 116 of them across
        // the tree to guard against a pre-7.2 contravariance hazard this
        // codebase does not have, and the legacy unit cells catch any real
        // widening the moment the class loads. Keep the hints. A hint naming a
        // class the floor does not have is a separate hazard, and parsing is
        // exactly what misses it: DowngradePostFloorParamTypeRector above takes
        // those, by name, without touching the other 116.
        \Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector::class,

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
