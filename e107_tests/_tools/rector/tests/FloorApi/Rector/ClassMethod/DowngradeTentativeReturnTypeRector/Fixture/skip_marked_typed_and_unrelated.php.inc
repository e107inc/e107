<?php

namespace E107\Rector\Tests\FloorApi\Rector\ClassMethod\DowngradeTentativeReturnTypeRector\Fixture;

class SomeSettledUri implements \JsonSerializable, \ArrayAccess
{
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return '';
    }

    public function offsetGet($offset): mixed
    {
        return null;
    }

    public function offsetExists($offset): bool
    {
        return false;
    }

    public function offsetSet($offset, $value): void
    {
    }

    public function offsetUnset($offset): void
    {
    }
}

class SomePlainBag
{
    public function offsetGet($offset)
    {
        return null;
    }

    public function jsonSerialize()
    {
        return '';
    }
}
