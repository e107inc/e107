<?php
function pm_set_default_prefs()
{
	$ret = array(
		'title' 				=> 'PMLAN_PM',
		'animate' 			=> '1',
		'dropdown' 			=> '0',
		'read_timeout' 	=> '0',
		'unread_timeout'	=> '0',
		'popup'				=> '0',
		'popup_delay'		=> '',
		'pm_class'			=> e_UC_MEMBER,
		'notify_class'		=>	e_UC_ADMIN,
		'receipt_class'	=> e_UC_MEMBER,
		'attach_class'		=>	e_UC_ADMIN,
		'attach_size'		=> 500,
		'sendall_class'	=>	e_UC_ADMIN,
		'multi_class'		=> e_UC_ADMIN,
		'allow_userclass'	=> '1'
	);
	return $ret;
}
?>