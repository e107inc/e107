<?php
// $Id$

// General purpose image bbcode. As well as the obvious insertion of a picture:
// 	a) if filname begins with 'th_' or 'thumb_', creates link to main image opening in new window
//	b) If filename contains '*', treats it as a wildcard, and displays a random image from all matching file names found ? really?
//
// Can use simple classes for float - e.g.:
// .floatleft {clear: right; float: left; margin: 0px 5px 5px 0px; padding:2px; border: 0px;}
// .floatright {clear: left; float: right; margin: 0px 0px 0px 5px; padding:2px; border: 0px;}
// Currently defaults class to 'floatnone' - overridden by bbcode


if (!defined('e107_INIT')) { exit; }

class bb_img extends e_bb_base
{
	function toDB($code_text, $parm)
	{
		$parms = eHelper::scParams($parm);
		$safe = array();
		
		if(vartrue($parms['class'])) 	$safe['class'] = eHelper::secureClassAttr($parms['class']);
		if(vartrue($parms['id']))		$safe['id']     = eHelper::secureIdAttr($parms['id']);
		if(vartrue($parms['style'])) 	$safe['style'] = eHelper::secureStyleAttr($parms['style']);
		if($safe)
		{
			return '[img '.eHelper::buildAttr($safe).']'.$code_text.'[/img]';
		}
		return '[img]'.$code_text.'[/img]';
	}
	
    /**
     * Media Manager bbcode. eg. using {e_MEDIA_IMAGE} and auto-resizing. 
     * @return <img> tag with resized image. 
     */
    private function mediaImage($code_text,$parm)
    {
        $tp = e107::getParser();
          
		// Replace the bbcode path with a real one. 
		$code_text = str_replace('{e_MEDIA}images/','{e_MEDIA_IMAGE}',$code_text); //BC 0.8 fix.
        $code_text = str_replace('{e_MEDIA_IMAGE}', e_HTTP."thumb.php?src=e_MEDIA_IMAGE/", $code_text);
		$code_text = str_replace('{e_THEME}', e_HTTP."thumb.php?src=e_THEME/", $code_text);
        $imgParms    = $this->processParm($code_text, $parm);
        
        foreach($imgParms as $k => $v)
        {
           // $parmStr .= " ".$k.'="'.$v.'"';
            $p[] = $tp->toAttribute($k).'="'.$tp->toAttribute($v).'"';
        } 
        
       
        $w = e107::getBB()->resizeWidth(); // varies depending on the class set by external script. see admin->media-manager->prefs
        
        
        $w = vartrue($imgParms['width']) ? intval($imgParms['width']) : vartrue(e107::getBB()->resizeWidth(),0);
     //   $h = vartrue($imgParms['height']) ? intval($imgParms['height']) : e107::getBB()->resizeHeight();
        
        $resize = "&w=".$w; // Always resize - otherwise the thumbnailer returns nothing. 
        $parmStr = implode(" ",$p);
    
   //     print_a($imgParms);
   //     print_a($parmStr);
        
        return "<img src=\"".$code_text.$resize."\" {$parmStr} />";
    }

    /**
     * Process the [img] bbcode parm. ie. [img parms]something[/img]
     */
    private function processParm($code_text, $parm, $mode='')
    {      
        $tp = e107::getParser(); 
        
        $imgParms               = array();
        $parmStr                = "";
     
        $parm = preg_replace('#onerror *=#i','',$parm);
        $parm = str_replace("amp;", "&", $parm);
        
     //   $parm = str_replace(" ","&",$parm); // Needed as parse_str() doesn't know how to handle spaces. Could return [width] => '400 AltValue'
        
        parse_str($parm,$imgParms);
        

		if(!vartrue($imgParms['width']) && strpos($parm,'width')!==false) // Calculate thumbnail width from style. 
        {
			preg_match("/width:([\d]*)/i", $parm, $m);
			if($m[1] > 0)
			{
				$imgParms['width'] = $m[1];
			}
		}
		
        if(!vartrue($imgParms['alt'])) // Generate an Alt value from filename if one not found.  
        {
           preg_match("/([\w]*)(?:\.png|\.jpg|\.jpeg|\.gif)/i", $code_text, $match); // Generate required Alt attribute. 
           $imgParms['alt']        = ucwords(str_replace("_"," ",$match[1])); 
        }
        
        $imgParms['class']      = "img-rounded bbcode ".e107::getBB()->getClass('img');;  //  This will be overridden if a new class is specified        
        
        if($mode == 'string')
		{
			$text = '';
			foreach($imgParms as $key => $val)
			{
				$text .= $key."=\"".$val."\" ";	
			}	
			return $text;
		}
        
        
        
        return $imgParms;       
    }  
    
	
	function toHTML($code_text, $parm)
    {
       
		$tp = e107::getParser();
        $pref = e107::getPref();

        if (trim($code_text) == "") return "";                      // Do nothing on empty file
        
        if(substr($code_text,0,15) == '{e_MEDIA_IMAGE}' || substr($code_text,0,9) == '{e_MEDIA}' || substr($code_text,0,9) == '{e_THEME}') // Image from Media-Manager. 
        {  
            return $this->mediaImage($code_text, $parm);          
        }
        
     
    
        
		if (preg_match("#\.php\?.*#",$code_text)){return "";} //XXX Breaks MediaManager Images, so do it after mediaManager check. 
		
		$addlink = FALSE;

        
		// Automatic Img Resizing -- 
		$w = e107::getBB()->resizeWidth(); // varies depending on the class set by external script. see admin->media-manager->prefs
		$h = e107::getBB()->resizeHeight();
		
		// No resizing on v1.x legacy images. 
		if(strpos($code_text,"://")==false && ($w || $h) && strpos($code_text,"{e_IMAGE}custom")===false && strpos($code_text,"newspost_images/")===false) // local file. 
		{
			$code_text = $tp->thumbUrl($code_text,'w='.$w.'&h='.$h);
		}
		
		// ------------------------
		
		$search = array('"', '{E_IMAGE}', '{E_FILE}', '{e_IMAGE}', '{e_FILE}');
		$replace = array('&#039;', e_IMAGE_ABS, e_FILE_ABS, e_IMAGE_ABS, e_FILE_ABS);
		$replaceInt = array('&#039;', e_IMAGE, e_FILE, e_IMAGE, e_FILE);
		$intName = str_replace($search, $replaceInt, $code_text);			// Server-relative file names
		
	
		$code_text = str_replace($search, $replace, $code_text);
		$code_text = $tp -> toAttribute($code_text);
        
		$img_file = pathinfo($code_text);		// 'External' file name. N.B. - might still contain a constant such as e_IMAGE
		
        $parmStr = $this->processParm($code_text, $parm, 'string');
		

		
		// Select a random file if required
		if (strpos($img_file['basename'],'*') !== FALSE)
		{
			$fileList = array();
			$intFile = pathinfo($intName);		// N.B. - might still contain a constant such as e_IMAGE
			$matchString = '#'.str_replace('*','.*?',$intFile['basename']).'#';
			$dirName = $tp->replaceConstants($intFile['dirname'].'/');			// we want server-relative directory
			if (($h = opendir($dirName)) !== FALSE)
			{
				while (($f = readdir($h)) !== FALSE)
				{
					if (preg_match($matchString,$f))
					{
						$fileList[] = $f;		// Just need to note file names
					}
				}
				closedir($h);
			}
			else
			{
				echo "Error opening directory: {$dirName}<br />";
				return '';
			}
			if (count($fileList))
			{
				$img_file['basename'] = $fileList[mt_rand(0,count($fileList)-1)];		// Just change name of displayed file - no change on directory
				$code_text = $img_file['dirname']."/".$img_file['basename'];
			}
			else
			{
				echo 'No file: '.$code_text;
				return '';
			}
		}
		
		
		// Check for whether we can display image down here - so we can show image name if appropriate
		if (!vartrue($pref['image_post']) || !check_class($pref['image_post_class']))
		{
			switch ($pref['image_post_disabled_method'])
			{
				case '1' :
					return CORE_LAN17;
				case '2' :
					return '';
			}
			return CORE_LAN18.$code_text;
		}
		
		
		// Check for link to main image if required
		if (strpos($img_file['basename'],'th_') === 0)
		{
			$addlink = TRUE;
			$main_name = $img_file['dirname']."/".substr($img_file['basename'],3);     // delete the 'th' prefix from file name
		}
		elseif (strpos($img_file['basename'],'thumb_') === 0)
		{
			$addlink = TRUE;
			$main_name = $img_file['dirname']."/".substr($img_file['basename'],6);     // delete the 'thumb' prefix from file name
		}
		

		
		if ($addlink)
		{
			return "<a href='".$main_name."' rel='external'><img src='".$code_text."' {$parmStr} /></a>";
		}
		else
		{
			return "<img src='".$code_text."' {$parmStr} />";
		}
	}

}

?>