<?php

namespace E107\Rector\Tests\Downgrade\Fixture;

class Uri implements \JsonSerializable
{
    public function jsonSerialize(): string
    {
        return $this->__toString();
    }

    public function __toString()
    {
        return '';
    }
}
-----
<?php

namespace E107\Rector\Tests\Downgrade\Fixture;

class Uri implements \JsonSerializable
{
    /**
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->__toString();
    }

    public function __toString()
    {
        return '';
    }
}
