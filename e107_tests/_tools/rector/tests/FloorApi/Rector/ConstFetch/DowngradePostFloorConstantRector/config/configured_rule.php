<?php

declare(strict_types=1);

use E107\Rector\FloorApi\Rector\ConstFetch\DowngradePostFloorConstantRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(DowngradePostFloorConstantRector::class, [
        'PREG_JIT_STACKLIMIT_ERROR' => 6,
        'PHP_OS_FAMILY' => 'Linux',
    ]);
};
