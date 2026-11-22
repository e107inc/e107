<?php

declare(strict_types=1);

use E107\Rector\FloorApi\Rector\FunctionLike\DowngradePostFloorParamTypeRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(DowngradePostFloorParamTypeRector::class, [
        'OpenSSLAsymmetricKey',
        'GdImage',
    ]);
};
