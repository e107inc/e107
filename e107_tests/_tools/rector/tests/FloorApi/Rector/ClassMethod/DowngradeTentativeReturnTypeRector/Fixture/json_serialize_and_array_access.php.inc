<?php

namespace E107\Rector\Tests\FloorApi\Rector\ClassMethod\DowngradeTentativeReturnTypeRector\Fixture;

class SomeUri implements \JsonSerializable, \ArrayAccess
{
    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return '';
    }

    public function offsetGet($offset)
    {
        return null;
    }

    public function offsetExists($offset)
    {
        return false;
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }

    public function withScheme($scheme)
    {
        return $this;
    }
}
-----
<?php

namespace E107\Rector\Tests\FloorApi\Rector\ClassMethod\DowngradeTentativeReturnTypeRector\Fixture;

class SomeUri implements \JsonSerializable, \ArrayAccess
{
    /**
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return '';
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return null;
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return false;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
    }

    public function withScheme($scheme)
    {
        return $this;
    }
}
