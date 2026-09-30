<?php

namespace E107\Rector\Tests\Downgrade\Fixture\HybridauthApple;

class JWT
{
    public static function decode($jwt, $keyOrKeyArray, &$headers = null)
    {
        return $jwt;
    }
}

class Apple
{
    protected function verifyIdToken($id_token, $pem, $jwtVersion)
    {
        $payload = (version_compare($jwtVersion, '6.2') < 0) ?
            JWT::decode($id_token, $pem, ['RS256']) :
            JWT::decode($id_token, $pem);

        return $payload;
    }
}
-----
<?php

namespace E107\Rector\Tests\Downgrade\Fixture\HybridauthApple;

class JWT
{
    public static function decode($jwt, $keyOrKeyArray, &$headers = null)
    {
        return $jwt;
    }
}

class Apple
{
    protected function verifyIdToken($id_token, $pem, $jwtVersion)
    {
        $headers = ['RS256'];
        $payload = (version_compare($jwtVersion, '6.2') < 0) ?
            JWT::decode($id_token, $pem, $headers) :
            JWT::decode($id_token, $pem);

        return $payload;
    }
}
