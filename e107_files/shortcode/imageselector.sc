// $Id: imageselector.sc,v 1.4 2008-01-16 10:52:33 e107coders Exp $

global $sql,$parm,$tp;

	if(strstr($parm,"=")){  // query style parms.
    	 parse_str($parm, $tmp);
		 extract($tmp);
	}
	else
	{        // comma separated parms.
    	list($name,$path,$default,$width,$height,$multiple,$label,$subdirs,$filter,$fullpath) = explode(",",$parm);
    }

	require_once(e_HANDLER."file_class.php");
	$fl = new e_file;

  	$paths = explode("|",$path);
    $recurse = ($subdirs) ? $subdirs : 0;
	$imagelist = array();

	foreach($paths as $path)
	{
		$imagelist += $fl->get_files($path,".jpg|.gif|.png|.JPG|.GIF|.PNG", 'standard', $recurse);
    }

    if($imagelist)
	{
   		sort($imagelist);
	}

	if(!$fullpath && (count($paths) > 1))
	{
    	$fullpath = TRUE;
	}

    $multi = ($multiple == "TRUE" || $multiple == "1") ? "multiple='multiple' style='height:{$height}'" : "style='float:left'";
    $width = ($width) ? $width : "*";
    $height = ($height) ? $height : "*";
    $label = ($label) ? $label : " -- -- ";

	$text .= "<select {$multi} class='tbox' name='$name' id='$name' onchange=\"replaceSC('$name',this.form,'{$name}_prev');\">
	<option value=''>".$label."</option>\n";
	foreach($imagelist as $icon)
	{
		$dir = str_replace($paths,"",$icon['path']);

	  	if(!$filter || ($filter && ereg($filter,$dir.$icon['fname'])))
	  	{
	  		$pth = ($fullpath) ? $tp->createConstants($icon['path'],1) : $dir;
			$selected = ($default == $pth.$icon['fname']) ? " selected='selected'" : "";
			$text .= "<option value='".$pth.$icon['fname']."'".$selected.">".$dir.$icon['fname']."</option>\n";
        }
	}
	$text .= "</select>";

	$pvw_default = ($default) ? $path.$default : e_IMAGE_ABS."generic/blank.gif";
	if($default[0]=="{")
	{
    	$pvw_default = $tp->replaceConstants($default);
	}
  	$text .= "&nbsp;<span id='{$name}_prev'><img src='{$pvw_default}' alt='' style='width:{$width};height:{$height}' /></span>\n";


 return "\n\n<!-- Start Image Selector -->\n\n".$text."\n\n<!-- End Image Selector -->\n\n";