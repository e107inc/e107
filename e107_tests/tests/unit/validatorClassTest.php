<?php


	class validatorClassTest extends \Codeception\Test\Unit
	{

		/** @var validatorClass */
		protected $vc;

		protected $vettingInfo;

		protected function _before()
		{

			try
			{
				$this->vc = $this->make('validatorClass');
			}

			catch(Exception $e)
			{
				$this->assertTrue(false, $e->getMessage());
			}

			$this->vettingInfo = array (
			  'user_name' =>
			  array (
				  'niceName'         => 'Display name',
				  'fieldType'        => 'string',
				  'vetMethod'        => '1,2',
				  'vetParam'         => 'signup_disallow_text',
				  'srcName'          => 'username',
				  'stripTags'        => true,
				  'stripChars'       => '/ |\\#|\\=|\\$/',
				  'fixedBlock'       => 'anonymous',
				  'minLength'        => 2,
				  'maxLength'        => '20',
				  ),
				  'user_loginname'   =>
				  array (
				  'niceName'         => 'Login Name',
				  'fieldType'        => 'string',
				  'vetMethod'        => '1',
				  'vetParam'         => '',
				  'srcName'          => 'loginname',
				  'stripTags'        => true,
				  'stripChars'       => '#[^\\p{L}\\p{M}a-z0-9_\\.]#ui',
				  'minLength'        => 2,
				  'maxLength'        => '30',
				  ),
				  'user_login'       =>
				  array (
				  'niceName'         => 'Real Name',
				  'fieldType'        => 'string',
				  'vetMethod'        => '0',
				  'vetParam'         => '',
				  'srcName'          => 'realname',
				  'dbClean'          => 'toDB',
				  'stripTags'        => true,
				  'stripChars'       => '#<|>#i',
				  ),
				  'user_customtitle' =>
				  array (
				  'niceName'         => 'Custom title',
				  'fieldType'        => 'string',
				  'vetMethod'        => '0',
				  'vetParam'         => '',
				  'srcName'          => 'customtitle',
				  'dbClean'          => 'toDB',
				  'enablePref'       => 'signup_option_customtitle',
				  'stripTags'        => true,
				  'stripChars'       => '#<|>#i',
				  ),
				  'user_password'    =>
				  array (
				  'niceName'         => 'Password',
				  'fieldType'        => 'string',
				  'vetMethod'        => '0',
				  'vetParam'         => '',
				  'srcName'          => 'password1',
				  'dataType'         => 2,
				  'minLength'        => '6',
				  ),
				  'user_sess'        =>
				  array (
				  'niceName'         => 'Photograph',
				  'fieldType'        => 'string',
				  'vetMethod'        => '0',
				  'vetParam'         => '',
				  'stripChars'       => '#"|\'|(|)#',
				  'dbClean'          => 'image',
				  'imagePath'        => 'e107_media/b4d51b59e5/avatars/upload/',
				  'maxHeight'        => '80',
				  'maxWidth'         => '80',
				  ),
				  'user_image'       =>
				  array (
				  'niceName'         => 'Avatar',
				  'fieldType'        => 'string',
				  'vetMethod'        => '0',
				  'vetParam'         => '',
				  'srcName'          => 'image',
				  'stripChars'       => '#"|\'|(|)#',
				  'dbClean'          => 'avatar',
				  ),
				  'user_email'       =>
				  array (
				  'niceName'         => 'Email address',
				  'fieldType'        => 'string',
				  'vetMethod'        => '1,3',
				  'vetParam'         => '',
				  'fieldOptional'    => '0',
				  'srcName'          => 'email',
				  'dbClean'          => 'toDB',
				  ),
				  'user_signature'   =>
				  array (
				  'niceName'         => 'Signature',
				  'fieldType'        => 'string',
				  'vetMethod'        => '0',
				  'vetParam'         => '',
				  'srcName'          => 'signature',
				  'dbClean'          => 'toDB',
				  ),
				  'user_hideemail'   =>
				  array (
				  'niceName'         => 'Hide email',
				  'fieldType'        => 'int',
				  'vetMethod'        => '0',
				  'vetParam'         => '',
				  'srcName'          => 'hideemail',
				  'dbClean'          => 'intval',
				  ),
				  'user_xup'         =>
				  array (
				  'niceName'         => 'XUP File',
				  'fieldType'        => 'string',
				  'vetMethod'        => '0',
				  'vetParam'         => '',
				  'srcName'          => 'user_xup',
				  'dbClean'          => 'toDB',
				  ),
				  'user_class'       =>
				  array (
				  'niceName'         => 'User class',
				  'fieldType'        => 'string',
				  'vetMethod'        => '0',
				  'vetParam'         => '',
				  'srcName'          => 'class',
				  'dataType'         => '1',
				  ),
             );

		}
/*
		public function testAddFieldTypes()
		{

		}
*/
		public function testDbValidateArray()
		{

			$posted = array (
			  'data' =>
				  array (
				    'user_name'         => 'user11',
				    'user_loginname'    => 'user11',
				    'user_password'     => 'Test1234',
				    'user_email'        => 'user11@test.com',
				    'user_hideemail'    => 1,
				  ),
			  'failed' => array (),
			  'errors' => array (),
			);

			$expected = $posted;
			$vc = $this->vc;

			$vc::dbValidateArray($posted, $this->vettingInfo, 'user', 0);
			$this->assertSame($expected, $posted);

		}
/*
		public function testFindChanges()
		{

		}

		public function testCheckMandatory()
		{


		}

		public function testMakeErrorList()
		{

		}
*/
		public function testValidateFields()
		{

			// Signup posted data.
			$posted = array (
			  'e-token' => 'faefb3f337edb39dbc0c91abee497b94',
			  'simulation' => '1',
			  'loginname' => 'user11',
			  'email' => 'user11@test.com',
			  'email2' => '',
			  'password1' => 'Test1234',
			  'password2' => 'Test1234',
			  'register' => 'Register',
			  'hideemail' => 1,
			  'email_confirm' => 'user11@test.com',
			  'username' => 'user11',
			);

			$expected = array (
			  'data' =>
				  array (
				    'user_name'         => 'user11',
				    'user_loginname'    => 'user11',
				    'user_password'     => 'Test1234',
				    'user_email'        => 'user11@test.com',
				    'user_hideemail'    => 1,
				  ),
			  'failed' => array (),
			  'errors' => array (),
			);


			$vc = $this->vc;
			$result = $vc::validateFields($posted, $this->vettingInfo, true);

			$this->assertSame($expected, $result);

		}




	}
