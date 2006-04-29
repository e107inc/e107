global $sql,$parm;

    list($name,$path,$default,$width,$height,$multiple) = explode(",",$parm);

	require_once(e_HANDLER."file_class.php");
	$fl = new e_file;

  //	$paths = explode("|",$path); 

	if($imagelist = $fl->get_files($path,".jpg|.gif|.png")){
		sort($imagelist);
	}

    $multi = ($multiple) ? "multiple='multiple' style='height:{$height}'" : "style='float:left'";
    $width = ($width) ? $width : "*";
    $height = ($height) ? $height : "*";


	$text .= "<select {$multi} class='tbox' name='$name' id='$name' onchange=\"preview_image('$name','$path');\">
	<option value=''> --  -- </option>";
	foreach($imagelist as $icon)
	{
		$selected = ($default == $icon['fname']) ? " selected='selected'" : "";
		$text .= "<option value='".$icon['fname']."'".$selected.">".$icon['fname']."</option>\n";
	}
	$text .= "</select>";

	$pvw_default = ($default) ? $path.$default : e_IMAGE."generic/blank.gif";
	$text .= "&nbsp;<img id='{$name}_prev' src='{$pvw_default}' alt='' style='width:{$width};height:{$height}' />";


 return $text;
