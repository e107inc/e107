<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/theme_handler.php,v $
|     $Revision: 1.26 $
|     $Date: 2009-07-07 22:56:12 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

class themeHandler{

	var $themeArray;
	var $action;
	var $id;
	var $frm;

	/* constructor */

	function themeHandler() {

   		require_once(e_HANDLER."form_handler.php");
		$this->frm = new e_form(); //enable inner tabindex counter

		if (isset($_POST['upload'])) {
			$this -> themeUpload();
		}

		$this -> themeArray = $this -> getThemes();

		foreach($_POST as $key => $post)
		{
			if(strstr($key,"preview"))
			{
			 //	$this -> id = str_replace("preview_", "", $key);
				$this -> id = key($post);
				$this -> themePreview();
			}
			if(strstr($key,"selectmain"))
			{
			//	$this -> id = str_replace("selectmain_", "", $key);
				$this -> id = key($post);
				$this -> setTheme();
			}

			if(strstr($key,"selectadmin"))
			{
				$this -> id = key($post);
				$this -> setAdminTheme();
				$this -> refreshPage('admin');
			}
		}

		if(isset($_POST['submit_adminstyle']))
		{
 			$this -> id = $_POST['curTheme'];
			$this -> setAdminStyle();

		}

		if(isset($_POST['submit_style']))
		{
			$this -> id = $_POST['curTheme'];
			$this -> setStyle();

		}

	}

	function getThemes($mode=FALSE)
	{
		$themeArray = array();

		$tloop = 1;
		$handle = opendir(e_THEME);
		while (false !== ($file = readdir($handle)))
		{
		  	if ($file != "." && $file != ".." && $file != "CVS" && $file != "templates" && is_dir(e_THEME.$file) && is_readable(e_THEME.$file."/theme.php") )
		  	{
				if($mode == "id")
				{
					$themeArray[$tloop] = $file;
				}
				else
				{
					$themeArray[$file]['id'] = $tloop;
				}
				$tloop++;
				$STYLESHEET = FALSE;
				if(!$mode)
				{
					$handle2 = opendir(e_THEME.$file."/");
					while (false !== ($file2 = readdir($handle2))) // Read files in theme directory
				  	{
						if ($file2 != "." && $file2 != ".." && $file != "CVS" && !is_dir(e_THEME.$file."/".$file2))
						{
					  		$themeArray[$file]['files'][] = $file2;
					  		if(strstr($file2, "preview."))
					  		{
								$themeArray[$file]['preview'] = e_THEME.$file."/".$file2;
					  		}
						  	if(strstr($file2, "css") && !strstr($file2, "menu.css") && strpos($file2, "e_") !== 0 && strpos($file2, "admin_") !== 0)
						  	{
								/* get information string for css file */
								$fp=fopen(e_THEME.$file."/".$file2, "r");
								$cssContents = fread ($fp, filesize(e_THEME.$file."/".$file2));
								fclose($fp);
								$nonadmin = preg_match('/\* Non-Admin(.*?)\*\//', $cssContents) ? true : false;
								preg_match('/\* info:(.*?)\*\//', $cssContents, $match);
								$match[1]=varset($match[1],'');
								$themeArray[$file]['css'][] = array("name" => $file2, "info" => $match[1], "nonadmin" => $nonadmin);
								if($STYLESHEET)
								{
								  $themeArray[$file]['multipleStylesheets'] = TRUE;
								}
								else
								{
								  $STYLESHEET = TRUE;
								}
						  	}
 						}
	 				} // end while..

				  	closedir($handle2);

					// Load Theme information and merge with existing array. theme.xml (0.8 themes) is given priority over theme.php (0.7).
					if(in_array("theme.xml",$themeArray[$file]['files']) )
					{
		 	         	$themeArray[$file] = array_merge($themeArray[$file], $this->parse_theme_xml($file));
		    		}
					elseif(in_array("theme.php",$themeArray[$file]['files']))
					{
						$themeArray[$file] =  array_merge($themeArray[$file], $this->parse_theme_php($file));
		         	}
				}
		  	}
		}
		closedir($handle);
/*
    echo "<table><tr><td>";
     echo "<pre>";
		print_r($themeArray['jayya']);
		echo "</pre>";
    echo "</td><td>";
      echo "<pre>";
		print_r($themeArray['e107v4a']);
		echo "</pre>";

	echo "</td></tr></table>";*/

		return $themeArray;
	}

	function themeUpload()
	{
		if (!$_POST['ac'] == md5(ADMINPWCHANGE)) {
			exit;
		}
		global $ns;
		extract($_FILES);
		if(!is_writable(e_THEME))
		{
			$ns->tablerender(TPVLAN_16, TPVLAN_20);
		}
		else
		{
			require_once(e_HANDLER."upload_handler.php");
			$fileName = $file_userfile['name'][0];
			$fileSize = $file_userfile['size'][0];
			$fileType = $file_userfile['type'][0];

			if(strstr($file_userfile['type'][0], "gzip")) {
				$fileType = "tar";
			} else if (strstr($file_userfile['type'][0], "zip")) {
				$fileType = "zip";
			} else {
				$ns->tablerender(TPVLAN_16, TPVLAN_17);
				require_once("footer.php");
				exit;
			}

			if ($fileSize) {

				$uploaded = file_upload(e_THEME);

				$archiveName = $uploaded[0]['name'];


				if($fileType == "zip") {
					require_once(e_HANDLER."pclzip.lib.php");
					$archive = new PclZip(e_THEME.$archiveName);
					$unarc = ($fileList = $archive -> extract(PCLZIP_OPT_PATH, e_THEME, PCLZIP_OPT_SET_CHMOD, 0666));
				} else {
					require_once(e_HANDLER."pcltar.lib.php");
					$unarc = ($fileList = PclTarExtract($archiveName, e_THEME));
				}

				if(!$unarc) {
					if($fileType == "zip") {
					$error = TPVLAN_46." '".$archive -> errorName(TRUE)."'";
				} else {
					$error = TPVLAN_47.PclErrorString().", ".TPVLAN_48.intval(PclErrorCode());
					}
					$ns->tablerender(TPVLAN_16, TPVLAN_18." ".$archiveName." ".$error);
					require_once("footer.php");
					exit;
				}

				$folderName = substr($fileList[0]['stored_filename'], 0, (strpos($fileList[0]['stored_filename'], "/")));
				$ns->tablerender(TPVLAN_16, "<div class='center'>".TPVLAN_19."</div>");

				@unlink(e_THEME.$archiveName);
			}
		}
	}

	function showThemes($mode='main')
	{
		global $ns, $pref;

		echo "<div class='center'>
		<form enctype='multipart/form-data' method='post' action='".e_SELF."?".$mode."'>\n";


        if($mode == "main" || !$mode)  // Show Main Configuration
		{
			foreach($this -> themeArray as $key => $theme)
			{
				if($key == $pref['sitetheme'])
				{
					$text = $this -> renderTheme(1, $theme);
				}
			}

	   		$ns->tablerender(TPVLAN_26." :: ".TPVLAN_33, $text);
        }

        if($mode == "admin")  // Show Admin Configuration
		{

			foreach($this -> themeArray as $key => $theme)
			{
				if($key == $pref['admintheme'])
				{
					$text = $this -> renderTheme(2, $theme);
				}
			}
			$ns->tablerender(TPVLAN_26." :: ".TPVLAN_34, $text);
        }


        if($mode == "upload")  // Show Upload Form
		{
			if(!is_writable(e_THEME)) {
				$ns->tablerender(TPVLAN_16, TPVLAN_15);
				$text = "";
			}
			else
			{
			  require_once(e_HANDLER.'upload_handler.php');
			  $max_file_size = get_user_max_upload();

			  $text = "<div style='text-align:center'>
				<table style='".ADMIN_WIDTH."' class='fborder'>
				<tr>
				<td class='forumheader3' style='width: 50%;'>".TPVLAN_13."</td>
				<td class='forumheader3' style='width: 50%;'>
				<input type='hidden' name='MAX_FILE_SIZE' value='{$max_file_size}' />
				<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
				<input class='tbox' type='file' name='file_userfile[]' size='50' />
				</td>
				</tr>
				<tr>
				<td colspan='2' style='text-align:center' class='forumheader'>";

                $text .= $this->frm->admin_button('upload', TPVLAN_14, 'submit');
				$text .= "
 				</td>
				</tr>
				</table>
				<br /></div>\n";
			}

			$ns->tablerender(TPVLAN_26." :: ".TPVLAN_38, $text);
		}


        if($mode == "choose")  // Show All Themes
		{
			$text = "";
			foreach($this -> themeArray as $key => $theme)
			{
			  //	if($key != $pref['admintheme'] && $key != $pref['sitetheme'])
			  //	{
					$text .= $this -> renderTheme(FALSE, $theme);
			  //	}
			}

			$ns->tablerender(TPVLAN_26." :: ".TPVLAN_39, $text);
		}


		echo "</form>\n</div>\n";
	}

    function renderThemeInfo($theme)
	{

		// TO-DO : This SHOULD be loaded by ajax before release.

        global $pref;
		$author = ($theme['email'] ? "<a href='mailto:".$theme['email']."' title='".$theme['email']."'>".$theme['author']."</a>" : $theme['author']);
		$website = ($theme['website'] ? "<a href='".$theme['website']."' rel='external'>".$theme['website']."</a>" : "");
		$preview = "<a href='".e_BASE."news.php?themepreview.".$theme['id']."' title='".TPVLAN_9."' >".($theme['preview'] ? "<img src='".$theme['preview']."' style='border: 1px solid #000;width:200px' alt='' />" : "<img src='".e_IMAGE_ABS."admin_images/nopreview.png' style='border:0px' title='".TPVLAN_12."' alt='' />")."</a>";


    	$text = "<div style='font-weight:bold;margin-bottom:10px'>".TPVLAN_7."</div>
			<table class='adminlist' cellpadding='4'>";
        $text .= $author ? "<tr><td style='vertical-align:top; width:24%'><b>".TPVLAN_4."</b>:</td><td style='vertical-align:top'>".$author."</td></tr>" : "";
		$text .= $website ? "<tr><td style='vertical-align:top; width:24%'><b>".TPVLAN_5."</b>:</td><td style='vertical-align:top'>".$website."</td></tr>" : "";
		$text .= $theme['date'] ? "<tr><td style='vertical-align:top; width:24%'><b>".TPVLAN_6."</b>:</td><td style='vertical-align:top'>".$theme['date']."</td></tr>" : "";
        $text .= "<tr><td style='vertical-align:top; width:24%'><b>".TPVLAN_49."</b>:</td>
			<td style='vertical-align:top'>XHTML ";
        $text .= ($theme['xhtmlcompliant']) ? ADMIN_TRUE_ICON : ADMIN_FALSE_ICON;
		$text .= "  &nbsp;&nbsp;  CSS ";
		$text .= ($theme['csscompliant']) ? ADMIN_TRUE_ICON : ADMIN_FALSE_ICON;
		$text .= "</td></tr>";

        if($theme['layouts'])  // New in 0.8    WORK IN PROGRESS ----
		{
            $itext .= "<tr>
					<td style='vertical-align:top; width:24%'><b>".TPVLAN_50."</b>:</td>
					<td style='vertical-align:top'><table class='fborder' style='margin-left:0px;margin-right:auto' >
						<tr>";
                        $itext .= ($mode == 1) ? "<td class='fcaption' style='text-align:center;vertical-align:top;'>Default</td>" : "";
						$itext .= "
							<td class='fcaption'>Title</td>
							<td class='fcaption'>Requirements</td>
							<td class='fcaption' style='text-align:center;width:100px'>Menu Preset</td>
						</tr>\n";

			foreach($theme['layouts'] as $key=>$val)
		 	{
                $itext .= "
				<tr>";
				if($mode == 1)
				{
					if(!$pref['sitetheme_deflayout'])
					{
						$pref['sitetheme_deflayout'] = ($val['@attributes']['default']=='true') ? $key : "";
					  //	echo "------------- NODEFAULT";
					}
					$itext .= "
	                <td style='vertical-align:top width:auto;text-align:center'>
						<input type='radio' name='layout_default' value='{$key}' ".($pref['sitetheme_deflayout']==$key ? " checked='checked'" : "")." />
					</td>";
				}

				$itext .= "<td style='vertical-align:top'>";
				$itext .= ($val['@attributes']['previewFull']) ? "<a href='".e_THEME_ABS.$theme['path']."/".$val['@attributes']['previewFull']."' >" : "";
				$itext .= $val['@attributes']['title'];
				$itext .= ($val['@attributes']['previewFull']) ? "</a>" : "";
                $itext .= ($pref['sitetheme_deflayout'] == $key) ? " (default)" : "";
				$itext .= "</td>
					<td style='vertical-align:top'>".$val['@attributes']['plugins']."&nbsp;</td>
                    <td style='vertical-align:top;text-align:center'>";
                    $itext .= ($val['menuPresets']) ? ADMIN_TRUE_ICON: "&nbsp;";
					$itext .= "</td>
				</tr>";
			}

			$itext .= "</table></td></tr>";
		}

        $text .= "<tr><td><b>".TPVLAN_22.": </b></td><td colspan='2'>";
		foreach($theme['css'] as $val)
		{
        	$text .= $val['name']."<br />";
		}
		$text .= "</td></tr>";

        $text .= $itext."</table>";
        $text .= "<div class='right'><a href='#themeInfo_".$theme['id']."' class='e-expandit'>Close</a></div>";
        return $text;
	}

	function renderThemeConfig()  // process custom theme configuration - TODO.
	{
   			$confile = e_THEME.$this->id."/".$this->id."_config.php";
	       	if(is_readable($confile))
		   	{
	       		include_once($confile);
				if(function_exists($this->id."_config"))
				{
	            	$var = call_user_func($this->id."_config");
                    foreach($var as $val)
					{
	                	$text .= "<tr><td><b>".$val['caption']."</b>:</td><td colspan='2'>".$val['html']."</td></tr>";
					}
					return $text;
				}
	  	   	}
	}

	function setThemeConfig()
	{
		$confile = e_THEME.$this->id."/".$this->id."_config.php";

      	include($confile);
		if(function_exists($this->id."_process"))
		{
        	$text = call_user_func($this->id."_process");
	 	}

		return $text;
	}

	function renderTheme($mode=FALSE, $theme)
	{

		/*
		mode = 0 :: normal
		mode = 1 :: selected site theme
		mode = 2 :: selected admin theme
		*/

		global $ns, $pref;



		$author = ($theme['email'] ? "<a href='mailto:".$theme['email']."' title='".$theme['email']."'>".$theme['author']."</a>" : $theme['author']);
		$website = ($theme['website'] ? "<a href='".$theme['website']."' rel='external'>".$theme['website']."</a>" : "");
		$preview = "<a href='".e_BASE."news.php?themepreview.".$theme['id']."' title='".TPVLAN_9."' >".($theme['preview'] ? "<img src='".$theme['preview']."' style='border: 1px solid #000;width:200px' alt='' />" : "<img src='".e_IMAGE_ABS."admin_images/nopreview.png' style='border:0px' title='".TPVLAN_12."' alt='' />")."</a>";

		$previewbutton = (!$mode ? "<input class='button' type='submit' name='preview_".$theme['id']."' value='".TPVLAN_9."' /> " : "");

		$main_icon 		= ($pref['sitetheme'] != $theme['path']) ? "<input style='vertical-align:middle;'  type='image' src='".e_IMAGE_ABS."admin_images/main_16.png'  name='selectmain[".$theme['id']."]' alt='' title=\"".TPVLAN_10."\" />\n" : ADMIN_TRUE_ICON;
     	$info_icon 		= "<a href='#themeInfo_".$theme['id']."' class='e-expandit' style='height:16px' title='Click to select columns to display'><img src='".e_IMAGE_ABS."admin_images/info_16.png' alt='' title=\"".TPVLAN_7."\" style='border:0px; vertical-align:middle;' /></a>\n";
        $preview_icon 	= "<input style='vertical-align:middle;' type='image' src='".e_IMAGE_ABS."admin_images/search_16.png'  name=\"preview[".$theme['id']."]\" title='".TPVLAN_9." #".$theme['id']."' />\n";
        $admin_icon 	= ($pref['admintheme'] != $theme['path']) ? "<input style='vertical-align:middle;' type='image' src='".e_IMAGE_ABS."e107_icon_16.png'  name='selectadmin[".$theme['id']."]' alt='' title=\"".TPVLAN_32."\" />\n" : ADMIN_TRUE_ICON;

        $newpreview 	= "<a href='".e_BASE."news.php?themepreview.".$theme['id']."' title='".TPVLAN_9."' >".($theme['preview'] ? "<img src='".$theme['preview']."' style='border: 0px;width:200px;height:160px;' alt='' />" : "<img src='".e_IMAGE_ABS."admin_images/nopreview.png' style='width:200px;height:160px;border:0px' title='".TPVLAN_12."' alt='' />")."</a>";


		if(!$mode) // Choose a Theme to Install.
		{
			// styles NEED to be put into style.css

			$borderStyle = (($pref['sitetheme'] == $theme['path']) || ($pref['admintheme'] == $theme['path'])) ? "border:1px solid black" : "border:1px dotted silver;background-color:#DDDDDD";
        	$text = "<div class='block-text' style='margin:5px;".$borderStyle.";float:left;width:202px;height:160px'>
                     <div style='height:130px;overflow:hidden;border:1px solid black;margin-bottom:10px'>".$newpreview."</div>
                     <div class='mediumtext' style='width:60%;float:left;font-weight:bold'>".$theme['name']." ".$theme['version']."</div>
					 <div style='text-align:right;float:right;width:40%;height:16px'>\n\n\n".$main_icon.$admin_icon.$info_icon.$preview_icon."\n\n</div>
                     <div id='themeInfo_".$theme['id']."' class='e-hideme col-selection' style='position:relative;top:30px;width:480px'>\n".$this->renderThemeInfo($theme)."</div>

			</div>";
            return $text;
		}

        	$this->id = $theme['path'];

		$text = "<div style='text-align:center;margin-left:auto;margin-right:auto'>
		<table class='adminlist'>

		<tr><td colspan='2'><h1>".$theme['name']."</h1></td></tr>
		<tr><td><b>".TPVLAN_11."</b></td><td>".$theme['version']."</td>
		<td class='first last' rowspan='6' style='text-align:center;width:25%'>$newpreview </td></tr>";

		$text .= "<tr><td style='vertical-align:top; width:25%'><b>".TPVLAN_4."</b>:</td><td style='vertical-align:top'>".$author."</td></tr>";
		$text .= "<tr><td style='vertical-align:top; width:25%'><b>".TPVLAN_5."</b>:</td><td style='vertical-align:top'>".$website."</td></tr>";
		$text .= "<tr><td style='vertical-align:top; width:25%'><b>".TPVLAN_6."</b>:</td><td style='vertical-align:top'>".$theme['date']."</td></tr>";
        $text .= "<tr><td style='vertical-align:top; width:25%'><b>".TPVLAN_49."</b>:</td>
			<td style='vertical-align:top'>XHTML ";
        $text .= ($theme['xhtmlcompliant']) ? ADMIN_TRUE_ICON : "X";
		$text .= "  &nbsp;&nbsp;  CSS ";
		$text .= ($theme['csscompliant']) ? ADMIN_TRUE_ICON : "X";
		$text .= "</td></tr>";
		$text .= "<tr><td style='vertical-align:top; width:25%'><b>".TPVLAN_7."</b>:</td><td style='vertical-align:top'>".$theme['info']."</td></tr>
		";

		if($mode == 1)
		{
                $text .= "
				<tr>
					<td style='vertical-align:top; width:24%;'><b>".TPVLAN_30."</b></td>
					<td colspan='2' style='vertical-align:top width:auto;'>
					<input type='radio' name='image_preload' value='1'".($pref['image_preload'] ? " checked='checked'" : "")." /> ".TPVLAN_28."&nbsp;&nbsp;
					<input type='radio' name='image_preload' value='0'".(!$pref['image_preload'] ? " checked='checked'" : "")." /> ".TPVLAN_29."
					</td>
				</tr>";
		}

        if($theme['layouts'] && $mode==1)  // New in 0.8   ----
		{
            $itext .= "<tr>
					<td style='vertical-align:top; width:24%'><b>".TPVLAN_50."</b>:</td>
					<td colspan='2' style='vertical-align:top'>
					<table class='adminlist' style='auto;width:100%' >
						<tr>";
                        $itext .= ($mode == 1) ? "<td class='fcaption' style='width:15%;text-align:center;vertical-align:top;'>".TPVLAN_55."</td>" : "";
						$itext .= "
							<td class='fcaption' style='width:20%'>".TPVLAN_52."</td>
							<td class='fcaption' style='width:35%'>".TPVLAN_53."</td>
							<td class='fcaption' style='text-align:center;width:100px'>".TPVLAN_54."</td>
						</tr>\n";


						foreach($theme['layouts'] as $key=>$val)
					 	{
			                $itext .= "
							<tr>";
							if($mode == 1)
							{
								if(!$pref['sitetheme_deflayout'])
								{
									$pref['sitetheme_deflayout'] = ($val['@attributes']['default']=='true') ? $key : "";
						 		}
								$itext .= "<td style='vertical-align:top width:auto;text-align:center'>\n";

								$itext .= "
									<input type='radio' name='layout_default' value='{$key}' ".($pref['sitetheme_deflayout']==$key ? " checked='checked'" : "")." />
								</td>";
							}

							$itext .= "<td style='vertical-align:top'>";
							$itext .= ($val['@attributes']['previewFull']) ? "<a href='".e_THEME_ABS.$theme['path']."/".$val['@attributes']['previewFull']."' >" : "";
							$itext .= $val['@attributes']['title'];
							$itext .= ($val['@attributes']['previewFull']) ? "</a>" : "";
			                $itext .= ($pref['sitetheme_deflayout'] == $key) ? " (default)" : "";
							$itext .= "</td>
								<td style='vertical-align:top'>".$this->renderPlugins($val['@attributes']['plugins'])."&nbsp;</td>
			                    <td style='vertical-align:top;text-align:center'>";
			                    $itext .= ($val['menuPresets']) ? ADMIN_TRUE_ICON : "&nbsp;";
								$itext .= "</td>
							</tr>";
						}

		   $itext .=  "</table></td></tr>";
		}


  //		$itext .= !$mode ? "<tr><td style='vertical-align:top;width:24%'><b>".TPVLAN_8."</b>:</td><td style='vertical-align:top'>".$previewbutton.$selectmainbutton.$selectadminbutton."</td></tr>" : "";


        $text .= $itext;

		if(array_key_exists("multipleStylesheets", $theme) && $mode)
		{
			$text .= "
				<tr><td style='vertical-align:top;'><b>".TPVLAN_22.":</b></td><td colspan='2' style='vertical-align:top'>
				<table class='adminlist' style='width:100%' >
				<tr>
                	<td class='fcaption center' style='width:15%'>".TPVLAN_55."</td>
			  		<td class='fcaption' style='width:20%'>".TPVLAN_52."</td>
					<td class='fcaption left'>".TPVLAN_7."</td>
				</tr>\n";

				foreach($theme['css'] as $css)
				{
                    $text .= "<tr>\n";
					if($mode == 2)
					{
						if (!$css['nonadmin']) {
							$text .= "
							<td class='center'>
							<input type='radio' name='admincss' value='".$css['name']."' ".($pref['admincss'] == $css['name'] || (!$pref['admincss'] && $css['name'] == "style.css") ? " checked='checked'" : "")." />
							</td>
							<td>".$css['name']."</td>
							<td>".($css['info'] ? $css['info'] : ($css['name'] == "style.css" ? TPVLAN_23 : TPVLAN_24))."</td>\n";
						}
					}

					if($mode == 1)
					{
						$text .= "
						<td class='center'>
						<input type='radio' name='themecss' value='".$css['name']."' ".($pref['themecss'] == $css['name'] || (!$pref['themecss'] && $css['name'] == "style.css") ? " checked='checked'" : "")." />
						</td>
						<td>".$css['name']."
						</td>
						<td>".($css['info'] ? $css['info'] : ($css['name'] == "style.css" ? TPVLAN_23 : TPVLAN_24))."</td>\n";
					}
					$text .= "</tr>";
				}

				$text .= "</table></td></tr>";
		}



		if($mode == 2)
		{

			$astext = "";
			require_once(e_HANDLER."file_class.php");
			$file = new e_file;

			$adminstyles = $file -> get_files(e_ADMIN."includes");

				$astext = "\n<select id='mode2' name='adminstyle' class='tbox'>\n";

				foreach($adminstyles as $as)
				{
					$style = str_replace(".php", "", $as['fname']);
					$astext .= "<option value='{$style}'".($pref['adminstyle'] == $style ? " selected='selected'" : "").">".$style."</option>\n";
				}
				$astext .= "</select>";

			$text .= "
			<tr>
				<td style='vertical-align:top'><b>".TPVLAN_41.":</b></td>
				<td style='vertical-align:top'>".$astext."</td>
			</tr>
			\n";
		}


        if($mode == 1)
		{
        	$text .= $this->renderThemeConfig();
        }

        $text .= "</table>


		   		<div class='center buttons-bar'>";

                  if($mode == 2) // admin
				  {
				  	$mainid = "selectmain[".$theme['id']."]";
				  	$text .= $this->frm->admin_button('submit_adminstyle', TPVLAN_35, 'update');
					$text .= $this->frm->admin_button($mainid, TPVLAN_10, 'submit');

				  }
				  else   // main
				  {
				  	$adminid = "selectadmin[".$theme['id']."]";
				  	$text .= $this->frm->admin_button('submit_style', TPVLAN_35, 'update');
                    $text .= $this->frm->admin_button($adminid, TPVLAN_32, 'submit');
				  }

				  $text .= "<input type='hidden' name='curTheme' value='".$theme['path']."' />";

   		$text .= "
				</div>



		</div>\n";

		return $text;
	}

    function renderPlugins($val)
	{
		$tmp = explode(",",$val);
		$tmp = array_filter($tmp);

		foreach($tmp as $plug)
		{
			$plug = trim($plug);
         	if(plugInstalled($plug))
			{
            	$text .= ADMIN_TRUE_ICON.$plug;
			}
			else
			{
               $text .= ADMIN_FALSE_ICON."<a href='".e_ADMIN."plugin.php'>".$plug."</a>";
			}
            $text .= "&nbsp;";
		}

        return $text;
	}

	function refreshPage($page=e_QUERY)
	{
           header("Location: ".e_SELF."?".$page);
		   exit;
	}

	function themePreview()
	{
		echo "<script type='text/javascript'>document.location.href='".e_BASE."index.php?themepreview.".$this -> id."'</script>\n";
		exit;
	}

	function showPreview()
	{
		include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_theme.php");
		$text = "<br /><div class='indent'>".TPVLAN_1.".</div><br />";
		global $ns;
		$ns->tablerender(TPVLAN_2, $text);
	}

	function setTheme()
	{
		global $pref, $e107cache, $ns, $sql;
		$themeArray = $this -> getThemes("id");

		$pref['sitetheme'] = $themeArray[$this -> id];
		$pref['themecss'] ='style.css';
        $pref['sitetheme_deflayout'] = $this->findDefault($themeArray[$this -> id]);
		$pref['sitetheme_layouts'] = is_array($this->themeArray[$pref['sitetheme']]['layouts']) ? $this->themeArray[$pref['sitetheme']]['layouts'] : array();
        $pref['sitetheme_custompages'] = $this->themeArray[$pref['sitetheme']]['custompages'];

        $sql -> db_Delete("menus", "menu_layout !='' ");

		$e107cache->clear_sys();
	 	save_prefs();

		$this->theme_adminlog('01',$pref['sitetheme'].', '.$pref['themecss']);
		$ns->tablerender("Admin Message", "<br /><div style='text-align:center;'>".TPVLAN_3." <b>'".$themeArray[$this -> id]."'</b>.</div><br />");
	}

	function findDefault($theme)
	{
		if(varset($_POST['layout_default']))
		{
        	return $_POST['layout_default'];
		}

    	$l = $this->themeArray[$theme];

		foreach($l['layouts'] as $key=>$val)
		{
        	if(isset($val['@attributes']['default']) && ($val['@attributes']['default'] == "true"))
			{
            	return $key;
			}
		}
	}

	function setAdminTheme()
	{
		global $pref, $e107cache, $ns;
		$themeArray = $this -> getThemes("id");
		$pref['admintheme'] = $themeArray[$this -> id];
		$pref['admincss'] = file_exists(THEME.'admin_style.css') ? 'admin_style.css' : 'style.css';
		$e107cache->clear_sys();
		save_prefs();
		$this->theme_adminlog('02',$pref['admintheme'].', '.$pref['admincss']);
		$ns->tablerender("Admin Message", "<br /><div style='text-align:center;'>".TPVLAN_40." <b>'".$themeArray[$this -> id]."'</b>.</div><br />");
      //  $this->showThemes('admin');
	}

	function setStyle()
	{
		global $pref, $e107cache, $ns, $sql;
		$pref['themecss'] = $_POST['themecss'];
		$pref['image_preload'] = $_POST['image_preload'];
		$pref['sitetheme_deflayout'] = $_POST['layout_default'];

		$e107cache->clear_sys();
		save_prefs();
        $custom_message = $this -> setThemeConfig();
		$this->theme_adminlog('03',$pref['image_preload'].', '.$pref['themecss']);
		$ns->tablerender(TPVLAN_36, "<br /><div style='text-align:center;'>".TPVLAN_37.".<br />".$custom_message."</div><br />");
	}

	function setAdminStyle()
	{
		global $pref, $e107cache, $ns;
		$pref['admincss'] = $_POST['admincss'];
		$pref['adminstyle'] = $_POST['adminstyle'];


		$e107cache->clear_sys();
		save_prefs();
		$this->theme_adminlog('04',$pref['adminstyle'].', '.$pref['admincss']);
		$ns->tablerender(TPVLAN_36, "<br /><div style='text-align:center;'>".TPVLAN_43.".</div><br />");
	}


	// Log event to admin log
	function theme_adminlog($msg_num='00', $woffle='')
	{
		global $pref, $admin_log;
		//  if (!varset($pref['admin_log_log']['admin_banlist'],0)) return;
		$admin_log->log_event('THEME_'.$msg_num,$woffle,E_LOG_INFORMATIVE,'');
	}

	function parse_theme_php($path)
	{
		$fp=fopen(e_THEME.$path."/theme.php", "r");
		$themeContents = fread ($fp, filesize(e_THEME.$path."/theme.php"));
		fclose($fp);


		preg_match('/themename(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		$themeArray['name'] = varset($match[3],'');
		preg_match('/themeversion(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		$themeArray['version'] = varset($match[3],'');
		preg_match('/themeauthor(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		$themeArray['author'] = varset($match[3],'');
		preg_match('/themeemail(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		$themeArray['email'] = varset($match[3],'');
		preg_match('/themewebsite(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		$themeArray['website'] = varset($match[3],'');
		preg_match('/themedate(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		$themeArray['date'] = varset($match[3],'');
		preg_match('/themeinfo(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		$themeArray['info'] = varset($match[3],'');
        preg_match('/xhtmlcompliant(\s*?=\s*?)(\S*?);/si', $themeContents, $match);
		$xhtml = strtolower($match[2]);
		$themeArray['xhtmlcompliant'] = ($xhtml == "true" ? true : false);

		preg_match('/csscompliant(\s*?=\s*?)(\S*?);/si', $themeContents, $match);
		$css = strtolower($match[2]);
		$themeArray['csscompliant'] = ($css == "true" ? true : false);

/*        preg_match('/CUSTOMPAGES(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		$themeArray['custompages'] = array_filter(explode(" ",$match[3]));*/

        $themeContentsArray = explode("\n",$themeContents);

  		if (!$themeArray['name'])
		{
			unset($themeArray);
		}
  //      echo " <hr>".$path."<hr>";

		foreach($themeContentsArray as $line)
		{
        	if(strstr($line,"CUSTOMPAGES"))
			{
				eval(str_replace("$","\\$",$line));
			}
		}
        if(is_array($CUSTOMPAGES))
		{
             foreach($CUSTOMPAGES as $key=>$val)
			 {
             	$themeArray['custompages'][$key] = explode(" ",$val);
			 }
		}
		elseif($CUSTOMPAGES)
		{
        	$themeArray['custompages']['no_array'] = explode(" ",$CUSTOMPAGES);
		}

		$themeArray['path'] = $path;

    	return $themeArray;
	}

    function parse_theme_xml($path)
	{
		global $tp;
	  //	loadLanFiles($path, 'admin');					// Look for LAN files on default paths
		require_once(e_HANDLER.'xml_class.php');
		$xml = new xmlClass;
		$vars = $xml->loadXMLfile(e_THEME.$path.'/theme.xml', true, true);

		$vars['name']					= varset($vars['@attributes']['name']);
		$vars['version']				= varset($vars['@attributes']['version']);
		$vars['date']					= varset($vars['@attributes']['date']);
		$vars['compatibility']		= varset($vars['@attributes']['compatibility']);


		$vars['email']	 				= varset($vars['author']['@attributes']['email']);
      $vars['website'] 				= varset($vars['author']['@attributes']['url']);
		$tmp								= varset($vars['author']['@attributes']['name']);
		$vars['author'] = $tmp;


		$vars['info'] 					= $vars['description'];
		$vars['xhtmlcompliant'] 	= (strtolower($vars['compliance']['@attributes']['xhtml']) == 'true' ? 1 : 0);
		$vars['csscompliant'] 		= (strtolower($vars['compliance']['@attributes']['css']) == 'true' ? 1 : 0);
		$vars['path']					= $path;
		$vars['@attributes']['default'] = (strtolower($vars['@attributes']['default'])=='true') ? 1 : 0;

		unset($vars['authorEmail'],$vars['authorUrl'],$vars['xhtmlCompliant'],$vars['cssCompliant'],$vars['description']);

		// Compile layout information into a more usable format.

		foreach($vars['layouts'] as $layout)
		{
			if(is_array($layout[0]))
			{
				foreach($layout as $key=>$val)
				{

					$name = $val['@attributes']['name'];
					unset($val['@attributes']['name']);
					$lays[$name] = $val;
					if(isset($val['customPages']))
					{
						$custom[$name] =  array_filter(explode(" ",$val['customPages']));
					}
				}
			}
			else
			{
                $name = $layout['@attributes']['name'];
			 	unset($layout['@attributes']['name']);
			  	$lays[$name] = $layout;
                if(isset($val['customPages']))
		  		{
					$custom[$name] =  array_filter(explode(" ",$layout['customPages']));
				}
			}
        }

        $vars['layouts'] = $lays;
        $vars['path'] = $path;
		$vars['custompages'] = $custom;

	  	return $vars;
	}

}
?>