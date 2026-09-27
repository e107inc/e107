<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Fixture;

use E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source\Decoder;

class StaticCallInATernaryArm
{
    public function verify($token, array $keys, $legacy)
    {
        $payload = null;
        foreach ($keys as $pem) {
            try {
                $payload = $legacy ?
                    Decoder::decode($token, $pem, ['RS256']) :
                    Decoder::decode($token, $pem);
                break;
            } catch (\Exception $e) {
            }
        }

        return $payload;
    }
}
-----
<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Fixture;

use E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source\Decoder;

class StaticCallInATernaryArm
{
    public function verify($token, array $keys, $legacy)
    {
        $payload = null;
        foreach ($keys as $pem) {
            try {
                $headers = ['RS256'];
                $payload = $legacy ?
                    Decoder::decode($token, $pem, $headers) :
                    Decoder::decode($token, $pem);
                break;
            } catch (\Exception $e) {
            }
        }

        return $payload;
    }
}
