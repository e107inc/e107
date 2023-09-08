<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class class2Test extends \Codeception\Test\Unit
	{
		public $usr;
		/*protected function _before()
		{

			try
			{
				$this->usr = $this->make('e_user_model');
			}
			catch(Exception $e)
			{
				$this->fail( "Couldn't load e_user_model object");
			}

			e107::getUser()->load(1); // load user_id  = 1.

		}*/

		function testLoadClass2()
		{
			require_once(e_BASE."class2.php"); // already loaded but coverage says otherwise.

		}


		function testGetPerms()
		{
		//	$this->markTestSkipped("Skipped - CLI mode changes behavior.");
			// See class2.php Line 1643

			$result = getperms('N', '5');
			$this->assertFalse($result);

			$result = getperms('N', '0');
			$this->assertTrue($result);

			$result = getperms('N', '0.');
			$this->assertTrue($result);

			$result = getperms('U1|U2', '0.');
			$this->assertTrue($result);



			$pid = e107::getDb()->retrieve('plugin', 'plugin_id', "plugin_path = 'gallery'");

			$result = getperms('P', 'P'.$pid);
			$this->assertFalse($result);


			$result = getperms('P', 'P'.$pid, 'http://localhost/e107v2/e107_plugins/gallery/admin_config.php');
			$this->assertTrue($result);


		}

		function testUserModel()
		{
			$result = e107::getUser();
			var_dump($result);
		}



		function testCheckClass()
		{
			// XXX: Should not use some flag just to make tests pass!
			global $_E107;
			$_E107['phpunit'] = true;

			$result = check_class(0, "253,254,250,251,0");
			$this->assertTrue($result);

			$result = check_class('NEWSLETTER', "253,254,250,251,0");
			$this->assertFalse($result);

			$result = check_class('NEWSLETTER', "253,254,250,251,3,0"); // NEWSLETTER = 3
			$this->assertTrue($result);

			$result = check_class('-NEWSLETTER', "253,254,250,251,0");
			$this->assertTrue($result);

			$result = check_class(254, "253,254,250,251,0");
			$this->assertTrue($result);

			$result = check_class('0', "253,254,250,251,0");
			$this->assertTrue($result);

			$result = check_class(null, "253,254,250,251,0");
			$this->assertFalse($result);

			$result = check_class('-254', "253,254,250,251,0");
			$this->assertFalse($result);

			$result = check_class('-254', "253,250,251,0");
			$this->assertTrue($result);

			$result = check_class(-254, "253,250,251,0");
			$this->assertTrue($result);

			$result = check_class(-254, "254,253,250,251,0");
			$this->assertFalse($result);

			$result = check_class(e_UC_NOBODY, "253,254,250,251,0");
			$this->assertFalse($result);

			$result = check_class(e_UC_NEWUSER, "247,253,254,250,251,0");
			$this->assertTrue($result);

			$result = check_class(e_UC_NEWUSER, "253,254,250,251,0");
			$this->assertFalse($result);

			unset($_E107['phpunit']);
		}



		function testCheckEmail()
		{
			$result = check_email("test@somewhere.com"); // good email.
			$this->assertEquals('test@somewhere.com', $result);

			$result = check_email("test@somewherecom"); // Missing .
			$this->assertFalse($result);

			$result = check_email("test@somewhere.technology"); // New TLDs
			$this->assertEquals('test@somewhere.technology',$result);

		}



		function testSystemTimeZoneIsValid()
		{
			$tests = array(
				'America/Los_Angeles'   => true,
				'bla/foo'               => false,
				'Pacific/Wake'          => true,

			);

			foreach($tests as $zone=>$expected)
			{
				$result = systemTimeZoneIsValid($zone);
				$this->assertSame($expected, $result);


			}


		}

		function getExpectedZones()
		{
			return array (
			  'Africa/Abidjan' => 'Africa/Abidjan (+00:00)',
			  'Africa/Accra' => 'Africa/Accra (+00:00)',
			  'Africa/Addis_Ababa' => 'Africa/Addis Ababa (+03:00)',
			  'Africa/Algiers' => 'Africa/Algiers (+01:00)',
			  'Africa/Asmara' => 'Africa/Asmara (+03:00)',
			  'Africa/Bamako' => 'Africa/Bamako (+00:00)',
			  'Africa/Bangui' => 'Africa/Bangui (+01:00)',
			  'Africa/Banjul' => 'Africa/Banjul (+00:00)',
			  'Africa/Bissau' => 'Africa/Bissau (+00:00)',
			  'Africa/Blantyre' => 'Africa/Blantyre (+02:00)',
			  'Africa/Brazzaville' => 'Africa/Brazzaville (+01:00)',
			  'Africa/Bujumbura' => 'Africa/Bujumbura (+02:00)',
			  'Africa/Cairo' => 'Africa/Cairo (+02:00)',
			  'Africa/Casablanca' => 'Africa/Casablanca (+01:00)',
			  'Africa/Ceuta' => 'Africa/Ceuta (+01:00)',
			  'Africa/Conakry' => 'Africa/Conakry (+00:00)',
			  'Africa/Dakar' => 'Africa/Dakar (+00:00)',
			  'Africa/Dar_es_Salaam' => 'Africa/Dar es Salaam (+03:00)',
			  'Africa/Djibouti' => 'Africa/Djibouti (+03:00)',
			  'Africa/Douala' => 'Africa/Douala (+01:00)',
			  'Africa/El_Aaiun' => 'Africa/El Aaiun (+01:00)',
			  'Africa/Freetown' => 'Africa/Freetown (+00:00)',
			  'Africa/Gaborone' => 'Africa/Gaborone (+02:00)',
			  'Africa/Harare' => 'Africa/Harare (+02:00)',
			  'Africa/Johannesburg' => 'Africa/Johannesburg (+02:00)',
			  'Africa/Juba' => 'Africa/Juba (+03:00)',
			  'Africa/Kampala' => 'Africa/Kampala (+03:00)',
			  'Africa/Khartoum' => 'Africa/Khartoum (+02:00)',
			  'Africa/Kigali' => 'Africa/Kigali (+02:00)',
			  'Africa/Kinshasa' => 'Africa/Kinshasa (+01:00)',
			  'Africa/Lagos' => 'Africa/Lagos (+01:00)',
			  'Africa/Libreville' => 'Africa/Libreville (+01:00)',
			  'Africa/Lome' => 'Africa/Lome (+00:00)',
			  'Africa/Luanda' => 'Africa/Luanda (+01:00)',
			  'Africa/Lubumbashi' => 'Africa/Lubumbashi (+02:00)',
			  'Africa/Lusaka' => 'Africa/Lusaka (+02:00)',
			  'Africa/Malabo' => 'Africa/Malabo (+01:00)',
			  'Africa/Maputo' => 'Africa/Maputo (+02:00)',
			  'Africa/Maseru' => 'Africa/Maseru (+02:00)',
			  'Africa/Mbabane' => 'Africa/Mbabane (+02:00)',
			  'Africa/Mogadishu' => 'Africa/Mogadishu (+03:00)',
			  'Africa/Monrovia' => 'Africa/Monrovia (+00:00)',
			  'Africa/Nairobi' => 'Africa/Nairobi (+03:00)',
			  'Africa/Ndjamena' => 'Africa/Ndjamena (+01:00)',
			  'Africa/Niamey' => 'Africa/Niamey (+01:00)',
			  'Africa/Nouakchott' => 'Africa/Nouakchott (+00:00)',
			  'Africa/Ouagadougou' => 'Africa/Ouagadougou (+00:00)',
			  'Africa/Porto-Novo' => 'Africa/Porto-Novo (+01:00)',
			  'Africa/Sao_Tome' => 'Africa/Sao Tome (+00:00)',
			  'Africa/Tripoli' => 'Africa/Tripoli (+02:00)',
			  'Africa/Tunis' => 'Africa/Tunis (+01:00)',
			  'Africa/Windhoek' => 'Africa/Windhoek (+02:00)',
			  'America/Adak' => 'America/Adak (-10:00)',
			  'America/Anchorage' => 'America/Anchorage (-09:00)',
			  'America/Anguilla' => 'America/Anguilla (-04:00)',
			  'America/Antigua' => 'America/Antigua (-04:00)',
			  'America/Araguaina' => 'America/Araguaina (-03:00)',
			  'America/Argentina/Buenos_Aires' => 'America/Argentina/Buenos Aires (-03:00)',
			  'America/Argentina/Catamarca' => 'America/Argentina/Catamarca (-03:00)',
			  'America/Argentina/Cordoba' => 'America/Argentina/Cordoba (-03:00)',
			  'America/Argentina/Jujuy' => 'America/Argentina/Jujuy (-03:00)',
			  'America/Argentina/La_Rioja' => 'America/Argentina/La Rioja (-03:00)',
			  'America/Argentina/Mendoza' => 'America/Argentina/Mendoza (-03:00)',
			  'America/Argentina/Rio_Gallegos' => 'America/Argentina/Rio Gallegos (-03:00)',
			  'America/Argentina/Salta' => 'America/Argentina/Salta (-03:00)',
			  'America/Argentina/San_Juan' => 'America/Argentina/San Juan (-03:00)',
			  'America/Argentina/San_Luis' => 'America/Argentina/San Luis (-03:00)',
			  'America/Argentina/Tucuman' => 'America/Argentina/Tucuman (-03:00)',
			  'America/Argentina/Ushuaia' => 'America/Argentina/Ushuaia (-03:00)',
			  'America/Aruba' => 'America/Aruba (-04:00)',
			  'America/Asuncion' => 'America/Asuncion (-03:00)',
			  'America/Atikokan' => 'America/Atikokan (-05:00)',
			  'America/Bahia' => 'America/Bahia (-03:00)',
			  'America/Bahia_Banderas' => 'America/Bahia Banderas (-06:00)',
			  'America/Barbados' => 'America/Barbados (-04:00)',
			  'America/Belem' => 'America/Belem (-03:00)',
			  'America/Belize' => 'America/Belize (-06:00)',
			  'America/Blanc-Sablon' => 'America/Blanc-Sablon (-04:00)',
			  'America/Boa_Vista' => 'America/Boa Vista (-04:00)',
			  'America/Bogota' => 'America/Bogota (-05:00)',
			  'America/Boise' => 'America/Boise (-07:00)',
			  'America/Cambridge_Bay' => 'America/Cambridge Bay (-07:00)',
			  'America/Campo_Grande' => 'America/Campo Grande (-04:00)',
			  'America/Cancun' => 'America/Cancun (-05:00)',
			  'America/Caracas' => 'America/Caracas (-04:00)',
			  'America/Cayenne' => 'America/Cayenne (-03:00)',
			  'America/Cayman' => 'America/Cayman (-05:00)',
			  'America/Chicago' => 'America/Chicago (-06:00)',
			  'America/Chihuahua' => 'America/Chihuahua (-07:00)',
			  'America/Costa_Rica' => 'America/Costa Rica (-06:00)',
			  'America/Creston' => 'America/Creston (-07:00)',
			  'America/Cuiaba' => 'America/Cuiaba (-04:00)',
			  'America/Curacao' => 'America/Curacao (-04:00)',
			  'America/Danmarkshavn' => 'America/Danmarkshavn (+00:00)',
			  'America/Dawson' => 'America/Dawson (-07:00)',
			  'America/Dawson_Creek' => 'America/Dawson Creek (-07:00)',
			  'America/Denver' => 'America/Denver (-07:00)',
			  'America/Detroit' => 'America/Detroit (-05:00)',
			  'America/Dominica' => 'America/Dominica (-04:00)',
			  'America/Edmonton' => 'America/Edmonton (-07:00)',
			  'America/Eirunepe' => 'America/Eirunepe (-05:00)',
			  'America/El_Salvador' => 'America/El Salvador (-06:00)',
			  'America/Fort_Nelson' => 'America/Fort Nelson (-07:00)',
			  'America/Fortaleza' => 'America/Fortaleza (-03:00)',
			  'America/Glace_Bay' => 'America/Glace Bay (-04:00)',
			  'America/Goose_Bay' => 'America/Goose Bay (-04:00)',
			  'America/Grand_Turk' => 'America/Grand Turk (-05:00)',
			  'America/Grenada' => 'America/Grenada (-04:00)',
			  'America/Guadeloupe' => 'America/Guadeloupe (-04:00)',
			  'America/Guatemala' => 'America/Guatemala (-06:00)',
			  'America/Guayaquil' => 'America/Guayaquil (-05:00)',
			  'America/Guyana' => 'America/Guyana (-04:00)',
			  'America/Halifax' => 'America/Halifax (-04:00)',
			  'America/Havana' => 'America/Havana (-05:00)',
			  'America/Hermosillo' => 'America/Hermosillo (-07:00)',
			  'America/Indiana/Indianapolis' => 'America/Indiana/Indianapolis (-05:00)',
			  'America/Indiana/Knox' => 'America/Indiana/Knox (-06:00)',
			  'America/Indiana/Marengo' => 'America/Indiana/Marengo (-05:00)',
			  'America/Indiana/Petersburg' => 'America/Indiana/Petersburg (-05:00)',
			  'America/Indiana/Tell_City' => 'America/Indiana/Tell City (-06:00)',
			  'America/Indiana/Vevay' => 'America/Indiana/Vevay (-05:00)',
			  'America/Indiana/Vincennes' => 'America/Indiana/Vincennes (-05:00)',
			  'America/Indiana/Winamac' => 'America/Indiana/Winamac (-05:00)',
			  'America/Inuvik' => 'America/Inuvik (-07:00)',
			  'America/Iqaluit' => 'America/Iqaluit (-05:00)',
			  'America/Jamaica' => 'America/Jamaica (-05:00)',
			  'America/Juneau' => 'America/Juneau (-09:00)',
			  'America/Kentucky/Louisville' => 'America/Kentucky/Louisville (-05:00)',
			  'America/Kentucky/Monticello' => 'America/Kentucky/Monticello (-05:00)',
			  'America/Kralendijk' => 'America/Kralendijk (-04:00)',
			  'America/La_Paz' => 'America/La Paz (-04:00)',
			  'America/Lima' => 'America/Lima (-05:00)',
			  'America/Los_Angeles' => 'America/Los Angeles (-08:00)',
			  'America/Lower_Princes' => 'America/Lower Princes (-04:00)',
			  'America/Maceio' => 'America/Maceio (-03:00)',
			  'America/Managua' => 'America/Managua (-06:00)',
			  'America/Manaus' => 'America/Manaus (-04:00)',
			  'America/Marigot' => 'America/Marigot (-04:00)',
			  'America/Martinique' => 'America/Martinique (-04:00)',
			  'America/Matamoros' => 'America/Matamoros (-06:00)',
			  'America/Mazatlan' => 'America/Mazatlan (-07:00)',
			  'America/Menominee' => 'America/Menominee (-06:00)',
			  'America/Merida' => 'America/Merida (-06:00)',
			  'America/Metlakatla' => 'America/Metlakatla (-09:00)',
			  'America/Mexico_City' => 'America/Mexico City (-06:00)',
			  'America/Miquelon' => 'America/Miquelon (-03:00)',
			  'America/Moncton' => 'America/Moncton (-04:00)',
			  'America/Monterrey' => 'America/Monterrey (-06:00)',
			  'America/Montevideo' => 'America/Montevideo (-03:00)',
			  'America/Montserrat' => 'America/Montserrat (-04:00)',
			  'America/Nassau' => 'America/Nassau (-05:00)',
			  'America/New_York' => 'America/New York (-05:00)',
			  'America/Nipigon' => 'America/Nipigon (-05:00)',
			  'America/Nome' => 'America/Nome (-09:00)',
			  'America/Noronha' => 'America/Noronha (-02:00)',
			  'America/North_Dakota/Beulah' => 'America/North Dakota/Beulah (-06:00)',
			  'America/North_Dakota/Center' => 'America/North Dakota/Center (-06:00)',
			  'America/North_Dakota/New_Salem' => 'America/North Dakota/New Salem (-06:00)',
			  'America/Nuuk' => 'America/Nuuk (-03:00)',
			  'America/Ojinaga' => 'America/Ojinaga (-07:00)',
			  'America/Panama' => 'America/Panama (-05:00)',
			  'America/Pangnirtung' => 'America/Pangnirtung (-05:00)',
			  'America/Paramaribo' => 'America/Paramaribo (-03:00)',
			  'America/Phoenix' => 'America/Phoenix (-07:00)',
			  'America/Port_of_Spain' => 'America/Port of Spain (-04:00)',
			  'America/Port-au-Prince' => 'America/Port-au-Prince (-05:00)',
			  'America/Porto_Velho' => 'America/Porto Velho (-04:00)',
			  'America/Puerto_Rico' => 'America/Puerto Rico (-04:00)',
			  'America/Punta_Arenas' => 'America/Punta Arenas (-03:00)',
			  'America/Rainy_River' => 'America/Rainy River (-06:00)',
			  'America/Rankin_Inlet' => 'America/Rankin Inlet (-06:00)',
			  'America/Recife' => 'America/Recife (-03:00)',
			  'America/Regina' => 'America/Regina (-06:00)',
			  'America/Resolute' => 'America/Resolute (-06:00)',
			  'America/Rio_Branco' => 'America/Rio Branco (-05:00)',
			  'America/Santarem' => 'America/Santarem (-03:00)',
			  'America/Santiago' => 'America/Santiago (-03:00)',
			  'America/Santo_Domingo' => 'America/Santo Domingo (-04:00)',
			  'America/Sao_Paulo' => 'America/Sao Paulo (-03:00)',
			  'America/Scoresbysund' => 'America/Scoresbysund (-01:00)',
			  'America/Sitka' => 'America/Sitka (-09:00)',
			  'America/St_Barthelemy' => 'America/St Barthelemy (-04:00)',
			  'America/St_Johns' => 'America/St Johns (-03:30)',
			  'America/St_Kitts' => 'America/St Kitts (-04:00)',
			  'America/St_Lucia' => 'America/St Lucia (-04:00)',
			  'America/St_Thomas' => 'America/St Thomas (-04:00)',
			  'America/St_Vincent' => 'America/St Vincent (-04:00)',
			  'America/Swift_Current' => 'America/Swift Current (-06:00)',
			  'America/Tegucigalpa' => 'America/Tegucigalpa (-06:00)',
			  'America/Thule' => 'America/Thule (-04:00)',
			  'America/Thunder_Bay' => 'America/Thunder Bay (-05:00)',
			  'America/Tijuana' => 'America/Tijuana (-08:00)',
			  'America/Toronto' => 'America/Toronto (-05:00)',
			  'America/Tortola' => 'America/Tortola (-04:00)',
			  'America/Vancouver' => 'America/Vancouver (-08:00)',
			  'America/Whitehorse' => 'America/Whitehorse (-07:00)',
			  'America/Winnipeg' => 'America/Winnipeg (-06:00)',
			  'America/Yakutat' => 'America/Yakutat (-09:00)',
			  'America/Yellowknife' => 'America/Yellowknife (-07:00)',
			  'Antarctica/Casey' => 'Antarctica/Casey (+11:00)',
			  'Antarctica/Davis' => 'Antarctica/Davis (+07:00)',
			  'Antarctica/DumontDUrville' => 'Antarctica/DumontDUrville (+10:00)',
			  'Antarctica/Macquarie' => 'Antarctica/Macquarie (+11:00)',
			  'Antarctica/Mawson' => 'Antarctica/Mawson (+05:00)',
			  'Antarctica/McMurdo' => 'Antarctica/McMurdo (+13:00)',
			  'Antarctica/Palmer' => 'Antarctica/Palmer (-03:00)',
			  'Antarctica/Rothera' => 'Antarctica/Rothera (-03:00)',
			  'Antarctica/Syowa' => 'Antarctica/Syowa (+03:00)',
			  'Antarctica/Troll' => 'Antarctica/Troll (+00:00)',
			  'Antarctica/Vostok' => 'Antarctica/Vostok (+06:00)',
			  'Arctic/Longyearbyen' => 'Arctic/Longyearbyen (+01:00)',
			  'Asia/Aden' => 'Asia/Aden (+03:00)',
			  'Asia/Almaty' => 'Asia/Almaty (+06:00)',
			  'Asia/Amman' => 'Asia/Amman (+02:00)',
			  'Asia/Anadyr' => 'Asia/Anadyr (+12:00)',
			  'Asia/Aqtau' => 'Asia/Aqtau (+05:00)',
			  'Asia/Aqtobe' => 'Asia/Aqtobe (+05:00)',
			  'Asia/Ashgabat' => 'Asia/Ashgabat (+05:00)',
			  'Asia/Atyrau' => 'Asia/Atyrau (+05:00)',
			  'Asia/Baghdad' => 'Asia/Baghdad (+03:00)',
			  'Asia/Bahrain' => 'Asia/Bahrain (+03:00)',
			  'Asia/Baku' => 'Asia/Baku (+04:00)',
			  'Asia/Bangkok' => 'Asia/Bangkok (+07:00)',
			  'Asia/Barnaul' => 'Asia/Barnaul (+07:00)',
			  'Asia/Beirut' => 'Asia/Beirut (+02:00)',
			  'Asia/Bishkek' => 'Asia/Bishkek (+06:00)',
			  'Asia/Brunei' => 'Asia/Brunei (+08:00)',
			  'Asia/Chita' => 'Asia/Chita (+09:00)',
			  'Asia/Choibalsan' => 'Asia/Choibalsan (+08:00)',
			  'Asia/Colombo' => 'Asia/Colombo (+05:30)',
			  'Asia/Damascus' => 'Asia/Damascus (+02:00)',
			  'Asia/Dhaka' => 'Asia/Dhaka (+06:00)',
			  'Asia/Dili' => 'Asia/Dili (+09:00)',
			  'Asia/Dubai' => 'Asia/Dubai (+04:00)',
			  'Asia/Dushanbe' => 'Asia/Dushanbe (+05:00)',
			  'Asia/Famagusta' => 'Asia/Famagusta (+02:00)',
			  'Asia/Gaza' => 'Asia/Gaza (+02:00)',
			  'Asia/Hebron' => 'Asia/Hebron (+02:00)',
			  'Asia/Ho_Chi_Minh' => 'Asia/Ho Chi Minh (+07:00)',
			  'Asia/Hong_Kong' => 'Asia/Hong Kong (+08:00)',
			  'Asia/Hovd' => 'Asia/Hovd (+07:00)',
			  'Asia/Irkutsk' => 'Asia/Irkutsk (+08:00)',
			  'Asia/Jakarta' => 'Asia/Jakarta (+07:00)',
			  'Asia/Jayapura' => 'Asia/Jayapura (+09:00)',
			  'Asia/Jerusalem' => 'Asia/Jerusalem (+02:00)',
			  'Asia/Kabul' => 'Asia/Kabul (+04:30)',
			  'Asia/Kamchatka' => 'Asia/Kamchatka (+12:00)',
			  'Asia/Karachi' => 'Asia/Karachi (+05:00)',
			  'Asia/Kathmandu' => 'Asia/Kathmandu (+05:45)',
			  'Asia/Khandyga' => 'Asia/Khandyga (+09:00)',
			  'Asia/Kolkata' => 'Asia/Kolkata (+05:30)',
			  'Asia/Krasnoyarsk' => 'Asia/Krasnoyarsk (+07:00)',
			  'Asia/Kuala_Lumpur' => 'Asia/Kuala Lumpur (+08:00)',
			  'Asia/Kuching' => 'Asia/Kuching (+08:00)',
			  'Asia/Kuwait' => 'Asia/Kuwait (+03:00)',
			  'Asia/Macau' => 'Asia/Macau (+08:00)',
			  'Asia/Magadan' => 'Asia/Magadan (+11:00)',
			  'Asia/Makassar' => 'Asia/Makassar (+08:00)',
			  'Asia/Manila' => 'Asia/Manila (+08:00)',
			  'Asia/Muscat' => 'Asia/Muscat (+04:00)',
			  'Asia/Nicosia' => 'Asia/Nicosia (+02:00)',
			  'Asia/Novokuznetsk' => 'Asia/Novokuznetsk (+07:00)',
			  'Asia/Novosibirsk' => 'Asia/Novosibirsk (+07:00)',
			  'Asia/Omsk' => 'Asia/Omsk (+06:00)',
			  'Asia/Oral' => 'Asia/Oral (+05:00)',
			  'Asia/Phnom_Penh' => 'Asia/Phnom Penh (+07:00)',
			  'Asia/Pontianak' => 'Asia/Pontianak (+07:00)',
			  'Asia/Pyongyang' => 'Asia/Pyongyang (+09:00)',
			  'Asia/Qatar' => 'Asia/Qatar (+03:00)',
			  'Asia/Qostanay' => 'Asia/Qostanay (+06:00)',
			  'Asia/Qyzylorda' => 'Asia/Qyzylorda (+05:00)',
			  'Asia/Riyadh' => 'Asia/Riyadh (+03:00)',
			  'Asia/Sakhalin' => 'Asia/Sakhalin (+11:00)',
			  'Asia/Samarkand' => 'Asia/Samarkand (+05:00)',
			  'Asia/Seoul' => 'Asia/Seoul (+09:00)',
			  'Asia/Shanghai' => 'Asia/Shanghai (+08:00)',
			  'Asia/Singapore' => 'Asia/Singapore (+08:00)',
			  'Asia/Srednekolymsk' => 'Asia/Srednekolymsk (+11:00)',
			  'Asia/Taipei' => 'Asia/Taipei (+08:00)',
			  'Asia/Tashkent' => 'Asia/Tashkent (+05:00)',
			  'Asia/Tbilisi' => 'Asia/Tbilisi (+04:00)',
			  'Asia/Tehran' => 'Asia/Tehran (+03:30)',
			  'Asia/Thimphu' => 'Asia/Thimphu (+06:00)',
			  'Asia/Tokyo' => 'Asia/Tokyo (+09:00)',
			  'Asia/Tomsk' => 'Asia/Tomsk (+07:00)',
			  'Asia/Ulaanbaatar' => 'Asia/Ulaanbaatar (+08:00)',
			  'Asia/Urumqi' => 'Asia/Urumqi (+06:00)',
			  'Asia/Ust-Nera' => 'Asia/Ust-Nera (+10:00)',
			  'Asia/Vientiane' => 'Asia/Vientiane (+07:00)',
			  'Asia/Vladivostok' => 'Asia/Vladivostok (+10:00)',
			  'Asia/Yakutsk' => 'Asia/Yakutsk (+09:00)',
			  'Asia/Yangon' => 'Asia/Yangon (+06:30)',
			  'Asia/Yekaterinburg' => 'Asia/Yekaterinburg (+05:00)',
			  'Asia/Yerevan' => 'Asia/Yerevan (+04:00)',
			  'Atlantic/Azores' => 'Atlantic/Azores (-01:00)',
			  'Atlantic/Bermuda' => 'Atlantic/Bermuda (-04:00)',
			  'Atlantic/Canary' => 'Atlantic/Canary (+00:00)',
			  'Atlantic/Cape_Verde' => 'Atlantic/Cape Verde (-01:00)',
			  'Atlantic/Faroe' => 'Atlantic/Faroe (+00:00)',
			  'Atlantic/Madeira' => 'Atlantic/Madeira (+00:00)',
			  'Atlantic/Reykjavik' => 'Atlantic/Reykjavik (+00:00)',
			  'Atlantic/South_Georgia' => 'Atlantic/South Georgia (-02:00)',
			  'Atlantic/St_Helena' => 'Atlantic/St Helena (+00:00)',
			  'Atlantic/Stanley' => 'Atlantic/Stanley (-03:00)',
			  'Australia/Adelaide' => 'Australia/Adelaide (+10:30)',
			  'Australia/Brisbane' => 'Australia/Brisbane (+10:00)',
			  'Australia/Broken_Hill' => 'Australia/Broken Hill (+10:30)',
			  'Australia/Currie' => 'Australia/Currie (+11:00)',
			  'Australia/Darwin' => 'Australia/Darwin (+09:30)',
			  'Australia/Eucla' => 'Australia/Eucla (+08:45)',
			  'Australia/Hobart' => 'Australia/Hobart (+11:00)',
			  'Australia/Lindeman' => 'Australia/Lindeman (+10:00)',
			  'Australia/Lord_Howe' => 'Australia/Lord Howe (+11:00)',
			  'Australia/Melbourne' => 'Australia/Melbourne (+11:00)',
			  'Australia/Perth' => 'Australia/Perth (+08:00)',
			  'Australia/Sydney' => 'Australia/Sydney (+11:00)',
			  'Europe/Amsterdam' => 'Europe/Amsterdam (+01:00)',
			  'Europe/Andorra' => 'Europe/Andorra (+01:00)',
			  'Europe/Astrakhan' => 'Europe/Astrakhan (+04:00)',
			  'Europe/Athens' => 'Europe/Athens (+02:00)',
			  'Europe/Belgrade' => 'Europe/Belgrade (+01:00)',
			  'Europe/Berlin' => 'Europe/Berlin (+01:00)',
			  'Europe/Bratislava' => 'Europe/Bratislava (+01:00)',
			  'Europe/Brussels' => 'Europe/Brussels (+01:00)',
			  'Europe/Bucharest' => 'Europe/Bucharest (+02:00)',
			  'Europe/Budapest' => 'Europe/Budapest (+01:00)',
			  'Europe/Busingen' => 'Europe/Busingen (+01:00)',
			  'Europe/Chisinau' => 'Europe/Chisinau (+02:00)',
			  'Europe/Copenhagen' => 'Europe/Copenhagen (+01:00)',
			  'Europe/Dublin' => 'Europe/Dublin (+00:00)',
			  'Europe/Gibraltar' => 'Europe/Gibraltar (+01:00)',
			  'Europe/Guernsey' => 'Europe/Guernsey (+00:00)',
			  'Europe/Helsinki' => 'Europe/Helsinki (+02:00)',
			  'Europe/Isle_of_Man' => 'Europe/Isle of Man (+00:00)',
			  'Europe/Istanbul' => 'Europe/Istanbul (+03:00)',
			  'Europe/Jersey' => 'Europe/Jersey (+00:00)',
			  'Europe/Kaliningrad' => 'Europe/Kaliningrad (+02:00)',
			  'Europe/Kiev' => 'Europe/Kiev (+02:00)',
			  'Europe/Kirov' => 'Europe/Kirov (+03:00)',
			  'Europe/Lisbon' => 'Europe/Lisbon (+00:00)',
			  'Europe/Ljubljana' => 'Europe/Ljubljana (+01:00)',
			  'Europe/London' => 'Europe/London (+00:00)',
			  'Europe/Luxembourg' => 'Europe/Luxembourg (+01:00)',
			  'Europe/Madrid' => 'Europe/Madrid (+01:00)',
			  'Europe/Malta' => 'Europe/Malta (+01:00)',
			  'Europe/Mariehamn' => 'Europe/Mariehamn (+02:00)',
			  'Europe/Minsk' => 'Europe/Minsk (+03:00)',
			  'Europe/Monaco' => 'Europe/Monaco (+01:00)',
			  'Europe/Moscow' => 'Europe/Moscow (+03:00)',
			  'Europe/Oslo' => 'Europe/Oslo (+01:00)',
			  'Europe/Paris' => 'Europe/Paris (+01:00)',
			  'Europe/Podgorica' => 'Europe/Podgorica (+01:00)',
			  'Europe/Prague' => 'Europe/Prague (+01:00)',
			  'Europe/Riga' => 'Europe/Riga (+02:00)',
			  'Europe/Rome' => 'Europe/Rome (+01:00)',
			  'Europe/Samara' => 'Europe/Samara (+04:00)',
			  'Europe/San_Marino' => 'Europe/San Marino (+01:00)',
			  'Europe/Sarajevo' => 'Europe/Sarajevo (+01:00)',
			  'Europe/Saratov' => 'Europe/Saratov (+04:00)',
			  'Europe/Simferopol' => 'Europe/Simferopol (+03:00)',
			  'Europe/Skopje' => 'Europe/Skopje (+01:00)',
			  'Europe/Sofia' => 'Europe/Sofia (+02:00)',
			  'Europe/Stockholm' => 'Europe/Stockholm (+01:00)',
			  'Europe/Tallinn' => 'Europe/Tallinn (+02:00)',
			  'Europe/Tirane' => 'Europe/Tirane (+01:00)',
			  'Europe/Ulyanovsk' => 'Europe/Ulyanovsk (+04:00)',
			  'Europe/Uzhgorod' => 'Europe/Uzhgorod (+02:00)',
			  'Europe/Vaduz' => 'Europe/Vaduz (+01:00)',
			  'Europe/Vatican' => 'Europe/Vatican (+01:00)',
			  'Europe/Vienna' => 'Europe/Vienna (+01:00)',
			  'Europe/Vilnius' => 'Europe/Vilnius (+02:00)',
			  'Europe/Volgograd' => 'Europe/Volgograd (+04:00)',
			  'Europe/Warsaw' => 'Europe/Warsaw (+01:00)',
			  'Europe/Zagreb' => 'Europe/Zagreb (+01:00)',
			  'Europe/Zaporozhye' => 'Europe/Zaporozhye (+02:00)',
			  'Europe/Zurich' => 'Europe/Zurich (+01:00)',
			  'Indian/Antananarivo' => 'Indian/Antananarivo (+03:00)',
			  'Indian/Chagos' => 'Indian/Chagos (+06:00)',
			  'Indian/Christmas' => 'Indian/Christmas (+07:00)',
			  'Indian/Cocos' => 'Indian/Cocos (+06:30)',
			  'Indian/Comoro' => 'Indian/Comoro (+03:00)',
			  'Indian/Kerguelen' => 'Indian/Kerguelen (+05:00)',
			  'Indian/Mahe' => 'Indian/Mahe (+04:00)',
			  'Indian/Maldives' => 'Indian/Maldives (+05:00)',
			  'Indian/Mauritius' => 'Indian/Mauritius (+04:00)',
			  'Indian/Mayotte' => 'Indian/Mayotte (+03:00)',
			  'Indian/Reunion' => 'Indian/Reunion (+04:00)',
			  'Pacific/Apia' => 'Pacific/Apia (+14:00)',
			  'Pacific/Auckland' => 'Pacific/Auckland (+13:00)',
			  'Pacific/Bougainville' => 'Pacific/Bougainville (+11:00)',
			  'Pacific/Chatham' => 'Pacific/Chatham (+13:45)',
			  'Pacific/Chuuk' => 'Pacific/Chuuk (+10:00)',
			  'Pacific/Easter' => 'Pacific/Easter (-05:00)',
			  'Pacific/Efate' => 'Pacific/Efate (+11:00)',
			  'Pacific/Enderbury' => 'Pacific/Enderbury (+13:00)',
			  'Pacific/Fakaofo' => 'Pacific/Fakaofo (+13:00)',
			  'Pacific/Fiji' => 'Pacific/Fiji (+12:00)',
			  'Pacific/Funafuti' => 'Pacific/Funafuti (+12:00)',
			  'Pacific/Galapagos' => 'Pacific/Galapagos (-06:00)',
			  'Pacific/Gambier' => 'Pacific/Gambier (-09:00)',
			  'Pacific/Guadalcanal' => 'Pacific/Guadalcanal (+11:00)',
			  'Pacific/Guam' => 'Pacific/Guam (+10:00)',
			  'Pacific/Honolulu' => 'Pacific/Honolulu (-10:00)',
			  'Pacific/Kiritimati' => 'Pacific/Kiritimati (+14:00)',
			  'Pacific/Kosrae' => 'Pacific/Kosrae (+11:00)',
			  'Pacific/Kwajalein' => 'Pacific/Kwajalein (+12:00)',
			  'Pacific/Majuro' => 'Pacific/Majuro (+12:00)',
			  'Pacific/Marquesas' => 'Pacific/Marquesas (-09:30)',
			  'Pacific/Midway' => 'Pacific/Midway (-11:00)',
			  'Pacific/Nauru' => 'Pacific/Nauru (+12:00)',
			  'Pacific/Niue' => 'Pacific/Niue (-11:00)',
			  'Pacific/Norfolk' => 'Pacific/Norfolk (+12:00)',
			  'Pacific/Noumea' => 'Pacific/Noumea (+11:00)',
			  'Pacific/Pago_Pago' => 'Pacific/Pago Pago (-11:00)',
			  'Pacific/Palau' => 'Pacific/Palau (+09:00)',
			  'Pacific/Pitcairn' => 'Pacific/Pitcairn (-08:00)',
			  'Pacific/Pohnpei' => 'Pacific/Pohnpei (+11:00)',
			  'Pacific/Port_Moresby' => 'Pacific/Port Moresby (+10:00)',
			  'Pacific/Rarotonga' => 'Pacific/Rarotonga (-10:00)',
			  'Pacific/Saipan' => 'Pacific/Saipan (+10:00)',
			  'Pacific/Tahiti' => 'Pacific/Tahiti (-10:00)',
			  'Pacific/Tarawa' => 'Pacific/Tarawa (+12:00)',
			  'Pacific/Tongatapu' => 'Pacific/Tongatapu (+13:00)',
			  'Pacific/Wake' => 'Pacific/Wake (+12:00)',
			  'Pacific/Wallis' => 'Pacific/Wallis (+12:00)',
			  'UTC' => 'UTC (+00:00)',
			);


		}

		/**
		 * Will vary from system to system.
		 */
		/*function testSystemTimeZones()
		{
			$expected = $this->getExpectedZones();

		//	$this->echoMem();
			$zones = systemTimeZones();

		//	$this->echoMem();

			$zoneKeys = array_keys($zones);
			$expectedKeys = array_keys($expected);


			$this->assertSame($expectedKeys, $zoneKeys);
		}*/


		private function echoMem()
		{

			$mem_usage = memory_get_usage();

			static $lastUsage;

			$calcUsage = $mem_usage - $lastUsage;

			/* Peak memory usage */
			//   $mem_peak = memory_get_peak_usage();
			if(!empty($lastUsage))
			{
				echo "\nThe code used " . round($calcUsage / 1024) . "KB of memory\n";
			}

			if(!isset($lastUsage))
			{
				$lastUsage = $mem_usage;
			}
			//   echo 'Peak usage: ' . round($mem_peak / 1024) . 'KB</strong> of memory.'."\n\n";
		}


	}
