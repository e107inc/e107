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
function resize_image($source_file, $destination_file){

	global $pref;

	$mode = ($pref['resize_method'][1] ? $pref['resize_method'][1] : "gd2");
	$new_size = ($pref['im_width'][1] ? $pref['im_width'][1] : 400);

	$image_stats = getimagesize($source_file);
	if($image_stats == null){ return false; }

	if ($image_stats[2] != 2 && $image_stats[2] != 3 && ($mode == 'gd1' || $mode == 'gd2')){
		echo "<b>DEBUG</b> Wrong image type<br />";
		return FALSE;
	}

	$imagewidth = $image_stats[0];
	$imageheight = $image_stats[1];
//	$image_type = $image_stats[2];
	$ratio = ($imagewidth / $new_size);
	$new_imageheight = round($imageheight / $ratio);

	if($mode == "ImageMagick"){
		if (preg_match("/\A[a-z]:/i",__FILE__)){
			$source_file =   '"'.$source_file.'"';
			$im_destination_file = str_replace('%', '%%', ('"'.$destination_file.'"'));
		} else {
			$source_file =   escapeshellarg($source_file);
			$im_destination_file = str_replace('%', '%%', escapeshellarg($destination_file));
		}
		exec ($pref['im_path']."convert -quality ".$pref['im_quality'][0]." -antialias -geometry ".$new_size."x".$new_imageheight." ".$source_file." ".$im_destination_file);
	

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
		imagejpeg($dst_img, $destination_file, $pref['im_quality'][0]);
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
		imagejpeg($dst_img, $destination_file, $pref['im_quality'][0]);
		imagedestroy($src_img);
		imagedestroy($dst_img);
		
	}

	@chmod($destination_file, 0644);
	if($pref['image_owner'][0]){
		@chown($destination_file, $pref['image_owner'][0]);
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