<?php
/*
	This is a handler for displaying a security image and verifying its validity.
	Some of the code was ripped from postnuke...thanks :)
	
*/

if(!$mySQLserver){
	require_once("../class2.php");
}

class secure_image {
	
	var $random_number;
	
	function secure_image(){
		mt_srand ((double)microtime()*1000000);
		$maxran = 1000000;
		$random_num = mt_rand(0, $maxran);
		$this -> random_number = $random_num;
	}

	function create_code($rand_num){
		global $pref;
		$datekey = date("F j");
		$rcode = hexdec(md5($_SERVER[HTTP_USER_AGENT] . serialize($pref). $rand_num . $datekey));
		$code = substr($rcode, 2, 6);
		return $code;
	}

	function verify_code($rand_num,$checkstr){
		return ($checkstr == $this -> create_code($rand_num));
	}

	function render_image($rand_num){
		$image = ImageCreateFromJPEG(e_IMAGE."generic/code_bg.jpg");
		$code = $this -> create_code($rand_num);
    	$text_color = ImageColorAllocate($image, 80, 80, 80);
    	Header("Content-type: image/jpeg");
    	ImageString ($image, 5, 12, 2, $code, $text_color);
    	ImageJPEG($image, '', 75);
    	ImageDestroy($image);
    	die();
    }

	function r_image(){
		global $HANDLERS_DIRECTORY;
		return "<img src='".e_BASE.$HANDLERS_DIRECTORY."secure_img_handler.php?RANDNUM.".$this -> random_number."' />";
	}
}

if(preg_match("#RANDNUM\.#",e_QUERY)){
	$qs = explode(".",e_QUERY);
	$si = new secure_image;
	$si -> render_image($qs[1]);
}

?>