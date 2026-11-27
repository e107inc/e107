<?php

namespace E107\Rector\Tests\FloorApi\Rector\ClassMethod\DowngradeTentativeReturnTypeRector\Fixture;

function makeSerialiser()
{
    return new class implements \JsonSerializable {
        public function jsonSerialize()
        {
            return [];
        }
    };
}
-----
<?php

namespace E107\Rector\Tests\FloorApi\Rector\ClassMethod\DowngradeTentativeReturnTypeRector\Fixture;

function makeSerialiser()
{
    return new class implements \JsonSerializable {
        #[\ReturnTypeWillChange]
        public function jsonSerialize()
        {
            return [];
        }
    };
}
