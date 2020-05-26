<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace e107\SessionHandlers;


use e107\Factories\SessionHandlerFactory;
use PHPUnit\Framework\AssertionFailedError;

class DatabaseSessionHandlerTest extends BaseSessionHandlerTest
{
    /**
     * @var DatabaseSessionHandler
     */
    private $sessionHandler;

    public function _before()
    {
        $this->sessionHandler = SessionHandlerFactory::make(DatabaseSessionHandler::class);
    }

    public function testReadFailureThrowsException()
    {
        \e107::getDb()->gen('DROP TABLE e107_session_bak');
        \e107::getDb()->gen('ALTER TABLE e107_session RENAME e107_session_bak');

        try
        {
            $this->sessionHandler->read('whatever');
            $this->fail('Session read did not throw an exception');
        }
        catch (\RuntimeException $e)
        {
            if ($e instanceof AssertionFailedError) throw $e;
            $this->assertTrue(true, 'Session read threw an exception as expected: ' . $e->getMessage());
        }
        finally
        {
            \e107::getDb()->gen('ALTER TABLE e107_session_bak RENAME e107_session');
        }
    }

    public function testWriteReturnsFalseWithBadSessionId()
    {
        $this->assertFalse($this->sessionHandler->write('!@#$%^&*()', 'Hello'));
    }

    public function testWriteFailureThrowsException()
    {
        \e107::getDb()->gen('DROP TABLE e107_session_bak');
        \e107::getDb()->gen('ALTER TABLE e107_session RENAME e107_session_bak');

        try
        {
            $this->sessionHandler->write('whatever', 'session data');
            $this->fail('Session write did not throw an exception');
        }
        catch (\RuntimeException $e)
        {
            if ($e instanceof AssertionFailedError) throw $e;
            $this->assertTrue(true, 'Session write threw an exception as expected: ' . $e->getMessage());
        }
        finally
        {
            \e107::getDb()->gen('ALTER TABLE e107_session_bak RENAME e107_session');
        }
    }
}
