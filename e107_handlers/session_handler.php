<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/classes/session_handler..php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

function sess_open($save_path, $session_name){
	global $mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $mySQLprefix, $session_connection;
	$session_connection = mysql_pconnect($mySQLserver, $mySQLuser, $mySQLpassword);
	mysql_select_db($mySQLdefaultdb, $session_connection);
	return true;
}

function sess_close(){
	return true;
}


function sess_read($session_id){
	global $session_connection, $session_lifetime, $mySQLprefix, $session_read;
	if($result = mysql_query("SELECT * FROM ".$mySQLprefix."session WHERE session_id = '$session_id' AND session_expire > " . time(), $session_connection)){
		$session_read = mysql_fetch_assoc($result);
		return $session_read['session_data'];
	}else{
		return FALSE;
	}
}

function sess_write($session_id, $session_data){
	if(!$session_data){ return FALSE; }
	global $session_connection, $session_lifetime, $mySQLprefix, $session_read;
	$expiry = time() + $session_lifetime;
	if($session_read && $session_read['session_ip'] != get_full_ip()){
		session_destroy();
        die("Invalid session ID");
	}
	if(version_compare(phpversion(), "4.3.0", "<")){
        $_session_data = mysql_escape_string($session_data);
    }else{
        $_session_data = mysql_real_escape_string($session_data);
    }

	if($session_read){
		$query = "UPDATE ".$mySQLprefix."session SET session_expire = $expiry, session_data = '$_session_data' WHERE session_id = '$session_id' AND session_expire > " . time();
		$result = mysql_query($query, $session_connection);
	}else{
		$query = "INSERT INTO ".$mySQLprefix."session VALUES ('$session_id', $expiry, ".time().", '".get_full_ip()."', '$_session_data')";
		$result= mysql_query($query, $session_connection);
	}
	return TRUE;
}

function sess_destroy($session_id){
	global $session_connection, $mySQLprefix;
	$query = "DELETE FROM ".$mySQLprefix."session WHERE session_id = '$session_id'";
	$result = mysql_query($query, $session_connection);
	return TRUE;
}

function sess_gc($session_lifetime){
	global $session_connection, $mySQLprefix;
	$query = "DELETE FROM ".$mySQLprefix."session WHERE session_expire < " . time();
	$result = mysql_query($query, $session_connection);
//	Uncomment the foolowing lines if you want garbage collection logged ...
//	$fp =fopen('gc.txt',ab);
//	fwrite ($fp,"DELETE FROM ".$mySQLprefix."session WHERE session_expire < " .time()."\n");
//	fclose($fp);
	return mysql_affected_rows($session_connection);
}

function get_full_ip(){
	$ip_addr = getip();
	$tmp = $_SERVER['REMOTE_ADDR'];
	$ip_resolved = gethostbyaddr($tmp);
	$tmp2 = ($tmp != $ip_resolved && $ip_resolved ? $tmp." - ". $ip_resolved : $tmp2 = $tmp);
	$full_ip = ($ip_addr != $tmp ? "$ip_addr | $tmp2" : $tmp2);
	return $full_ip;
}

session_set_save_handler("sess_open", "sess_close", "sess_read", "sess_write", "sess_destroy", "sess_gc");

ini_set ( "session.save_handler", "user" );
$session_cookie_lifetime = 0;
$session_cookie_path = '/';
$session_cookie_domain = '';
$session_cache_expire = 60*24*30;
//$session_lifetime = ini_get("session.gc_maxlifetime");
$session_lifetime = 60*24*30;
session_name("PHPSESSID");
if($_SERVER["HTTPS"] == "on"){ $session_cookie_secure = true; }
session_set_cookie_params($session_cookie_lifetime, $session_cookie_path, $session_cookie_domain, $session_cookie_secure);
if(version_compare(phpversion(), "4.3.0", ">=")) ini_set ( "session.use_only_cookies", $session_use_only_cookies );
if(version_compare(phpversion(), "4.2.0", ">=")) session_cache_expire ($session_cache_expire);
ini_set ("session.url_rewriter.tags", 'a=href,area=href,frame=src,input=src,form=fakeentry');

if($sql -> db_Select("session", "session_id", "session_ip='".get_full_ip()."' ")){
	$row = $sql -> db_Fetch();
	session_id($row['session_id']);
}

session_start();
?>