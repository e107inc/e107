// $Id$
// <?
global $sql,$parm;

	if(strstr($parm,"=")){  // query style parms.
    	 parse_str($parm, $tmp);
		 extract($tmp);
	}else{        // comma separated parms.
    	list($name,$path,$default,$width,$height,$multiple,$label,$subdirs) = explode(",",$parm);
    }


	require_once(e_HANDLER."file_class.php");
	$fl = new e_file;

  //	$paths = explode("|",$path);
    $recurse = ($subdirs) ? $subdirs : 0;
	if($imagelist = $fl->get_files($path,'\.jpg|\.gif|\.png|\.JPG|\.GIF|\.PNG|\.jpeg|\.JPEG|\.svg|\.SVG', 'standard', $recurse))
	{
		sort($imagelist);
	}

    $multi = ($multiple == "TRUE" || $multiple == "1") ? "multiple='multiple' style='height:{$height}'" : "style='float:left'";
    $width = ($width) ? $width : "*";
    $height = ($height) ? $height : "*";
    $label = ($label) ? $label : " -- -- ";

	$text .= "<select {$multi} class='tbox sc-imageselector' name='$name' id='$name' onchange=\"preview_image('$name','$path','".e_IMAGE_ABS."generic/blank.gif');\">
	<option value=''>".$label."</option>\n";
	foreach($imagelist as $icon)
	{
		$dir = str_replace($path,"",$icon['path']);
		$selected = ($default == $dir.$icon['fname']) ? " selected='selected'" : "";
		$text .= "<option value='".$dir.$icon['fname']."'".$selected.">".$dir.$icon['fname']."</option>\n";
	}
	$text .= "</select>";

	$pvw_default = ($default) ? $path.$default : e_IMAGE_ABS."generic/blank.gif";
  	$text .= "&nbsp;<img id='{$name}_prev' src='{$pvw_default}' alt='' style='width:{$width};height:{$height}' />\n";


 return "\n\n<!-- Start Image Selector -->\n\n".$text."\n\n<!-- End Image Selector -->\n\n";