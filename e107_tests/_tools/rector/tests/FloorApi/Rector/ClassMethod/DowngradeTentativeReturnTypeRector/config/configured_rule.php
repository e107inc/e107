<?php

declare(strict_types=1);

use E107\Rector\FloorApi\Rector\ClassMethod\DowngradeTentativeReturnTypeRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(DowngradeTentativeReturnTypeRector::class, [
        'ArrayAccess' => ['offsetExists', 'offsetGet', 'offsetSet', 'offsetUnset'],
        'JsonSerializable' => ['jsonSerialize'],
    ]);
};
