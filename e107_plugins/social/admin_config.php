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
	
	protected $menuTitle = LAN_PLUGIN_SOCIAL_NAME;
}




				
class social_ui extends e_admin_ui
{
			
		protected $pluginTitle		= LAN_PLUGIN_SOCIAL_NAME;
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


				"AOL"  => array (
					"enabled" => true
				),

				"Facebook" => array (
					"enabled" => true,
					"keys"    => array ( "id" => "", "secret" => "" ),
					"trustForwarded" => false,
					// A comma-separated list of permissions you want to request from the user. See the Facebook docs for a full list of available permissions: http://developers.facebook.com/docs/reference/api/permissions.
					"scope"   => "email",

					// The display context to show the authentication page. Options are: page, popup, iframe, touch and wap. Read the Facebook docs for more details: http://developers.facebook.com/docs/reference/dialogs#display. Default: page
					"display" => ""
				),

				"Foursquare" => array (
					"enabled" => true,
					"keys"    => array ( "id" => "", "secret" => "" )
				),

				"Github" => array (
					"enabled" => true,
					"keys"    => array ( "id" => "", "secret" => "" ),
					"scope"   => "user:email",
				),

				"Google" => array (
					"enabled" => true,
					"keys"    => array ( "id" => "", "secret" => "" ),
					"scope"   => "email"
				),
/*
				"Instagram" => array (
					"enabled" => true,
					"keys"	 => array ( "id" => "", "secret" => "" )
				),*/

				"LinkedIn" => array (
					"enabled" => true,
					"keys"    => array ( "id" => "", "secret" => "" )
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

				"OpenID" => array (
					"enabled" => true
				),

				"Steam" => array (
					"enabled" => true,
					"keys"	 => array ( "key" => "" )
				),

				"Twitter" => array (
					"enabled" => true,
					"keys"    => array ( "key" => "", "secret" => "" ),
					"includeEmail" => true,
				),


				"Yahoo" => array (
					"enabled" => true,
					"keys"    => array ( "id" => "", "secret" => "" ),
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
				"Steam"			=> "http://steamcommunity.com/dev/apikey",
				"Instagram"     => "http://instagram.com/developer"
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

			$notice = "".LAN_SOCIAL_ADMIN_08." <br /><a href='".$this->testUrl."' rel='external'>".$this->testUrl."</a>";

			$callBack = SITEURL."index.php";
			$notice .= "<br /><br />".LAN_SOCIAL_ADMIN_09."</br ><a href='".$callBack."'>".$callBack."</a>";


			return array("caption"=>LAN_HELP,'text'=> $notice);

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
						<td><label for='social-login-active'>".LAN_SOCIAL_ADMIN_02."</label>
						</td>
						<td>
							".$frm->radio_switch('social_login_active', $pref['social_login_active'])."
								<div class='smalltext field-help'>".LAN_SOCIAL_ADMIN_07." </div>

						</td>
					</tr>



					<tr><td colspan='2'>
							<table class='table table-bordered table-striped'>
							<colgroup>
								<col style='width:10%' />
								<col class='col-control' />
								<col class='col-control' />
								<col class='col-control' />
								<col style='width:20%' />
							</colgroup>
							<thead>
								<tr>
									<th>".LAN_SOCIAL_ADMIN_04."</th>
									<th>".LAN_SOCIAL_ADMIN_05."</th>
									<th>".LAN_SOCIAL_ADMIN_06."</th>
									<th>".LAN_SOCIAL_ADMIN_38."</th>
									<th class='center'>".LAN_SOCIAL_ADMIN_03."</th>
								</tr>
							</thead>

					";

			if(!is_array($pref['social_login']))
			{
				$pref['social_login'] = array();
			}

			foreach($this->social_logins as $prov=>$val)
			{
				$textKeys = '';
				$textScope = '';
				$keyCount= array();
				$label = varset($this->social_external[$prov]) ? "<a class='e-tip' rel='external' title=' ".LAN_SOCIAL_ADMIN_10."' href='".$this->social_external[$prov]."'>".$prov."</a>" : $prov;
				$radio_label = strtolower($prov);
				$text .= "
					<tr>
						<td><label for='social-login-".$radio_label."-enabled'>".$label."</label></td>

						";
				foreach($val as $k=>$v)
				{


					switch ($k) {
						case 'enabled':
							$eopt = array('class'=>'e-expandit');
							$textEnabled = $frm->radio_switch('social_login['.$prov.'][enabled]', vartrue($pref['social_login'][$prov]['enabled']),'','',$eopt);
							break;

						case 'keys':
							// $cls = vartrue($pref['single_login'][$prov]['keys'][$tk]) ? "class='e-hideme'" : '';
							$sty = vartrue($pref['social_login'][$prov]['keys'][vartrue($tk)]) ? "" : "e-hideme";
					//		$text .= "<div class='e-expandit-container {$sty}' id='option-{$prov}' >";
							foreach($v as $tk=>$idk)
							{
								$eopt = array( 'size'=>'block-level');
								$keyCount[] = 1;
								$textKeys .= "<td>".$frm->text('social_login['.$prov.'][keys]['.$tk.']', vartrue($pref['social_login'][$prov]['keys'][$tk]), 100, $eopt)."</td>";
							}
						//	$text .= "</div>";

							break;

						case 'scope':
							$eopt = array( 'size'=>'block-level');
							$keyCount[] = 1;
							$textScope .= "<td>".$frm->text('social_login['.$prov.'][scope]', vartrue($pref['social_login'][$prov]['scope']), 100, $eopt)."</td>";
							break;

						default:

							break;
					}




				}



				if(empty($keyCount))
				{
					$textKeys = "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				}
				elseif(count($keyCount) == 1)
				{
					$textKeys .= "<td>&nbsp;</td><td>&nbsp;</td>";
				}
				elseif(count($keyCount) == 2)
				{
					$textKeys .= "<td>&nbsp;</td>";
				}

				$text .= $textKeys.$textScope."<td class='center'>".$textEnabled."</td>";

				$text .= "
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
				'linkedin'		=>	array('label'=>"LinkedIn",	"placeholder"=>"eg. http://www.linkedin.com/groups?home=&gid=1782682"),
				'github'		=>	array('label'=>"Github",	"placeholder"=>"eg. https://github.com/e107inc"),
				'flickr'		=>	array('label'=>"Flickr",	"placeholder"=>""),
				'instagram'		=>	array('label'=>"Instagram",	"placeholder"=>""),
				'pinterest'		=>	array('label'=>"Pinterest",	"placeholder"=>""),
				'steam'			=>	array('label'=>"Steam",		"placeholder"=>"eg. http://steamcommunity.com"),
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
			$tabs = array();
			$tabs[] = array('caption'=> LAN_SOCIAL_ADMIN_00, 'text'=>$text);
			$tabs[] = array('caption'=> LAN_SOCIAL_ADMIN_01, 'text'=>$text2);

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
