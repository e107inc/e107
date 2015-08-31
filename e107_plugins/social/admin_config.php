<?php

// Generated e107 Plugin Admin Area 

require_once('../../class2.php');
if (!getperms('P')) 
{
	header('location:'.e_BASE.'index.php');
	exit;
}


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
		'main/configure'		=> array('caption'=> LAN_CONFIGURE, 'perm' => 'P'),
		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'P'),	


	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = 'Social';
}




				
class social_ui extends e_admin_ui
{
			
		protected $pluginTitle		= 'Social';
		protected $pluginName		= 'social';
	//	protected $eventName		= 'social-social'; // remove comment to enable event triggers in admin.
	//	protected $table			= 'social';
	//	protected $pid				= 'interview_id';
		protected $perPage			= 10; 
		protected $batchDelete		= true;
	//	protected $batchCopy		= true;		
	//	protected $sortField		= 'somefield_order';
	//	protected $orderStep		= 10;
	//	protected $tabs				= array('Tabl 1','Tab 2'); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable. 
		
	//	protected $listQry      	= "SELECT * FROM `#tableName` WHERE field != '' "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.
	
		protected $listOrder		= '';
	
		protected $fields           = array();
		
		protected $fieldpref        = array();
		

		protected $preftabs        = array("Sharing", 'Facebook Comments',  'Facebook Menu', 'Twitter Menu', );

		protected $prefs = array(


			'facebook_comments_limit'		=> array('title'=> 'Limit', 'type'=>'number', 'tab'=>1, 'data' => 'int','help'=>'Number of comments to display.'),
			'facebook_comments_theme'		=> array('title'=> 'Theme', 'type'=>'dropdown', 'tab'=>1, 'writeParms'=>array('optArray'=>array('light'=>'Light','dark'=>'Dark')), 'data' => 'str','help'=>''),
			'facebook_comments_loadingtext'		=> array('title'=> 'Text while loading', 'type'=>'text', 'tab'=>1, 'data' => 'str', 'writeParms'=>array('placeholder'=>'Loading...'), 'help'=>''),

			'facebook_like_menu_theme'	=> array('title'=> 'Theme', 'type'=>'dropdown', 'tab'=>2, 'writeParms'=>array('optArray'=>array('light'=>'Light','dark'=>'Dark')), 'data' => 'str'),
			'facebook_like_menu_action'	=> array('title'=> 'Action', 'type'=>'dropdown', 'tab'=>2, 'writeParms'=>array('optArray'=>array('like'=>'Like','recommend'=>'Recommend')), 'data' => 'str'),
			'facebook_like_menu_width'	=> array('title'=> 'Width', 'type'=>'number', 'tab'=>2, 'data' => 'int','help'=>'Width in px'),
		//	'facebook_like_menu_ref'	=> array('title'=> "Referrer", 'type'=>'text', 'tab'=>2, 'data' => 'str', 'writeParms'=>'size=xxlarge', 'help'=>'Leave blank to use Site Url'),



			'twitter_menu_theme'	=> array('title'=> 'Theme', 'type'=>'dropdown', 'tab'=>3, 'writeParms'=>array('optArray'=>array('light'=>'Light','dark'=>'Dark')), 'data' => 'str'),
			'twitter_menu_height'		=> array('title'=> 'Height', 'type'=>'number', 'tab'=>3, 'data' => 'int','help'=>'Height in px'),
			'twitter_menu_limit'		=> array('title'=> 'Limit', 'type'=>'number', 'tab'=>3, 'data' => 'int','help'=>'Number of tweets to display.'),


			'sharing_mode'              => array('title'=> 'Display Mode', 'type'=>'dropdown', 'tab'=>0, 'writeParms'=>array('optArray'=>array('normal'=>'Normal','dropdown'=>'Dropdown','off'=>'Disabled')), 'data' => 'str','help'=>''),
			'sharing_hashtags'		    => array('title'=> 'Hashtags', 'type'=>'tags', 'tab'=>0, 'data' => 'str','help'=>'Excluding the # symbol.'),
			'sharing_providers'         => array('title'=> 'Providers', 'type'=>'checkboxes', 'tab'=>0, 'writeParms'=>array(), 'data' => 'str','help'=>''),
		);

		protected $social_logins = array();

		protected $social_external = array();

		public function init()
		{
			if(!empty($_POST['save_social']) )
			{
				$cfg = e107::getConfig();

				$cfg->setPref('social_login', $_POST['social_login']);
				$cfg->setPref('social_login_active', $_POST['social_login_active']);
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
		//	print_a($bla);



// Single/ Social  Login / / copied from hybridAuth config.php so it's easy to add more.
// Used Below.

			$this->social_logins = array (
				// openid providers
				"OpenID" => array (
					"enabled" => true
				),

				"Yahoo" => array (
					"enabled" => true
				),

				"AOL"  => array (
					"enabled" => true
				),

				"Facebook" => array (
					"enabled" => true,
					"keys"    => array ( "id" => "", "secret" => "" ),
					"trustForwarded" => false,
					// A comma-separated list of permissions you want to request from the user. See the Facebook docs for a full list of available permissions: http://developers.facebook.com/docs/reference/api/permissions.
					"scope"   => "",

					// The display context to show the authentication page. Options are: page, popup, iframe, touch and wap. Read the Facebook docs for more details: http://developers.facebook.com/docs/reference/dialogs#display. Default: page
					"display" => ""
				),

				"Foursquare" => array (
					"enabled" => true,
					"keys"    => array ( "id" => "", "secret" => "" )
				),

				"Github" => array (
					"enabled" => true,
					"keys"    => array ( "id" => "", "secret" => "" )
				),

				"Google" => array (
					"enabled" => true,
					"keys"    => array ( "id" => "", "secret" => "" ),
					"scope"   => ""
				),

				"LinkedIn" => array (
					"enabled" => true,
					"keys"    => array ( "key" => "", "secret" => "" )
				),

				// windows live
				"Live" => array (
					"enabled" => true,
					"keys"    => array ( "id" => "", "secret" => "" )
				),

				/*
				"MySpace" => array (
					"enabled" => true,
					"keys"    => array ( "key" => "", "secret" => "" )
				),
				*/

				"Twitter" => array (
					"enabled" => true,
					"keys"    => array ( "key" => "", "secret" => "" )
				),








			);


			$this->social_external = array(
				"Facebook" 		=> "https://developers.facebook.com/apps",
				"Twitter"		=> "https://dev.twitter.com/apps/new",
				"Google"		=> "https://code.google.com/apis/console/",
				"Live"			=> "https://manage.dev.live.com/ApplicationOverview.aspx",
				"LinkedIn"		=> "https://www.linkedin.com/secure/developer",
				"Foursquare"	=> "https://www.foursquare.com/oauth/",
				"Github"		=> "https://github.com/settings/applications/new",
			);


		}

		
		// ------- Customize Create --------
		
		public function beforeCreate($new_data)
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
			$this->testUrl = SITEURL."?route=system/xup/test";

			$notice = "Note: In most cases you will need to obtain an id and secret key from one of the providers. Click the blue links below to configure.
					<br />You may test your configuration with the following URL: <a href='".$this->testUrl."' rel='external'>".$this->testUrl."</a>";

			return array("caption"=>"Help",'text'=> $notice);

		}

		// optional - a custom page.  
		public function configurePage()
		{
			$ns = e107::getRender();
			$frm = e107::getForm();
			$pref = e107::pref('core');




		//	e107::getMessage()->addInfo($notice);


			$text = "<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
						<tbody>
					<tr>
						<td><label for='social-login-active'>Social Signup/Login</label>
						</td>
						<td>
							".$frm->radio_switch('social_login_active', $pref['social_login_active'])."
								<div class='smalltext field-help'>Allows users to signup/login with their social media accounts. When enabled, this option will still allow users to signup/login even if the core user registration system above is disabled. </div>

						</td>
					</tr>


					<tr>
						<td>Application Keys and IDs <br /></td>
						<td>
							<table class='table table-bordered table-striped'>
							<colgroup>
								<col class='col-label' />
								<col class='col-control' />
							</colgroup>

					";

			if(!is_array($pref['social_login']))
			{
				$pref['social_login'] = array();
			}

			foreach($this->social_logins as $prov=>$val)
			{

				$label = varset($this->social_external[$prov]) ? "<a class='e-tip' rel='external' title='Get a key from the provider' href='".$social_external[$prov]."'>".$prov."</a>" : $prov;
				$radio_label = strtolower($prov);
				$text .= "
					<tr>
						<td><label for='social-login-".$radio_label."-enabled'>".$label."</label></td>
						<td>
						";
				foreach($val as $k=>$v)
				{
					switch ($k) {
						case 'enabled':
							$eopt = array('class'=>'e-expandit');
							$text .= $frm->radio_switch('social_login['.$prov.'][enabled]', vartrue($pref['social_login'][$prov]['enabled']),'','',$eopt);
							break;

						case 'keys':
							// $cls = vartrue($pref['single_login'][$prov]['keys'][$tk]) ? "class='e-hideme'" : '';
							$sty = vartrue($pref['social_login'][$prov]['keys'][vartrue($tk)]) ? "" : "e-hideme";
							$text .= "<div class='e-expandit-container {$sty}' id='option-{$prov}' >";
							foreach($v as $tk=>$idk)
							{
								$eopt = array('placeholder'=> $tk, 'size'=>'xxlarge');
								$text .= "<br />".$frm->text('social_login['.$prov.'][keys]['.$tk.']', vartrue($pref['social_login'][$prov]['keys'][$tk]), 100, $eopt);
							}
							$text .= "</div>";

							break;

						case 'scope':
							$text .= $frm->hidden('social_login['.$prov.'][scope]','email');
							break;

						default:

							break;
					}
				}

				$text .= "</td>
					</tr>
					";
			}







			$text .= "</table>
					</td></tr>


				</tbody></table>
			";



			// -------------------------------
			//
			//


			$text2 = "
					<table class='table'>
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
				'google'		=>	array('label'=>"Google+",	"placeholder"=>""),
				'linkedin'		=>	array('label'=>"LinkedIn",	"placeholder"=>"eg. http://www.linkedin.com/groups?home=&gid=1782682"),
				'github'		=>	array('label'=>"Github",	"placeholder"=>"eg. https://github.com/e107inc"),
				'flickr'		=>	array('label'=>"Flickr",	"placeholder"=>""),
				'instagram'		=>	array('label'=>"Instagram",	"placeholder"=>""),
				'pinterest'		=>	array('label'=>"Pinterest",	"placeholder"=>""),
				'vimeo'			=>	array('label'=>"Vimeo",		"placeholder"=>""),
			);

			foreach($xurls as $k=>$var)
			{
				$keypref = "xurl[".$k."]";
				$text_label = "xurl-".$k."";
				$def = "XURL_". strtoupper($k);

				$opts = array('size'=>'xxlarge','placeholder'=> $var['placeholder']);

				$text2 .= "
					<tr>
						<td><label for='".$text_label."'>Your ".$var['label']." page</label></td>
						<td>
							".$frm->text($keypref, $pref['xurl'][$k], false, $opts)."
							<div class='field-help'>Used by some themes to provide a link to your ".$var['label']." page. (".$def.")</div>
						</td>
					</tr>
				";
			}




			$text2 .= "
				</tbody>
			</table>

";
			$tabs = array();
			$tabs[] = array('caption'=>"Apps", 'text'=>$text);
			$tabs[] = array('caption'=>'Pages', 'text'=>$text2);

			$ret =  $frm->open('social','post',null, 'class=form-horizontal').$frm->tabs($tabs);

			$ret .= "<div class='buttons-bar center'>

			".$frm->button('save_social',1,'submit',LAN_SAVE)."

				</div>";

			$ret .= $frm->close();

			return $ret;
		}

			
}
				


class social_form_ui extends e_admin_form_ui
{

}		
		
		
new social_adminarea();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;

?>