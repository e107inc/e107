<?php

class e_admin_requestTest extends \Codeception\Test\Unit
{
    /**
     * @var \e_admin_request
     */
    protected $eAdminRequest;

    protected function _before()
    {
        // Instantiate the class e_admin_request
        $this->eAdminRequest = new e_admin_request('testQry1=myQry&searchquery="myQuoted"');
    }

    public function test__construct()
    {
        $this::assertEquals('main', $this->eAdminRequest->getMode());
        $this::assertEquals('index', $this->eAdminRequest->getAction());
        $this::assertEquals(0, $this->eAdminRequest->getId());
    }

    public function testGetQuery()
    {
        $this::assertNull($this->eAdminRequest->getQuery('some_key'));

        $this::assertSame('myQry',$this->eAdminRequest->getQuery('testQry1'));

		$this::assertSame('"myQuoted"', $this->eAdminRequest->getQuery('searchquery'));


    }

    public function testSetQuery()
    {
        $this->eAdminRequest->setQuery('test', 'value');
        $this::assertEquals('value', $this->eAdminRequest->getQuery('test'));
    }

    public function testGetPosted()
    {
        $_POST['test_post'] = 'value';
        $this::assertEquals('value', $this->eAdminRequest->getPosted('test_post'));
    }

    public function testSetPosted()
    {
        $this->eAdminRequest->setPosted('test_post', 'new_value');
        $this::assertEquals('new_value', $this->eAdminRequest->getPosted('test_post'));
    }

    public function testGetMode()
    {
        $this::assertEquals('main', $this->eAdminRequest->getMode());
    }

    public function testSetMode()
    {
        $this->eAdminRequest->setMode('new_mode');
        $this::assertEquals('new_mode', $this->eAdminRequest->getMode());
    }

    public function testGetAction()
    {
        $this::assertEquals('index', $this->eAdminRequest->getAction());
    }

    public function testSetAction()
    {
        $this->eAdminRequest->setAction('new_action');
        $this::assertEquals('new_action', $this->eAdminRequest->getAction());
    }

    public function testGetId()
    {
        $this::assertEquals(0, $this->eAdminRequest->getId());
    }

    public function testSetId()
    {
        $this->eAdminRequest->setId(5);
        $this::assertEquals(5, $this->eAdminRequest->getId());
    }

    public function testBuildQueryString()
    {
        $array = [
            'mode'  => 'default',
            'action'    => 'edit',
            'custom_key' => 'custom_value',
        ];

        $expected_result = "testQry1=myQry&amp;searchquery=%22myQuoted%22&amp;mode=default&amp;action=edit&amp;custom_key=custom_value";

        $this::assertEquals($expected_result, $this->eAdminRequest->buildQueryString($array));
    }

    public function testCamelize()
    {
        $testString = 'test_-string';
        $expected = 'TestString';

        $this::assertEquals($expected, $this->eAdminRequest->camelize($testString));


    }
}