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
/* 07-04-2004 - unknown: removed source/destination file rewriting, this should not break existing code */
/* 09-04-2004 - unknown: source/destination file should be quoted, otherwise files with spaces can't be handled */
function resize_image($source_file, $destination_file, $type = "upload", $model=""){

	global $pref;

	$new_height=0;
	$mode = ($pref['resize_method'] ? $pref['resize_method'] : "gd2");
	if($type == "upload"){
		$new_size = ($pref['im_width'] ? $pref['im_width'] : 400);
	}else if(is_numeric($type)){
		$new_size = $type;
	}else{
		$new_size = ($pref['im_width'] ? $pref['im_width'] : 120);	//avatar
		$new_height = ($pref['im_height'] ? $pref['im_height'] : 100);	//avatar
	}

	$im_quality = ($pref['im_quality'] ? $pref['im_quality'] : 99);

	$image_stats = getimagesize($source_file);

    if($image_stats[0] <= $type && is_numeric($type)){return false;}
	
if($image_stats == null){ echo "<b>DEBUG</b> image_stats are null<br />"; return false; }

	if ($image_stats[2] != 1 && $image_stats[2] != 2 && $image_stats[2] != 3 && ($mode == 'gd1' || $mode == 'gd2')){
		echo "<b>DEBUG</b> Wrong image type<br />";
		return FALSE;
	}
	$imagewidth = $image_stats[0];
	$imageheight = $image_stats[1];
	if($imagewidth <= $new_size && ($imageheight <= $new_height || $new_height == 0)){ return TRUE; }
	$ratio = ($imagewidth / $new_size);
	$new_imageheight = round($imageheight / $ratio);
	if(($new_height <= $new_imageheight) && $new_height > 0){
		$ratio = $new_imageheight / $new_height;
		$new_imageheight = $new_height;
		$new_size = round($new_size / $ratio);
		
	}
	if($mode == "ImageMagick"){
		if ($destination_file == "stdout") {
			/* if destination is stdout, output directly to the browser */
			$destination_file = "jpg:-";
			header("Content-type: image/jpeg");
			passthru ($pref['im_path']."convert -quality ".$im_quality." -antialias -geometry ".$new_size."x".$new_imageheight." ".escapeshellarg($source_file)." '".$destination_file."'");
		}else{
			/* otherwise output to file */
			if($model == "copy"){
				$name = substr($destination_file, (strrpos($destination_file, "/")+1));
				$name2 = "thumb_".$name;
				$destination_file = str_replace($name, $name2, $destination_file);
			}
			exec ($pref['im_path']."convert -quality ".$im_quality." -antialias -geometry ".$new_size."x".$new_imageheight." ".escapeshellarg($source_file)." '".$destination_file."'");

		}
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
		if($model == "copy"){
			$name = substr($destination_file, (strrpos($destination_file, "/")+1));
			$name2 = "thumb_".$name;
			$destination_file = str_replace($name, $name2, $destination_file);
		}

		if($destination_file == "stdout"){
			header("Content-type: image/jpeg");
			imagejpeg($dst_img, '', $im_quality);
		}else{
			imagejpeg($dst_img, $destination_file, $im_quality);
			imagedestroy($src_img);
			imagedestroy($dst_img);
		}

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

		if($model == "copy"){
			$name = substr($destination_file, (strrpos($destination_file, "/")+1));
			$name2 = "thumb_".$name;
			$destination_file = str_replace($name, $name2, $destination_file);
		}
		if($destination_file == "stdout"){
			header("Content-type: image/jpeg");
			imagejpeg($dst_img, '', $im_quality);
		}else{
			imagejpeg($dst_img, $destination_file, $im_quality);
			imagedestroy($src_img);
			imagedestroy($dst_img);
		}		
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