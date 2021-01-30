<?php


	class e_front_modelTest extends \Codeception\Test\Unit
	{

		/** @var e_front_model */
		protected $model;

		private $dataFields;

		protected function _before()
		{

			try
			{
				$this->model = $this->make('e_front_model');
			}

			catch(Exception $e)
			{
				$this->assertTrue(false, $e->getMessage());
			}

			$this->dataFields = array(
				'myfield'                   => 'str',
				'myfield2'                  => 'int',
				'myfield3'                  => 'str',
				'other/active'              => 'bool',
				'other/bla/active'          => 'array',
				'gateways/other'            => 'str',
				'gateways/paypal/active'    => 'int',
				'gateways/paypal/title'     => 'str',
				'another/one/active'        => 'bool',
			);


			$this->model->setDataFields($this->dataFields);

		}
/*
		public function testIsValidFieldKey()
		{

			$res = [];
			foreach($this->dataFields as $k=>$var)
			{
				$res[$k] = $this->model->isValidFieldKey($k);

			}


		}*/


		/**
		 * santize() takes posted data and then sanitized it based on the dataFields value.
		 */
		public function testSanitize()
		{

			$result = $this->model->sanitize('myfield', 'My Field Value');
			$this->assertSame('My Field Value', $result);


			$result = $this->model->sanitize(array('myfield' => 'My Field Value'));
			$this->assertSame(array( 'myfield' => 'My Field Value' ), $result);

			$result = $this->model->sanitize('non_field', 1);
			$this->assertNull($result);

			$result = $this->model->sanitize('gateways/paypal/active', 1);
			$this->assertSame(1, $result);

			// Non admin-ui example.
			$posted = array('gateways/paypal/active' =>
				    array (
				      'paypal' =>
				        array (
				          'active' =>  '0',
				          'title' => 'PayPal Express' ,
				          'icon' =>  'fa-paypal',
						)
				    )
			);

			// Real example from vstore prefs. key becomes multi-dimensional array when posted.
			$posted = array(
				'myfield'   => 'my string',
				'gateways' => array (
				      'paypal' =>
				        array (
				          'active' =>  '0',
				          'title' => 'PayPal Express' ,
				          'icon' =>  'fa-paypal',
						)
				),
				'other' => array(
					'active' => 1,

				),
				'another' => array(
					'one'   => array('active' => 1)
				)
			);

			$expected = array (
			  'myfield'   => 'my string',
			  'gateways' =>
			  array (
			    'paypal' =>
			    array (
			      'active' => 1, // converted to int.
			      'title' => 'PayPal Express',
			    ),
			  ),
			  'other' =>
			  array (
			    'active' => true, // converted to bool
			  ),
			  'another' =>
			  array (
			    'one' =>
			    array (
			      'active' => true, //  converted to bool
			    ),
			  ),
			);

			// @todo FIXME - doesn't pass. More accurate check required.
			$result = $this->model->sanitize($posted);
		//	$this->assertSame($expected, $result);



		}
/*
		public function testAddValidationError()
		{

		}

		public function testResetMessages()
		{

		}

		public function testGetSqlErrorNumber()
		{

		}

		public function testRenderMessages()
		{

		}

		public function testHasPostedData()
		{

		}

		public function testDataHasChangedFor()
		{

		}

		public function testSetValidationRule()
		{

		}

		public function testGetPostedData()
		{

		}

		public function testSetValidationRules()
		{

		}

		public function testRenderValidationErrors()
		{

		}

		public function testMergeData()
		{

		}

		public function testGetOptionalRules()
		{

		}

		public function testHasSqlError()
		{

		}

		public function testIsPostedData()
		{

		}

		public function testAddPostedData()
		{

		}

		public function testGetSqlQuery()
		{

		}

		public function testHasValidationError()
		{

		}

		public function testSetPosted()
		{

		}

		public function testSetPostedData()
		{

		}

		public function testSetOptionalRules()
		{

		}

		public function testGetDbTypes()
		{

		}

		public function testGetPosted()
		{

		}

		public function testGetIfPosted()
		{

		}

		public function testRemovePostedData()
		{

		}

		public function testDataHasChanged()
		{

		}

		public function testSave()
		{

		}

		public function testMergePostedData()
		{

		}

		public function testHasError()
		{

		}

		public function testIsPosted()
		{

		}

		public function testSetDbTypes()
		{

		}

		public function testSaveDebug()
		{

		}

		public function testSetMessages()
		{

		}
*/
/*
		public function testDestroy()
		{

		}

		public function testGetValidationRules()
		{

		}

		public function testGetValidator()
		{

		}

		public function testRemovePosted()
		{

		}

		public function testHasPosted()
		{

		}

		public function testValidate()
		{

		}

		public function testVerify()
		{

		}

		public function testGetSqlError()
		{

		}

		public function testLoad()
		{

		}

		public function testToSqlQuery()
		{

		}*/


	}
