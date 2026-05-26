<?php
/*!
* Hybridauth
* https://hybridauth.github.io | https://github.com/hybridauth/hybridauth
*  (c) 2017 Hybridauth authors | https://hybridauth.github.io/license.html
*/

namespace Hybridauth\Logger;

use Psr\Log\LoggerAwareTrait;

/**
 * Wrapper for PSR3 logger.
 */
class Psr3LoggerWrapper implements LoggerInterface
{
    use LoggerAwareTrait;

    /**
     * @inheritdoc
     * @param mixed[] $context
     */
    public function info($message, $context = [])
    {
        $this->logger->info($message, $context);
    }

    /**
     * @inheritdoc
     * @param mixed[] $context
     */
    public function debug($message, $context = [])
    {
        $this->logger->debug($message, $context);
    }

    /**
     * @inheritdoc
     * @param mixed[] $context
     */
    public function error($message, $context = [])
    {
        $this->logger->error($message, $context);
    }

    /**
     * @inheritdoc
     * @param mixed[] $context
     */
    public function log($level, $message, $context = [])
    {
        $this->logger->log($level, $message, $context);
    }
}
