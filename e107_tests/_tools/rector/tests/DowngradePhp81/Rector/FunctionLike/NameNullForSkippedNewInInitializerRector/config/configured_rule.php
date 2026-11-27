<?php

declare(strict_types=1);

use E107\Rector\DowngradePhp81\Rector\FunctionLike\NameNullForSkippedNewInInitializerRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(NameNullForSkippedNewInInitializerRector::class);
};
