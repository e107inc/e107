<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/sitelinks_handler.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
|
+---------------------------------------------------------------+
*/
function resize_image($source_file, $destination_file, $type = "upload"){
	global $pref;

	$mode = ($pref['resize_method'] ? $pref['resize_method'] : "gd2");
	if($type == "upload"){
		$new_size = ($pref['im_width'] ? $pref['im_width'] : 400);
	}else if(is_numeric($type)){
		$new_size = $type;
	}else{
		$new_size = ($pref['im_width'] ? $pref['im_width'] : 70);	//avatar
	}

	$im_quality = ($pref['im_quality'] ? $pref['im_quality'] : 99);

	$image_stats = getimagesize($source_file);
	if($image_stats == null){ return false; }

	if ($image_stats[2] != 2 && $image_stats[2] != 3 && ($mode == 'gd1' || $mode == 'gd2')){
		echo "Unable to resize that image type - not supported under GD.<br />";
		return FALSE;
	}

	$imagewidth = $image_stats[0];
	if($imagewidth <= $new_size){ return TRUE; }
	$imageheight = $image_stats[1];
	$ratio = ($imagewidth / $new_size);
	$new_imageheight = round($imageheight / $ratio);

	if($mode == "ImageMagick"){
		$source_file = $_SERVER['DOCUMENT_ROOT'].e_HTTP.$source_file;
		$destination_file = $_SERVER['DOCUMENT_ROOT'].e_HTTP.$destination_file;
		exec ($pref['im_path']."convert -quality ".$im_quality." -antialias -geometry ".$new_size."x".$new_imageheight." ".$source_file." ".$destination_file);
	

	}else if($mode == "gd1"){
		if($image_stats[2] == 2)
			$src_img = imagecreatefromjpeg($source_file);
		else
			$src_img = imagecreatefrompng($source_file);
		if(!$src_img){
			return FALSE;
		}
		$dst_img = imagecreate($new_size, $new_imageheight);
		imagecopyresized($dst_img, $src_img, 0, 0, 0, 0, $new_size, $new_imageheight, $imagewidth, $imageheight);
		imagejpeg($dst_img, $destination_file, $im_quality);
		imagedestroy($src_img);
		imagedestroy($dst_img);

	}else if($mode == "gd2"){

		if ($image_stats[2] == 2)
			$src_img = imagecreatefromjpeg($source_file);
		else
			$src_img = imagecreatefrompng($source_file);
		if (!$src_img){
			return FALSE;
		}

		$dst_img = imagecreatetruecolor($new_size, $new_imageheight);
		imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_size, $new_imageheight, $imagewidth, $imageheight);
		imagejpeg($dst_img, $destination_file, $im_quality);
		imagedestroy($src_img);
		imagedestroy($dst_img);
		
	}

	@chmod($destination_file, 0644);
	if($pref['image_owner']){
		@chown($destination_file, $pref['image_owner']);
	}

	$image_stats = getimagesize($destination_file);
	if($image_stats == null){
//		@unlink($source_file);
		return FALSE;
	}else{
		return TRUE;
	}
}
?>