<?php

declare(strict_types=1);

use E107\Rector\DowngradePhp70\Rector\Namespace_\DowngradeConflictingUseImportRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeConflictingUseImportRector::class);
};
