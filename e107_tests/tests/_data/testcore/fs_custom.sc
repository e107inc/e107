/*
* Copyright (C) 2006-2009 Corllete ltd (clabteam.com), Released under Creative Common license - http://creativecommons.org/licenses/by-nc/3.0/
* Download and update at http://www.free-source.net/
* $Id:
*
*/

global $tp,$pref;
$ret = "";
$custom_query = explode('+', $parm);

global $use_imagecode, $sec_img;
$use_imagecode = ($pref['logcode'] && extension_loaded('gd'));
if($use_imagecode) {
include_once(e_HANDLER.'secure_img_handler.php');
	$sec_img = new secure_image;
	$fs_sec_code_img = '
			<div class="H20"><!-- --></div>
			<div class="secure secure-img">
				'.$sec_img->r_image().'
			</div>
			<div class="secure secure-field center">
				<input type="hidden" name="rand_num" value="'.$sec_img->random_number.'" />
				<input class="custom-loginc verify" type="text" name="code_verify" size="15" maxlength="20" />
			</div>
			<div class="clear"><!-- --></div>
	';
}
$err = '';

if (LOGINMESSAGE != '') {
	$err = '
		<div class="login-message">
			<img class="f-left" src="'.THEME_ABS.'images/messagebox_critical.png" alt="Error" />
			<div style="margin-left: 40px;">'.LOGINMESSAGE.'</div>
		</div>
		<div class="clear H10"></div>
	';
}

switch($custom_query[0])
{
		case "login":
		case "login noprofile":
				include_lan(e_PLUGIN."login_menu/languages/".e_LANGUAGE.".php");

				
				if(ADMIN == TRUE)
					{
						$fs_admin = '
								<a class="logincn admin" href="'.e_ADMIN_ABS.'admin.php">'.LOGIN_MENU_L11.'</a>&nbsp;&nbsp;|
						';
					}
				if($custom_query[0] != "login noprofile") 
					{
						$fs_profile = '
								<a class="logincn profile" href="'.SITEURL.'user.php?id.'.USERID.'">'.LOGIN_MENU_L13.'</a>&nbsp;&nbsp;|
						';
					}
						$fs_settings = '
								<a class="logincn usersettings" href="' . SITEURL . 'usersettings.php">'.LOGIN_MENU_L12.'</a>&nbsp;&nbsp;|
						';
						$fs_logout = '
								<a class="logincn logout" href="'.SITEURL.'index.php?logout">'.LOGIN_MENU_L8.'</a>
						';

				if (USER == TRUE){
						$ret .= '
											<h3 class="prologin">'.LOGIN_MENU_L5.' '.USERNAME.'</h3>
											<div class="H5"></div>
											'.$fs_admin.'
											'.$fs_profile.'
											'.$fs_settings.'
											'.$fs_logout.'
						';
				} else {
					if($pref['user_reg'])
						{
							$fs_signup = '
								<a class="custom-loginc-link custom-signup f-right" href="'.e_SIGNUP.'">'.LOGIN_MENU_L3.'</a>
							';
						}
					if ($pref['user_tracking'] == "cookie")
						{
							$fs_autologin = "<input type='checkbox' name='autologin' value='1' />".LOGIN_MENU_L6."&nbsp;&nbsp;\n";
						}
						
					if (!$pref['auth_method'] || $pref['auth_method'] == 'e107')
						{
							$fs_pw = "<a class='custom-loginc-link custom-fpw' href='".SITEURL."fpw.php' title=\"".LOGIN_MENU_L4."\">".LOGIN_MENU_L4."</a>";
						}
					
					$user_txt = str_replace(':','',LOGIN_MENU_L1);	
					$pass_txt = str_replace(':','',LOGIN_MENU_L2);	
						
						$ret .= '
							<form method="post" action="'.e_SELF.'">
								<div id="login-wrapper" style="display: '.(($err && $err != '') ? 'block' : 'none').'">
									<div id="login-close" onclick="$(\'login-wrapper\').hide();">
										<img src="'.THEME_ABS.'images/p19_login_close.png" alt="Close" />
									</div>
									<div class="box-TC">
										<div class="cont">
											'.$err.'
											<div class="labels f-left">
												<div class="label right">'.$user_txt.'</div>
												<div class="H20"><!-- --></div>
												<div class="label right">'.$pass_txt.'</div>
											</div>
											<div class="fields f-right">
												<div class="user-field center">
													<input class="custom-loginc user" type="text" name="username" size="20" maxlength="20" />
												</div>
												<div class="H20"><!-- --></div>
												<div class="pass-field center">
													<input class="custom-loginc pass" type="password" name="userpass" size="15" maxlength="20" />
												</div>
												
												'.$fs_sec_code_img.'
												<div class="autologin">
													'.$fs_autologin.'
												</div>
												<div class="buttons">
													
													<button class="button f-right" type="submit" name="userlogin" value="'.LOGIN_MENU_L28.'" ><span>'.LOGIN_MENU_L28.'</span></button>
													
												</div>
											</div>
											<div class="clear"><!-- --></div>
																	
											
											<div class="fpw-cont">'.$fs_pw.'</div>
										</div>
									</div>
									<div class="box-BC">
									
									</div>
								</div>
							</form>
						';
				
				}
				return $ret;
				break;

}
