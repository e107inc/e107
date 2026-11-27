<?php

declare(strict_types=1);

use E107\Rector\Php86\Rector\FuncCall\IsAOfAClassNameToInstanceofRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(IsAOfAClassNameToInstanceofRector::class);
};
