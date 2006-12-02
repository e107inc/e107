if (ADMIN) {
	global $ns;
	ob_start();
	if(!FILE_UPLOADS){
	         echo message_handler("ADMIN_MESSAGE", LAN_head_2, __LINE__, __FILE__);
	}
	/*
	if(OPEN_BASEDIR){
	        echo message_handler("ADMIN_MESSAGE", LAN_head_3, __LINE__, __FILE__);
	}
	*/
	$message_text = ob_get_contents();
	ob_end_clean();
	return $message_text;
}
