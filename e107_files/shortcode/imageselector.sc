// $Id: imageselector.sc,v 1.2 2006-05-06 03:25:40 e107coders Exp $

global $sql,$parm;

	if(strstr($parm,"=")){  // query style parms.
    	 parse_str($parm, $tmp);
		 extract($tmp);
	}else{        // comma separated parms.
    	list($name,$path,$default,$width,$height,$multiple,$label) = explode(",",$parm);
    }


	require_once(e_HANDLER."file_class.php");
	$fl = new e_file;

  //	$paths = explode("|",$path);

	if($imagelist = $fl->get_files($path,".jpg|.gif|.png")){
		sort($imagelist);
	}

    $multi = ($multiple == "TRUE" || $multiple == "1") ? "multiple='multiple' style='height:{$height}'" : "style='float:left'";
    $width = ($width) ? $width : "*";
    $height = ($height) ? $height : "*";
    $label = ($label) ? $label : " -- -- ";

	$text .= "<select {$multi} class='tbox' name='$name' id='$name' onchange=\"preview_image('$name','$path');\">
	<option value=''>".$label."</option>\n";
	foreach($imagelist as $icon)
	{
		$selected = ($default == $icon['fname']) ? " selected='selected'" : "";
		$text .= "<option value='".$icon['fname']."'".$selected.">".$icon['fname']."</option>\n";
	}
	$text .= "</select>";

	$pvw_default = ($default) ? $path.$default : e_IMAGE."generic/blank.gif";
  	$text .= "&nbsp;<img id='{$name}_prev' src='{$pvw_default}' alt='' style='width:{$width};height:{$height}' />\n";


 return "\n\n<!-- Start Image Selector -->\n\n".$text."\n\n<!-- End Image Selector -->\n\n";