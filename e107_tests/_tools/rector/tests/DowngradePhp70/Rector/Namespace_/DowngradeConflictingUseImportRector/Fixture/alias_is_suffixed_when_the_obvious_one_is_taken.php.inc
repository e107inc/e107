<?php

namespace E107\Rector\Tests\DowngradePhp70\Rector\Namespace_\DowngradeConflictingUseImportRector\Source\Provider;

use E107\Rector\Tests\DowngradePhp70\Rector\Namespace_\DowngradeConflictingUseImportRector\Source\Adapter\OpenID;
use Psr\Log\LoggerInterface as OpenIDAdapter;

class CollidingProvider extends OpenID
{
    /**
     * @var OpenID
     */
    private $adapter;

    /**
     * @param OpenID|null $given
     * @return OpenID
     */
    public function boot(Openid $given)
    {
        return $given;
    }
}
-----
<?php

namespace E107\Rector\Tests\DowngradePhp70\Rector\Namespace_\DowngradeConflictingUseImportRector\Source\Provider;

use E107\Rector\Tests\DowngradePhp70\Rector\Namespace_\DowngradeConflictingUseImportRector\Source\Adapter\OpenID as OpenIDAdapter2;
use Psr\Log\LoggerInterface as OpenIDAdapter;

class CollidingProvider extends OpenIDAdapter2
{
    /**
     * @var OpenIDAdapter2
     */
    private $adapter;

    /**
     * @param OpenIDAdapter2|null $given
     * @return OpenIDAdapter2
     */
    public function boot(OpenIDAdapter2 $given)
    {
        return $given;
    }
}
