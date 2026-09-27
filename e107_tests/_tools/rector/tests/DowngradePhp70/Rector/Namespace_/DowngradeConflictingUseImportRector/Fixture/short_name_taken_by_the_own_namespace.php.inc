<?php

namespace E107\Rector\Tests\DowngradePhp70\Rector\Namespace_\DowngradeConflictingUseImportRector\Source\Provider;

use E107\Rector\Tests\DowngradePhp70\Rector\Namespace_\DowngradeConflictingUseImportRector\Source\Adapter\OpenID;

class AOLOpenID extends OpenID
{
    public function boot(OpenID $adapter)
    {
        return OpenID::class;
    }
}
-----
<?php

namespace E107\Rector\Tests\DowngradePhp70\Rector\Namespace_\DowngradeConflictingUseImportRector\Source\Provider;

use E107\Rector\Tests\DowngradePhp70\Rector\Namespace_\DowngradeConflictingUseImportRector\Source\Adapter\OpenID as OpenIDAdapter;

class AOLOpenID extends OpenIDAdapter
{
    public function boot(OpenIDAdapter $adapter)
    {
        return OpenIDAdapter::class;
    }
}
