<?php

namespace E107\Rector\Tests\DowngradePhp70\Rector\Namespace_\DowngradeConflictingUseImportRector\Source\Provider;

use E107\Rector\Tests\DowngradePhp70\Rector\Namespace_\DowngradeConflictingUseImportRector\Source\Adapter;
use E107\Rector\Tests\DowngradePhp70\Rector\Namespace_\DowngradeConflictingUseImportRector\Source\Adapter\OpenID as OpenIDAdapter;

class SteamOpenID extends OpenIDAdapter
{
    public function boot(Adapter\OpenID $adapter)
    {
    }
}
