<?php

// Generated e107 Plugin Admin Area 

require_once('../../class2.php');
if (!getperms('P'))
{
	e107::redirect('admin');
	exit;
}

e107::lan('social',true, true);
e107::lan('social',false, true);

// test legacy upgrade interface.
/*
$legacy = array(
	'FakeProviderNeverExisted' =>
		array(
			'enabled' => '1',
		),
	'AOL' =>
		array(
			'enabled' => '1',
		),
	'Facebook' =>
		array(
			'keys' =>
				array(
					'id' => 'a',
					'secret' => 'b',
				),
			'scope' => 'c',
			'enabled' => '1',
		),
	'Foursquare' =>
		array(
			'keys' =>
				array(
					'id' => 'a',
					'secret' => 'b',
				),
			'enabled' => '1',
		),
	'Github' =>
		array(
			'keys' =>
				array(
					'id' => 'a',
					'secret' => 'b',
				),
			'scope' => 'c',
			'enabled' => '1',
		),
	'Google' =>
		array(
			'keys' =>
				array(
					'id' => 'a',
					'secret' => 'b',
				),
			'scope' => 'c',
			'enabled' => '1',
		),
	'LinkedIn' =>
		array(
			'keys' =>
				array(
					'id' => 'a',
					'secret' => 'b',
				),
			'enabled' => '1',
		),
	'Live' =>
		array(
			'keys' =>
				array(
					'id' => 'a',
					'secret' => 'b',
				),
			'enabled' => '1',
		),
	'OpenID' =>
		array(
			'enabled' => '1',
		),
	'Steam' =>
		array(
			'keys' =>
				array(
					'key' => 'a',
				),
			'enabled' => '1',
		),
	'Twitter' =>
		array(
			'keys' =>
				array(
					'key' => 'a',
					'secret' => 'b',
				),
			'enabled' => '1',
		),
	'Yahoo' =>
		array(
			'keys' =>
				array(
					'id' => 'a',
					'secret' => 'b',
				),
			'enabled' => '1',
		),
);

e107::getConfig()->setPref('social_login', $legacy);
*/



class social_adminarea extends e_admin_dispatcher
{

	protected $modes = array(

		'main'	=> array(
			'controller' 	=> 'social_ui',
			'path' 			=> null,
			'ui' 			=> 'social_form_ui',
			'uipath' 		=> null
		),


	);


	protected $adminMenu = array(

	//	'main/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
		// 'main/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),
		'main/pages'        => array('caption'=>LAN_SOCIAL_ADMIN_01, 'perm'=>'P'),
		'main/configure'	=> array('caption'=> LAN_SOCIAL_ADMIN_02, 'perm' => 'P'),
		'main/add'		    => array('caption'=> LAN_SOCIAL_ADMIN_44, 'perm' => 'P'),
		'main/unsupported'	=> array('caption'=> LAN_SOCIAL_ADMIN_47, 'perm' => 'P'),

		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'P'),


	);

	protected $adminMenuAliases = array(
		'main/modify'	=> 'main/add'
	);

	protected $menuTitle = LAN_PLUGIN_SOCIAL_NAME;

	public function init()
	{
		$slcm = new social_login_config(e107::getConfig());
		$supported_providers = $slcm->getSupportedProviders();
		$configured_providers = $slcm->getConfiguredProviders();
		$unsupported_providers = array_diff($configured_providers, $supported_providers);

		if(empty($unsupported_providers) && !deftrue('e_DEBUG'))
		{
			unset($this->adminMenu['main/unsupported']);
		}
		else
		{
			$this->adminMenu['main/unsupported']['badge'] = array('value' => count($unsupported_providers), 'type'=>'warning');
		}

	}

}



require_once("includes/social_login_config.php");

class social_ui extends e_admin_ui
{

		protected $pluginTitle		= LAN_PLUGIN_SOCIAL_NAME;
		protected $pluginName		= 'social';
	//	protected $eventName		= 'social-social'; // remove comment to enable event triggers in admin.
	//	protected $table			= 'social';
		protected $pid				= 'social_id';
		protected $perPage			= 10;
		protected $batchDelete		= true;
	//	protected $batchCopy		= true;		
	//	protected $sortField		= 'somefield_order';
	//	protected $orderStep		= 10;
	//	protected $tabs				= array('Tabl 1','Tab 2'); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable. 

	//	protected $listQry      	= "SELECT * FROM `#tableName` WHERE field != '' "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.

		protected $listOrder		= '';

		protected $fields           = array(	);

		protected $fieldpref        = array();


		protected $preftabs        = array(LAN_LOGIN, LAN_SOCIAL_ADMIN_14, LAN_SOCIAL_ADMIN_15, LAN_SOCIAL_ADMIN_16, LAN_SOCIAL_ADMIN_17, LAN_SOCIAL_ADMIN_37);

		protected $prefs = array(


			'facebook_comments_limit'		=> array('title'=> LAN_SOCIAL_ADMIN_18, 'type'=>'number', 'tab'=>2, 'data' => 'int','help'=>LAN_SOCIAL_ADMIN_29),
			'facebook_comments_theme'		=> array('title'=> LAN_SOCIAL_ADMIN_19, 'type'=>'dropdown', 'tab'=>2, 'writeParms'=>array('optArray'=>array('light'=>LAN_SOCIAL_ADMIN_35,'dark'=>LAN_SOCIAL_ADMIN_36)), 'data' => 'str','help'=>''),
			'facebook_comments_loadingtext'		=> array('title'=> LAN_SOCIAL_ADMIN_21, 'type'=>'text', 'tab'=>2, 'data' => 'str', 'writeParms'=>array('placeholder'=>LAN_SOCIAL_ADMIN_30), 'help'=>''),

			'facebook_like_menu_theme'	=> array('title'=> LAN_SOCIAL_ADMIN_19, 'type'=>'dropdown', 'tab'=>3, 'writeParms'=>array('optArray'=>array('light'=>LAN_SOCIAL_ADMIN_35,'dark'=>LAN_SOCIAL_ADMIN_36)), 'data' => 'str'),
			'facebook_like_menu_action'	=> array('title'=> LAN_SOCIAL_ADMIN_20, 'type'=>'dropdown', 'tab'=>3, 'writeParms'=>array('optArray'=>array('like'=>'Like','recommend'=>LAN_SOCIAL_ADMIN_32)), 'data' => 'str'),
			'facebook_like_menu_width'	=> array('title'=> LAN_SOCIAL_ADMIN_22, 'type'=>'number', 'tab'=>3, 'data' => 'int','help'=>LAN_SOCIAL_ADMIN_31),
		//	'facebook_like_menu_ref'	=> array('title'=> "Referrer", 'type'=>'text', 'tab'=>2, 'data' => 'str', 'writeParms'=>'size=xxlarge', 'help'=>'Leave blank to use Site Url'),



			'twitter_menu_theme'	    => array('title'=> LAN_SOCIAL_ADMIN_19, 'type'=>'dropdown', 'tab'=>4, 'writeParms'=>array('optArray'=>array('light'=>LAN_SOCIAL_ADMIN_35,'dark'=>LAN_SOCIAL_ADMIN_36)), 'data' => 'str'),
			'twitter_menu_height'		=> array('title'=> LAN_SOCIAL_ADMIN_23, 'type'=>'number', 'tab'=>4, 'data' => 'int','help'=>LAN_SOCIAL_ADMIN_33),
			'twitter_menu_limit'		=> array('title'=> LAN_SOCIAL_ADMIN_18, 'type'=>'number', 'tab'=>4, 'data' => 'int','help'=>LAN_SOCIAL_ADMIN_34),


			'sharing_mode'              => array('title'=> LAN_SOCIAL_ADMIN_24, 'type'=>'dropdown', 'tab'=>1, 'writeParms'=>array('optArray'=>array('normal'=>LAN_SOCIAL_ADMIN_25,'dropdown'=>LAN_SOCIAL_ADMIN_26,'off'=>LAN_SOCIAL_ADMIN_27)), 'data' => 'str','help'=>''),
			'sharing_hashtags'		    => array('title'=> 'Hashtags', 'type'=>'tags', 'tab'=>1, 'data' => 'str','help'=>LAN_SOCIAL_ADMIN_28),
			'sharing_providers'         => array('title'=> LAN_SOCIAL_ADMIN_39, 'type'=>'checkboxes', 'tab'=>1, 'writeParms'=>array(), 'data' => 'str','help'=>''),

			'xup_login_update_username'  => array('title'=> LAN_SOCIAL_ADMIN_40, 'type'=>'bool', 'tab'=>0, 'writeParms'=>array(), 'data' => 'str','help'=>''),
			'xup_login_update_avatar'   => array('title'=> LAN_SOCIAL_ADMIN_41, 'type'=>'bool', 'tab'=>0, 'writeParms'=>array(), 'data' => 'str','help'=>''),

			'og_image'	                => array('title'=> LAN_SOCIAL_ADMIN_42, 'type'=>'image', 'tab'=>5, 'data' => 'str','help'=>'og:image'),


			);

		protected $social_logins = array();
		/**
		 * @var social_login_config
		 */
		protected $social_login_config_manager;

		protected $social_external = array();

	    const TEST_URL = SITEURL."?route=system/xup/test";

	public function init()
		{
			$this->social_login_config_manager = new social_login_config(e107::getConfig());

			if(!empty($_POST['save_social_logins']) )
			{
				$cfg = e107::getConfig();

				foreach ($_POST['social_login'] as $provider_name => $raw_updated_social_login)
				{
					$this->social_login_config_manager->setProviderConfig($provider_name, $raw_updated_social_login);
				}

				if(isset($_POST['social_login_active']))
				{
					$social_login_flags =
						!!$_POST['social_login_active'] << social_login_config::ENABLE_BIT_GLOBAL |
						!!$_POST['social_login_test_page'] << social_login_config::ENABLE_BIT_TEST_PAGE;
					$cfg->setPref(social_login_config::SOCIAL_LOGIN_FLAGS, $social_login_flags);
				}

				$cfg->save(true, true, true);

			}

			if(!empty($_POST['save_social_pages']) && isset($_POST['xurl']))
			{
				$cfg = e107::getConfig();
				$cfg->setPref('xurl', $_POST['xurl']);
				$cfg->save(true, true, true);
			}

			$tp = e107::getParser();

			require_once(e_PLUGIN."social/e_shortcode.php");
			$obj = new social_shortcodes;
			$providers = $obj->getProviders();
			foreach($providers as $k=>$v)
			{
				$this->prefs['sharing_providers']['writeParms']['optArray'][$k] = $k;
			}
		}


		// ------- Customize Create --------

		public function beforeCreate($new_data, $old_data)
		{
			return $new_data;
		}

		public function afterCreate($new_data, $old_data, $id)
		{
			// do something
		}

		public function onCreateError($new_data, $old_data)
		{
			// do something
		}


		// ------- Customize Update --------

		public function beforeUpdate($new_data, $old_data, $id)
		{
			return $new_data;
		}

		public function afterUpdate($new_data, $old_data, $id)
		{
			// do something
		}

		public function onUpdateError($new_data, $old_data, $id)
		{
			// do something
		}

		function renderHelp()
		{

			$action = $this->getAction();

			switch($action)
			{

				case "configure":
					$notice = LAN_SOCIAL_ADMIN_45;
					break;

				case "unsupported":
					$notice = LAN_SOCIAL_ADMIN_48;
				break;

				case "prefs":
					return null; // todo?
				break;

				default:
				case "add":
					$notice = LAN_SOCIAL_ADMIN_46;
					break;

			}

			if($action == 'configure' || $action == 'add')
			{
				$notice .= "<br /><br />".LAN_SOCIAL_ADMIN_08." <br /><br /><a href='".self::TEST_URL."' rel='external'>".self::TEST_URL."</a>";
				$callBack = SITEURL;
				$notice .= "<br /><br />".LAN_SOCIAL_ADMIN_09."</br ><a href='".$callBack."'>".$callBack."</a>";
			}

			$tp = e107::getParser();

			return array("caption"=>LAN_HELP,'text'=> $tp->toHTML($notice,true));

		}

		public function unsupportedPage()
		{
			$slcm = $this->social_login_config_manager;
			$supported_providers = $slcm->getSupportedProviders();
			$configured_providers = $slcm->getConfiguredProviders();
			$unsupported_providers = array_diff($configured_providers, $supported_providers);
		//	$configured_providers = array_diff($configured_providers, $unsupported_providers);

			return $this->generateSocialLoginSection($unsupported_providers, true	);


		}


		public function pagesPage()
		{
			$frm = $this->getUI();
			$pref = e107::pref('core');

			$text2 = "
					<table class='table table-bordered'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
						";

			//XXX XURL Definitions.
			$xurls = array(
				'facebook'		=> 	array('label'=>"Facebook", "placeholder"=>"eg. https://www.facebook.com/e107CMS"),
				'twitter'		=>	array('label'=>"Twitter",	"placeholder"=>"eg. https://twitter.com/e107"),
				'youtube'		=>	array('label'=>"Youtube",	"placeholder"=>"eg.https://youtube.com/e107Inc"),
				'linkedin'		=>	array('label'=>"LinkedIn",	"placeholder"=>"eg. http://www.linkedin.com/groups?home=&gid=1782682"),
				'github'		=>	array('label'=>"GitHub",	"placeholder"=>"eg. https://github.com/e107inc"),
				'flickr'		=>	array('label'=>"Flickr",	"placeholder"=>""),
				'instagram'		=>	array('label'=>"Instagram",	"placeholder"=>""),
				'pinterest'		=>	array('label'=>"Pinterest",	"placeholder"=>""),
				'steam'			=>	array('label'=>"Steam",		"placeholder"=>"eg. http://steamcommunity.com"),
				'vimeo'			=>	array('label'=>"Vimeo",		"placeholder"=>""),
				'twitch'		=> 	array('label'=>"Twitch", 	"placeholder"=>""),
				'vk'			=> 	array('label'=>"VK (Vkontakte)", "placeholder"=>""),
			);

			foreach($xurls as $k=>$var)
			{
				$keypref = "xurl[".$k."]";
				$text_label = "xurl-".$k."";
				$def = "XURL_". strtoupper($k);

				$opts = array('size'=>'xxlarge','placeholder'=> $var['placeholder']);

				$text2 .= "
					<tr>
						<td><label for='".$text_label."'>".LAN_SOCIAL_ADMIN_11." ".$var['label']." ".LAN_SOCIAL_ADMIN_12."</label></td>
						<td>
							".$frm->text($keypref, $pref['xurl'][$k], false, $opts)."
							<div class='field-help'>".LAN_SOCIAL_ADMIN_13." ".$var['label']." ".LAN_SOCIAL_ADMIN_12." (".$def.")</div>
						</td>
					</tr>
				";
			}




			$text2 .= "
				</tbody>
			</table>

";


			$ret =  $frm->open('social','post',null, 'class=form-horizontal');
			$ret .= $text2;

			$ret .= "<div class='buttons-bar center'>

			".$frm->button('save_social_pages',1,'submit',LAN_SAVE)."

				</div>";

			$ret .= $frm->close();

			return $ret;



		}



		public function modifyPage()
		{

			$frm= $this->getUI();
			$var = $this->getId();
			$this->addTitle($var);

			$text = $frm->open('add-social', 'post', e_SELF."?mode=main&action=add");
			$text .= $this->generateSocialLoginForm($var);

			$text .= "<div class='buttons-bar center'>".$frm->button('save_social_logins',1,'submit',LAN_ADD)."</div>";
			$text .= $frm->close();

			return $text;

		}

		public function addPage()
		{
			$slcm = $this->social_login_config_manager;
			$supported_providers = $slcm->getSupportedProviders();
			$configured_providers = $slcm->getConfiguredProviders();
			$unconfigured_providers = array_diff($supported_providers, $configured_providers);

		//	$text = "<table class='table table-striped table-bordered'>";
			$text = '';
			foreach($unconfigured_providers as $value)
			{
				$link = e_SELF."?mode=main&action=modify&id=".$value;
				$text .= "<a class='col-md-3 btn btn-default' href='".$link."'>".$value."</a>";
			}

			return $text;

		}



		// optional - a custom page.
		public function configurePage()
		{
			$ns = e107::getRender();
			$frm = e107::getForm();
			$pref = e107::pref('core');
			$slcm = $this->social_login_config_manager;

			require_once("social_setup.php");
			$social_setup = new social_setup();
			if ($social_setup->upgrade_required())
			{
				$srch = array('[',']');
				$repl = array("<a href=\"" . e_ADMIN_ABS . "e107_update.php\">", "</a>");

				e107::getMessage()->addInfo(str_replace($srch,$repl, LAN_SOCIAL_ADMIN_43));
				return null;
			}

			$text = $this->generateAdminFormJs();

			$text .= "<table class='table adminform table-bordered'>
				<colgroup>
					<col class='col-label' style='width:15%' />
					<col class='col-control' />
				</colgroup>
						<tbody>
					<tr>
						<td><label for='social-login-active-1'>".LAN_SOCIAL_ADMIN_51."</label>
						</td>
						<td>
							".$frm->radio_switch('social_login_active', $slcm->isFlagActive($slcm::ENABLE_BIT_GLOBAL))."
								<div class='smalltext field-help'>".LAN_SOCIAL_ADMIN_07." </div>
						</td>
					</tr>
					<tr>
						<td>
						  <label for='social-login-test-mode-1'>
						    <a href='".self::TEST_URL."' target='_blank'>".LAN_SOCIAL_ADMIN_49."</a>
						  </label>
						</td>
						<td>
							".$frm->radio_switch('social_login_test_page', $slcm->isFlagActive($slcm::ENABLE_BIT_TEST_PAGE))."
								<div class='smalltext field-help'>".LAN_SOCIAL_ADMIN_50." </div>
						</td>
					</tr>";

			$supported_providers = $slcm->getSupportedProviders();
			$configured_providers = $slcm->getConfiguredProviders();
		//	$unconfigured_providers = array_diff($supported_providers, $configured_providers);
			$unsupported_providers = array_diff($configured_providers, $supported_providers);
			$configured_providers = array_diff($configured_providers, $unsupported_providers);


			$text .= $this->generateSocialLoginSection($configured_providers);

			$text .= "
				</tbody></table>
			";


			$ret =  $frm->open('social','post',null, 'class=form-horizontal').$text;

			$ret .= "<div class='buttons-bar center'>

			".$frm->button('save_social_logins',1,'submit',LAN_SAVE)."

				</div>";

			$ret .= $frm->close();

			return $ret;
		}

	/**
	 * @param $text
	 * @param array $provider_names
	 * @return string
	 */
	private function generateSocialLoginSection($provider_names, $readonly=false)
	{
		if(empty($provider_names))
		{
			return "";
		}

			$text  = "
						<table class='table table-bordered table-striped'>
							<colgroup>
								<col style='width:10%' />
								<col style='width:5%' />
								<col class='col-control' />
								<col style='width:5%' />
							</colgroup>
							<thead>
								<tr>
									<th>" . LAN_SOCIAL_ADMIN_04 . "</th>
									<th>" . LAN_TYPE . "</th>
									<th>" . LAN_CONFIGURE . "</th>
									<th class='center'>" . LAN_SOCIAL_ADMIN_03 . "</th>
								</tr>
							</thead>
							";

		foreach ($provider_names as $provider_name)
		{
			$text .= $this->generateSocialLoginRow($provider_name, $readonly);
		}

		$text .= "</table>";


		return $text;
	}

	private function getLabel($fieldSlash)
	{
			$labels = array(
			'keys/key'      => LAN_SOCIAL_ADMIN_05,
			'keys/id'       => LAN_SOCIAL_ADMIN_05,
			'keys/secret'   => LAN_SOCIAL_ADMIN_06,
			'scope'         => LAN_SOCIAL_ADMIN_38

		);

		return varset($labels[$fieldSlash], ucfirst($fieldSlash));
	}

		/**
	 * @param $provider_name
	 * @return string Text to append
	 */
	private function generateSocialLoginForm($provider_name)
	{
		$slcm = $this->social_login_config_manager;
		$provider_type = $slcm->getTypeOfProvider($provider_name);
		if (empty($provider_type)) $provider_type = "<em>" . LAN_UNKNOWN . "</em>";

		$normalized_provider_name = $slcm->normalizeProviderName($provider_name);
		list($pretty_provider_name,) = array_pad(explode("-", $normalized_provider_name), 2, "");

		$frm = e107::getForm();
		$textKeys = '';
		$textScope = '';
		$label = varset(self::getApiDocumentationUrlFor($provider_name)) ? "<a class='e-tip' rel='external' title=' " . LAN_SOCIAL_ADMIN_10 . "' href='" . self::getApiDocumentationUrlFor($provider_name) . "'>" . $pretty_provider_name . "</a>" : $pretty_provider_name;
		$radio_label = strtolower($provider_name);

		$text = "<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
						<tbody>";

		$textEnabled = $frm->radio_switch("social_login[$provider_name][enabled]", $slcm->isProviderEnabled($provider_name), '', '', ['class' => 'e-expandit']);
		$text .= $textKeys . $textScope . "<tr><td>".LAN_ACTIVE."</td><td>" . $textEnabled . "</td></tr>";

		$text .= "
					<tr><td>".LAN_NAME."</td><td><label for='social-login-" . $radio_label . "-enabled'>" . $label . "</label></td></tr>
					<tr><td>".LAN_TYPE."</td><td>$provider_type</td></tr>
						";

		$fieldInfo = self::array_slash($slcm->getFieldsOf($provider_name));



		foreach ($fieldInfo as $fieldSlash => $description)
		{
			$field = str_replace("/", "][", $fieldSlash);
			$placeholder = self::getPlaceholderFor($provider_name, $fieldSlash);
			$frm_options = [
				'size' => 'xxlarge',
				'placeholder' => $placeholder,
			];

			$text .= "<tr><td>".$this->getLabel($fieldSlash)."</td><td>";
			$text .= $frm->text("social_login[$provider_name][$field]", $placeholder, 256, $frm_options);
			$text .= "<div class='smalltext field-help'>$description</div>";
			$text .= "</td></tr>";
		}

		$text .= "</table>";


		return $text;
	}

	/**
	 * @param $provider_name
	 * @return string Text to append
	 */
	private function generateSocialLoginRow($provider_name, $readonly = false)
	{
		$slcm = $this->social_login_config_manager;
		$provider_type = $slcm->getTypeOfProvider($provider_name);
		if (empty($provider_type)) $provider_type = "<em>" . LAN_UNKNOWN . "</em>";

		$normalized_provider_name = $slcm->normalizeProviderName($provider_name);
		list($pretty_provider_name,) = array_pad(explode("-", $normalized_provider_name), 2, "");

		$frm = e107::getForm();
		$textKeys = '';
		$textScope = '';
		$label = varset(self::getApiDocumentationUrlFor($provider_name)) ? "<a class='e-tip' rel='external' title=' " . LAN_SOCIAL_ADMIN_10 . "' href='" . self::getApiDocumentationUrlFor($provider_name) . "'>" . $pretty_provider_name . "</a>" : $pretty_provider_name;
		$radio_label = strtolower($provider_name);
		$text = "
					<tr id='social-login-row-" . $radio_label."'>
						<td><label for='social-login-" . $radio_label . "-enabled'>" . $label . "</label></td>
						<td>$provider_type</td>
						";

		$text .= "<td><table class='table table-bordered table-condensed' style='margin:0'>";
		$fieldInfo = self::array_slash($slcm->getFieldsOf($provider_name));
		foreach ($fieldInfo as $fieldSlash => $description)
		{
			$field = str_replace("/", "][", $fieldSlash);
			$frm_options = [
				'size' => 'block-level',
				'placeholder' => self::getPlaceholderFor($provider_name, $fieldSlash),
			];

			if($readonly)
			{
				$frm_options['readonly'] = 1; ;
			}

			$text .= "<tr><td class='col-label'>".$this->getLabel($fieldSlash)."</td>";
			$text .= "<td>".$frm->text("social_login[$provider_name][$field]",	$slcm->getProviderConfig($provider_name, $fieldSlash),	256, $frm_options).
			 "<div class='smalltext field-help'>$description</div>";
			$text .= "</td></tr>";
		}

		$text .= "</table></td>";


		if($readonly)
		{
			$textEnabled = ($slcm->isProviderEnabled($provider_name)) ? ADMIN_TRUE_ICON : ADMIN_FALSE_ICON;
		}
		else
		{
			$enabledOpts = ['class' => 'e-expandit'];
			$textEnabled = $frm->radio_switch("social_login[$provider_name][enabled]", $slcm->isProviderEnabled($provider_name), '', '', $enabledOpts);
		}

		$text .= $textKeys . $textScope . "<td class='center'>" . $textEnabled . "</td>";

		$text .= "
					</tr>
					";

		return $text;
	}

	/**
	 * Based on Illuminate\Support\Arr::dot()
	 * @copyright Copyright (c) Taylor Otwell
	 * @license https://github.com/illuminate/support/blob/master/LICENSE.md MIT License
	 * @param $array
	 * @param string $prepend
	 * @return array
	 */
	private static function array_slash($array, $prepend = '')
	{
		$results = [];

		foreach ($array as $key => $value)
		{
			if (is_array($value) && !empty($value))
			{
				$results = array_merge($results, static::array_slash($value, $prepend . $key . '/'));
			}
			else
			{
				$results[$prepend . $key] = $value;
			}
		}

		return $results;
	}

	private static function getPlaceholderFor($providerName, $fieldSlash)
	{
		switch ($fieldSlash)
		{
			case "scope":
				$propertyName = "scope";
				break;
			case "openid_identifier":
				$propertyName = "openidIdentifier";
				break;
			default:
				$propertyName = "";
		}

		try
		{
			$class = "\Hybridauth\Provider\\$providerName";
			$reflection = new ReflectionClass($class);
			$properties = $reflection->getDefaultProperties();
			return isset($properties[$propertyName]) ? $properties[$propertyName] : null;
		}
		catch (ReflectionException $e)
		{
			return null;
		}
	}

	private static function getApiDocumentationUrlFor($providerName)
	{
		try
		{
			$class = "\Hybridauth\Provider\\$providerName";
			$reflection = new ReflectionClass($class);
			$properties = $reflection->getDefaultProperties();
			return isset($properties['apiDocumentation']) ? $properties['apiDocumentation'] : null;
		}
		catch (ReflectionException $e)
		{
			return null;
		}
	}

	private function generateAdminFormJs()
	{
		return <<<EOD
<script type='text/javascript'>
var e107 = e107 || {'settings': {}, 'behaviors': {}};

let socialLoginSwitches = {
    'social-login-test-page__switch': null,
};

function socialLoginSwitchesHighstate(element) {
    if (element === undefined) return;
    
	let isActive = element.checked;
	
	if (isActive) {
	    for (let key in socialLoginSwitches) {
	        let toggle = $('[name='+key+']');
	        toggle.bootstrapSwitch('disabled', false);
	        if (socialLoginSwitches[key] !== null) toggle.bootstrapSwitch('state', socialLoginSwitches[key]);
	    }
	} else {
	    for (let key in socialLoginSwitches) {
	    	let toggle = $('[name='+key+']');
	        socialLoginSwitches[key] = toggle.bootstrapSwitch('state');
	        toggle.bootstrapSwitch('state', false);
	        toggle.bootstrapSwitch('disabled', true);
	    }
	}   
}

(function ($)
{
    e107.behaviors.manageSocialLoginSwitches = {
    	attach: function (context, settings) {
    	    let globalSwitch = $('[name=social-login-active__switch]');
    	    socialLoginSwitchesHighstate(globalSwitch.get(0));
			globalSwitch.on('switchChange.bootstrapSwitch', function(event) {
			    socialLoginSwitchesHighstate(event.target);
			});
		},
	};
})(jQuery);
</script>
EOD;
	}
}



class social_form_ui extends e_admin_form_ui
{

}


class social_admin_tree_model extends e_tree_model
{

	/**
	 * Load data from theme meta file.
	 * @param bool $force
	 */
	function loadBatch($force=false)
	{
		$themeList  = e107::getTheme()->getList();
		$newArray   = array();
		$parms      = $this->getParams();
		$siteTheme  = e107::getPref('sitetheme');

		if($parms['limitFrom'] == 0 && empty($parms['searchqry'])) // place the sitetheme first.
		{
			$newArray[] = $themeList[$siteTheme];
		}

		foreach($themeList as $k=>$v)
		{

			if(!empty($parms['searchqry']) && stripos($v['description'],$parms['searchqry']) === false && stripos($v['folder'],$parms['searchqry']) === false && stripos($v['name'],$parms['searchqry']) === false)
			{
				continue;
			}

			if($v['path'] == $siteTheme)
			{
				continue;
			}

			$newArray[] = $v;
		}

		if(!empty($parms['limitTo']) && empty($parms['searchqry']))
		{
			$arr = array_slice($newArray, $parms['limitFrom'], $parms['limitTo']);
		}
		else
		{
			$arr = $newArray;
		}


		foreach($arr as $k=>$v)
		{

			$v['social_id'] = $k;

			$v['social_thumbnail'] = !empty($v['thumbnail']) ? '{e_THEME}'.$v['path'].'/'.$v['thumbnail'] : null;
			$tmp = new e_model($v);
			$this->setNode($k,$tmp);

		}

		$this->setTotal(count($newArray));
	}


}




new social_adminarea();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;

?>
