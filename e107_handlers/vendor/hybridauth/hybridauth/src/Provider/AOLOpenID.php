<?php
/*!
* Hybridauth
* https://hybridauth.github.io | https://github.com/hybridauth/hybridauth
*  (c) 2017 Hybridauth authors | https://hybridauth.github.io/license.html
*/

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OpenID as OpenIDAdapter;

/**
 * AOL OpenID provider adapter.
 */
class AOLOpenID extends OpenIDAdapter
{
    /**
    * {@inheritdoc}
    */
    protected $openidIdentifier = 'http://openid.aol.com/';
}
