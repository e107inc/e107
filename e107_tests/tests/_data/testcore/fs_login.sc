global $pref;
if(!USER){
	$loginsc = '
	<div class="fs_c_login">
    <div class="singin">
			<a href="'.e_HTTP.'login.php">'.LAN_THEME_SING.'</a>
		</div>
    <div class="register">
			<div class="regl"></div>
			<div class="regr"></div>
			<div class="regm">
				<div class="register_text">
					<a href="'.e_HTTP.'signup.php">'.LAN_THEME_REG.'</a>
				</div>
			</div>
    </div>
  </div>
  ';
		return $loginsc;
}


if (USER == TRUE || ADMIN == TRUE) {
	$loginsc = '
			<div class="fs_c_login2">
				<span class="fs_welcome">
					'.LAN_THEME_23.'&nbsp;&nbsp;'.USERNAME.'
				</span>
  ';
		
	$loginsc .= '
	';
					if (ADMIN == TRUE) {
						$loginsc .= '
				<span class="fs_login_links_b">
												<a href="'.e_ADMIN_ABS.'admin.php">'.LAN_THEME_24.'</a>&nbsp;&nbsp;
							';
								}
									$loginsc .= '
											<a href="'.e_HTTP.'user.php?id.'.USERID.'">'.LAN_THEME_27.'</a>&nbsp;&nbsp;
											<a href="'.e_HTTP.'usersettings.php">'.LAN_THEME_26.'</a>&nbsp;&nbsp;
										 	'.(isset($pref['plug_installed']['list_new']) ? '<a href="'.e_PLUGIN_ABS.'list_new/list.php?new">'.LAN_THEME_29.'</a>' : '').'
				</span>
				<span class="logout">
					<span class="logm">
						<span class="register_text">
							<a href="'.e_HTTP.'news.php?logout">'.LAN_THEME_28.'</a>
						</span>
					</span>
				</span>
			</div>
	';

	$loginsc .= '
  ';
	return $loginsc;


}