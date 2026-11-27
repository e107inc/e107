<?php

namespace E107\Rector\Tests\DowngradePhp70\Rector\Namespace_\DowngradeConflictingUseImportRector\Source\Provider;

use E107\Rector\Tests\DowngradePhp70\Rector\Namespace_\DowngradeConflictingUseImportRector\Source\Adapter\OpenID;

class AOLOpenID extends OpenID
{
    protected $openidIdentifier = 'http://openid.aol.com/';
}
-----
<?php

namespace E107\Rector\Tests\DowngradePhp70\Rector\Namespace_\DowngradeConflictingUseImportRector\Source\Provider;

use E107\Rector\Tests\DowngradePhp70\Rector\Namespace_\DowngradeConflictingUseImportRector\Source\Adapter\OpenID as OpenIDAdapter;

class AOLOpenID extends OpenIDAdapter
{
    protected $openidIdentifier = 'http://openid.aol.com/';
}
