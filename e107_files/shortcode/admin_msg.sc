if (ADMIN) {
	global $ns;
	if(!FILE_UPLOADS){
	        return message_handler("ADMIN_MESSAGE", LAN_head_2, __LINE__, __FILE__);
	}
	/*
	if(OPEN_BASEDIR){
	        return message_handler("ADMIN_MESSAGE", LAN_head_3, __LINE__, __FILE__);
	}
	*/
}
