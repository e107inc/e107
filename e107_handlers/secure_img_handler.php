<?php
/*
	This is a handler for displaying a security image and verifying its validity.
	Some of the code was ripped from postnuke...thanks :)
	
*/

global $mySQLserver;
if(!$mySQLserver){
	if(file_exists("../class2.php")){
		require_once("../class2.php");
	} elseif(file_exists("class2.php")){
		require_once("class2.php");
	}
}

class secure_image {
	
	var $random_number;

	function secure_image() {
		list($usec, $sec) = explode(" ", microtime());
		$this -> random_number = str_replace(".","",$sec.$usec);
	}
	
	function create_code(){
		global $pref, $sql;
		mt_srand ((double)microtime()*1000000);
		$maxran = 1000000;
		$rand_num = mt_rand(0, $maxran);
		$datekey = date("r");
		$rcode = hexdec(md5($_SERVER[HTTP_USER_AGENT] . serialize($pref). $rand_num . $datekey));
		$code = substr($rcode, 2, 6);
		$recnum = $this -> random_number;
		$del_time = time()+1200;
		$sql -> db_Insert("tmp","'{$recnum}',{$del_time},'{$code}'");
		return $recnum;
	}

	function verify_code($rec_num,$checkstr){
		global $sql;
		if($sql -> db_Select("tmp","tmp_info","tmp_ip = '{$rec_num}'")){
			$row = $sql -> db_Fetch();
			$sql -> db_Delete("tmp","tmp_ip = '{$rec_num}'");
			return ($checkstr == $row[0]);
		} else {
			return FALSE;
		}
	}

	function render_image($rec_num){
		global $sql;
		ob_clean();
		if($sql -> db_Select("tmp","tmp_info","tmp_ip = '{$rec_num}'")){
			$row = $sql -> db_Fetch();
			$code = $row['tmp_info'];
			$image = ImageCreateFromJPEG(e_IMAGE."generic/code_bg.jpg");
    		$text_color = ImageColorAllocate($image, 80, 80, 80);
    		Header("Content-type: image/jpeg");
    		ImageString ($image, 5, 12, 2, $code, $text_color);
    		ImageJPEG($image, '', 75);
    		ImageDestroy($image);
    		die();
    	}
    }

	function r_image(){
		global $HANDLERS_DIRECTORY;
		$code = $this -> create_code();
		return "<img src='".e_BASE.$HANDLERS_DIRECTORY."secure_img_handler.php?REC.{$code}' />";
	}
}

if(preg_match("#REC\.#",e_QUERY)){
	$qs = explode(".",e_QUERY,2);
	$si = new secure_image;
	$si -> render_image($qs[1]);
}

?>