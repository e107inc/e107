<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 */

class rate_classTest extends \Codeception\Test\Unit
{
	/** @var rater */
	protected $rater;

	protected function _before()
	{
		try
		{
			$this->rater = $this->make('rater');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	/**
	 * Regression test: render() must not emit a "Cannot use bool as array" warning
	 * when there is no rating row for the requested table+id. getrating() legitimately
	 * returns false in that case and the list() destructure at the top of render()
	 * used to blow up on PHP 8.5.
	 */
	public function testRenderOnMissingRatingReturnsNoWarning()
	{
		$errors = array();
		set_error_handler(function ($severity, $message, $file, $line) use (&$errors)
		{
			$errors[] = compact('severity', 'message', 'file', 'line');
			return true;
		});

		try
		{
			$html = $this->rater->render('nonexistent_table_for_test', 99999);
		}
		finally
		{
			restore_error_handler();
		}

		self::assertSame(array(), $errors, 'render() must not emit warnings for missing ratings');
		self::assertIsString($html);
	}

	/**
	 * getrating() has multiple return types by design (array, bool, string). All callers
	 * that destructure with list() must tolerate a non-array return. This documents that
	 * contract so a future refactor does not silently regress callers.
	 */
	public function testGetRatingReturnsFalseWhenNoRow()
	{
		$result = $this->rater->getrating('nonexistent_table_for_test', 99999);
		self::assertFalse($result);
	}
}
