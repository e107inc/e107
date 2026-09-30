<?php

declare(strict_types=1);

use E107\Rector\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(HoistLiteralByReferenceArgumentRector::class);
};
