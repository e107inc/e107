<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (c) e107 Inc. 2001-2009
|     http://e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/theme_handler.php,v $
|     $Revision: 1.45 $
|     $Date: 2009-08-17 12:48:52 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

class themeHandler{

	var $themeArray;
	var $action;
	var $id;
	var $frm;
	var $fl;
	var $themeConfigObj = null;

	/* constructor */

	function themeHandler() {

		global $emessage, $e107cache, $pref;

   		require_once(e_HANDLER."form_handler.php");
		$this->frm = new e_form(); //enable inner tabindex counter

		require_once(e_HANDLER."file_class.php");
        $this->fl = new e_file;


		if (isset($_POST['upload']))
		{
			$this -> themeUpload();
		}

		$this -> themeArray = $this -> getThemes();

   //     print_a($this -> themeArray);

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
		 	$this -> SetCustomPages($_POST['custompages']);

		}

		if(isset($_POST['installplugin']))
		{
			$key = key($_POST['installplugin']);

			include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_plugin.php");
        	require_once(e_HANDLER."plugin_class.php");

			$eplug = new e107plugin;
            $message = $eplug->install_plugin($key);
		    $emessage->add($message, E_MESSAGE_SUCCESS);
		}

		if(isset($_POST['setMenuPreset']))
		{
        	$key = key($_POST['setMenuPreset']);
           	include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_menus.php");
        	require_once(e_HANDLER."menumanager_class.php");
			$men = new e_menuManager();
			$men->curLayout = $key;
			$men->dbLayout = ($men->curLayout !=$pref['sitetheme_deflayout']) ? $men->curLayout : "";  //menu_layout is left blank when it's default.

			if($areas = $men->menuSetPreset())
			{
            	foreach($areas as $val)
				{
                 	$ar[$val['menu_location']][] = $val['menu_name'];
				}
				foreach($ar as $k=>$v)
				{
                	$message .= MENLAN_14." ".$k." : ".implode(", ",$v)."<br />";
				}

				$emessage->add(MENLAN_43." : ".$key."<br />".$message, E_MESSAGE_SUCCESS);
			}

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
					$themeArray[$file] = $this->getThemeInfo($file);
					$themeArray[$file]['id'] = $tloop;
				}
				$tloop++;
		  	}
		}
	 	closedir($handle);

  /*

		 	echo "<pre>";
       print_r($themeArray);
   	 echo "</pre>";*/

		return $themeArray;
	}



    function getThemeInfo($file)
	{
    	$STYLESHEET = FALSE;

     	$reject = array('$.','$..','/','CVS','thumbs.db','*._$', 'index', 'null*','e_*');
                  	$handle2 = $this->fl->get_files(e_THEME.$file."/", ".php|.css|.xml|preview.jpg|preview.png",$reject,1);
                    foreach($handle2 as $fln)
		   	   		{
                        $file2 = str_replace(e_THEME.$file."/","",$fln['path']).$fln['fname'];

					  		$themeArray[$file]['files'][] = $file2;
					  		if(strstr($file2, "preview."))
					  		{
								$themeArray[$file]['preview'] = e_THEME.$file."/".$file2;
					  		}


                             // ----------------  get information string for css file


						  	if(strstr($file2, "css") && !strstr($file2, "menu.css") && strpos($file2, "e_") !== 0)
						  	{

								if($fp=fopen(e_THEME.$file."/".$file2, "r"))
								{
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





	 				}    // end while..


                 //   echo "<hr />";
			   //	  	closedir($handle2);

					// Load Theme information and merge with existing array. theme.xml (0.8 themes) is given priority over theme.php (0.7).
					if(in_array("theme.xml",$themeArray[$file]['files']) )
					{
		 	         	$themeArray[$file] = array_merge($themeArray[$file], $this->parse_theme_xml($file));
		    		}
					elseif(in_array("theme.php",$themeArray[$file]['files']))
					{
						$themeArray[$file] =  array_merge($themeArray[$file], $this->parse_theme_php($file));
		         	}




          return $themeArray[$file];


	}






	function themeUpload()
	{
		if (!$_POST['ac'] == md5(ADMINPWCHANGE)) {
			exit;
		}
		global $ns, $emessage;
		extract($_FILES);
		if(!is_writable(e_THEME))
		{
		 //	$ns->tablerender(TPVLAN_16, TPVLAN_20);
		    $emessage->add(TPVLAN_20, E_MESSAGE_INFO);
			return FALSE;
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
				$emessage->add(TPVLAN_17, E_MESSAGE_ERROR);
			//	$ns->tablerender(TPVLAN_16, TPVLAN_17);
			 //	require_once("footer.php");
				return FALSE;
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

				if(!$unarc)
				{
					if($fileType == "zip")
					{
						$error = TPVLAN_46." '".$archive -> errorName(TRUE)."'";
					}
					else
					{
						$error = TPVLAN_47.PclErrorString().", ".TPVLAN_48.intval(PclErrorCode());
					}

					$emessage->add(TPVLAN_18." ".$archiveName." ".$error, E_MESSAGE_ERROR);
				  //	$ns->tablerender(TPVLAN_16, TPVLAN_18." ".$archiveName." ".$error);
					return FALSE;
				}

				$folderName = substr($fileList[0]['stored_filename'], 0, (strpos($fileList[0]['stored_filename'], "/")));
				$emessage->add(TPVLAN_19, E_MESSAGE_SUCCESS);

				if(varset($_POST['setUploadTheme']))
				{
					$themeArray = $this->getThemes();
					$this->id = $themeArray[$folderName]['id'];
   				   	$this->setTheme();

				}

		 //		$ns->tablerender(TPVLAN_16, "<div class='center'>".TPVLAN_19."</div>");

				@unlink(e_THEME.$archiveName);
			}
		}
	}

	function showThemes($mode='main')
	{
		global $ns, $pref, $emessage;

		echo "<div>
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

	   		$ns->tablerender(TPVLAN_26." :: ".TPVLAN_33, $emessage->render(). $text);
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
			$ns->tablerender(TPVLAN_26." :: ".TPVLAN_34, $emessage->render(). $text);
        }


        if($mode == "upload")  // Show Upload Form
		{
        	$this -> renderUploadForm();
		}


        if($mode == "choose")  // Show All Themes
		{
			$text = "";
			foreach($this -> themeArray as $key => $theme)
			{
				$text .= $this -> renderTheme(FALSE, $theme);
			}
            $text .= "<div class='clear'>&nbsp;</div>";
			$ns->tablerender(TPVLAN_26." :: ".TPVLAN_39, $emessage->render().$text);
		}


		echo "</form>\n</div>\n";
	}


    function renderUploadForm()
	{
    	global $sql,$ns, $emessage;

    		if(!is_writable(e_THEME)) {
				$ns->tablerender(TPVLAN_16, TPVLAN_15);
				$text = "";
			}
			else
			{
			  require_once(e_HANDLER.'upload_handler.php');
			  $max_file_size = get_user_max_upload();

			  $text = "
			  	<div style='text-align:center'>
				<table cellpadding='0' cellspacing='0' class='adminform'>
					<colgroup span='2'>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
				<tr>
				<td>".TPVLAN_13."</td>
				<td>
				<input type='hidden' name='MAX_FILE_SIZE' value='{$max_file_size}' />
				<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
				<input class='tbox' type='file' name='file_userfile[]' size='50' />
				</td>
				</tr>
                <tr>
				<td>".TPVLAN_10."</td>
				<td>
                <input type='checkbox' name='setUploadTheme' value='1' />
				</td>
				</tr>
				</table>
				<div class='buttons-bar center'>";

				$text .= $this->frm->admin_button('upload', TPVLAN_14, 'submit');

                $text .= "
				</div>
				</div>\n";
			}

			$ns->tablerender(TPVLAN_26." :: ".TPVLAN_38,$emessage->render(). $text);
	}






    function renderThemeInfo($theme)
	{

		// TO-DO : This SHOULD be loaded by ajax before release.

        global $pref;
		$author = ($theme['email'] ? "<a href='mailto:".$theme['email']."' title='".$theme['email']."'>".$theme['author']."</a>" : $theme['author']);
		$website = ($theme['website'] ? "<a href='".$theme['website']."' rel='external'>".$theme['website']."</a>" : "");
		$preview = "<a href='".e_BASE."news.php?themepreview.".$theme['id']."' title='".TPVLAN_9."' >".($theme['preview'] ? "<img src='".$theme['preview']."' style='border: 1px solid #000;width:200px' alt='' />" : "<img src='".e_IMAGE_ABS."admin_images/nopreview.png' title='".TPVLAN_12."' alt='' />")."</a>";


    	$text = "<div style='font-weight:bold;margin-bottom:10px'>".TPVLAN_7."</div>
			<table class='adminlist' cellpadding='0' cellspacing='0'>";
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

    function loadThemeConfig()
	{
		$confile = e_THEME.$this->id."/".$this->id."_config.php";

		if(($this->themeConfigObj === null) && is_readable($confile))
		{

			include($confile);
            $className = 'theme_'.$this->id;
			if(class_exists($className))
			{
				$this->themeConfigObj = new $className();
			}
			else
			{
            	$this->themeConfigObj = FALSE;
			}
        }

	}



	function renderThemeConfig()  // process custom theme configuration - TODO.
	{
			global $frm;

            $this -> loadThemeConfig();

			if($this->themeConfigObj)
			{
	        	$var = call_user_method("config",$this->themeConfigObj);
                foreach($var as $val)
				{
	            	$text .= "<tr><td><b>".$val['caption']."</b>:</td><td colspan='2'>".$val['html']."</td></tr>";
				}
				return $text;
			}

	}


	function renderThemeHelp()
	{
		if($this->themeConfigObj)
	   	{
			return call_user_method("help",$this->themeConfigObj);
		}
	}



	function setThemeConfig()
	{
		global $theme_pref;
        $this -> loadThemeConfig();

        if($this->themeConfigObj)
	   	{
			return call_user_method("process",$this->themeConfigObj);
		}
	}

	function renderTheme($mode=FALSE, $theme)
	{

		/*
		mode = 0 :: normal
		mode = 1 :: selected site theme
		mode = 2 :: selected admin theme
		*/

		global $ns, $pref, $frm;



		$author = ($theme['email'] ? "<a href='mailto:".$theme['email']."' title='".$theme['email']."'>".$theme['author']."</a>" : $theme['author']);
		$website = ($theme['website'] ? "<a href='".$theme['website']."' rel='external'>".$theme['website']."</a>" : "");
		$preview = "<a href='".e_BASE."news.php?themepreview.".$theme['id']."' title='".TPVLAN_9."' >".($theme['preview'] ? "<img src='".$theme['preview']."' style='border: 1px solid #000;width:200px' alt='' />" : "<img src='".e_IMAGE_ABS."admin_images/nopreview.png' title='".TPVLAN_12."' alt='' />")."</a>";

		$previewbutton = (!$mode ? "<input class='button' type='submit' name='preview_".$theme['id']."' value='".TPVLAN_9."' /> " : "");

		$main_icon 		= ($pref['sitetheme'] != $theme['path']) ? "<input style='vertical-align:middle;'  type='image' src='".e_IMAGE_ABS."admin_images/main_16.png'  name='selectmain[".$theme['id']."]' alt='' title=\"".TPVLAN_10."\" />\n" : ADMIN_TRUE_ICON;
     	$info_icon 		= "<a href='#themeInfo_".$theme['id']."' class='e-expandit' title='Click to select columns to display'><img src='".e_IMAGE_ABS."admin_images/info_16.png' alt='' title=\"".TPVLAN_7."\" class='icon S16' /></a>\n";
        $preview_icon 	= "<input style='vertical-align:middle;' type='image' src='".e_IMAGE_ABS."admin_images/search_16.png'  name=\"preview[".$theme['id']."]\" title='".TPVLAN_9." #".$theme['id']."' />\n";
        $admin_icon 	= ($pref['admintheme'] != $theme['path']) ? "<input type='image' src='".e_IMAGE_ABS."e107_icon_16.png'  name='selectadmin[".$theme['id']."]' alt='' title=\"".TPVLAN_32."\" />\n" : ADMIN_TRUE_ICON;

        $newpreview 	= "<a href='".e_BASE."news.php?themepreview.".$theme['id']."' title='".TPVLAN_9."' >".($theme['preview'] ? "<img src='".$theme['preview']."' style='width:200px; height:160px;' alt='' />" : "<img src='".e_IMAGE_ABS."admin_images/nopreview.png' style='width:200px;height:160px;' title='".TPVLAN_12."' alt='' />")."</a>";


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

		$this->loadThemeConfig();   // load customn theme configuration fieldss.

		$text = "
		<h2 class='caption'>".$theme['name']."</h2>
        <div class='admintabs' id='tab-container'>";

        if(call_user_method("help",$this->themeConfigObj))
		{
			$text .= "
				<ul class='e-tabs e-hideme' id='core-thememanager-tabs'>
				<li id='tab-thememanager-configure'><a href='#core-thememanager-configure'>".LAN_CONFIGURE."</a></li>
				<li id='tab-thememanager-help'><a href='#core-thememanager-help'>".LAN_HELP."</a></li>
			</ul>";
		}

		$text .= "
		<div id='core-thememanager-configure'>
        <table cellpadding='0' cellspacing='0' class='adminform'>
        	<colgroup span='3'>
        		<col class='col-label' />
        		<col class='col-control' />
				<col class='col-control' />
        	</colgroup>
		<tr>
			<td><b>".TPVLAN_11."</b></td>
			<td>".$theme['version']."</td>
			<td class='center middle' rowspan='6' style='text-align:center; vertical-align:middle;width:25%'>".$newpreview."</td>
			</tr>";

		$text .= "<tr><td style='vertical-align:top; width:25%'><b>".TPVLAN_4."</b>:</td><td style='vertical-align:top'>".$author."</td></tr>";
		$text .= "<tr><td style='vertical-align:top; width:25%'><b>".TPVLAN_5."</b>:</td><td style='vertical-align:top'>".$website."</td></tr>";
		$text .= "<tr><td style='vertical-align:top; width:25%'><b>".TPVLAN_6."</b>:</td><td style='vertical-align:top'>".$theme['date']."</td></tr>";

		$text .= "<tr><td style='vertical-align:top; width:25%'><b>".TPVLAN_7."</b>:</td><td style='vertical-align:top'>".$theme['info']."</td></tr>";

         $text .= "<tr><td style='vertical-align:top; width:25%'><b>".TPVLAN_49."</b>:</td>
			<td style='vertical-align:top'>";
        $text .= ($theme['xhtmlcompliant']) ? "W3C XHTML ".$theme['xhtmlcompliant'] : "Not Specified";
		$text .= ($theme['csscompliant']) ? " &amp; CSS ".$theme['csscompliant'] : "";
		$text .= "</td></tr>";


		if($mode == 1)    // site theme..
		{

             	$text .= "
				<tr>
                    <td style='vertical-align:top; width:24%;'><b>".TPVLAN_53."</b></td>
					<td colspan='2' style='vertical-align:top width:auto;'>";

					if(varset($theme['pluginOptions']))
					{
						foreach($theme['pluginOptions'] as $key=>$val)
						{
	                  		$text .= $this->renderPlugins($theme['pluginOptions']);
							$text .= "&nbsp;";
						}
					}

					$text .= "&nbsp;</td>
				</tr>";

                 $text .= "
				<tr>
                    <td style='vertical-align:top; width:24%;'><b>".TPVLAN_30."</b></td>
					<td colspan='2' style='vertical-align:top width:auto;'>
					<input type='radio' name='image_preload' value='1'".($pref['image_preload'] ? " checked='checked'" : "")." /> ".TPVLAN_28."&nbsp;&nbsp;
					<input type='radio' name='image_preload' value='0'".(!$pref['image_preload'] ? " checked='checked'" : "")." /> ".TPVLAN_29."
					</td>
				</tr>";
		}



        if($mode==1)  // New in 0.8   ----   site theme.
		{

            $itext = "<tr>
					<td style='vertical-align:top; width:24%'><b>".TPVLAN_50."</b>:</td>
					<td colspan='2' style='vertical-align:top'>
                    <table cellpadding='0' cellspacing='0' class='adminlist'>
                      	<colgroup span='2'>
                      		<col class='col-tm-layout-default' style='width:10%' />
                      		<col class='col-tm-layout-name' style='width:20%' />
							<col class='col-tm-layout-visibility' style='width:35%' />
							<col class='col-tm-layout-preset' style='width:35%' />
                      	</colgroup>
						<tr>";
                        $itext .= ($mode == 1) ? "<td class='center top'>".TPVLAN_55."</td>" : "";
						$itext .= "
							<td>".TPVLAN_52."</td>
							<td>".TPVLAN_56."</td>
							<td>".TPVLAN_54."</td>

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
								$itext .= "<td class='center'>\n";

								$itext .= "
									<input type='radio' name='layout_default' value='{$key}' ".($pref['sitetheme_deflayout']==$key ? " checked='checked'" : "")." />
								</td>";
							}

							$itext .= "<td style='vertical-align:top'>";
							$itext .= ($val['@attributes']['previewFull']) ? "<a href='".e_THEME_ABS.$theme['path']."/".$val['@attributes']['previewFull']."' >" : "";
							$itext .= $val['@attributes']['title'];
							$itext .= ($val['@attributes']['previewFull']) ? "</a>" : "";

							$custompage_count = (isset($pref['sitetheme_custompages'][$key])) ? " [".count($pref['sitetheme_custompages'][$key])."]" : "";
                            $custompage_diz = "";
                            $count = 1;
							if(isset($pref['sitetheme_custompages'][$key]) && count($pref['sitetheme_custompages'][$key]) > 0)
							{
                            	foreach($pref['sitetheme_custompages'][$key] as $cp)
								{
                                	$custompage_diz .= "<a href='#element-to-be-shown' class='e-expandit'>".trim($cp)."</a>&nbsp;";
									if($count > 4)
									{
                                    	$custompage_diz .= "...";
										break;
									}
									$count++;
								}
							}
							else
							{
                            	$custompage_diz = "<a href='#element-to-be-shown' class='e-expandit'>None</a> ";
							}


							$itext .= "</td>
								<td style='vertical-align:top'>";
                              	$itext .= ($pref['sitetheme_deflayout'] != $key) ? $custompage_diz."<div class='e-hideme' id='element-to-be-shown'><textarea style='width:97%' rows='6' cols='20' name='custompages[".$key."]' >".(isset($pref['sitetheme_custompages'][$key]) ? implode("\n",$pref['sitetheme_custompages'][$key]) : "")."</textarea></div>\n" : TPVLAN_55;  // Default

							$itext .= "</td>";

                            $itext .= "<td>";

                            $itext .= (varset($val['menuPresets'])) ? $this->frm->admin_button("setMenuPreset[".$key."]", "Use Preset") : "";
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
                	<td class='center' style='width:10%'>".TPVLAN_55."</td>
			  		<td style='width:20%'>".TPVLAN_52."</td>
					<td class='left'>".TPVLAN_7."</td>
				</tr>\n";



				foreach($theme['css'] as $css)
				{
					$text2 = "";

                	if($mode == 1 && substr($css['name'],0,6)=="admin_")
					{
                    	continue;
					}

					if($mode == 2)
					{
						if (!$css['nonadmin']) {
							$text2 = "
							<td class='center'>
							<input type='radio' name='admincss' value='".$css['name']."' ".($pref['admincss'] == $css['name'] || (!$pref['admincss'] && $css['name'] == "style.css") ? " checked='checked'" : "")." />
							</td>
							<td>".$css['name']."</td>
							<td>".($css['info'] ? $css['info'] : ($css['name'] == "style.css" ? TPVLAN_23 : TPVLAN_24))."</td>\n";
						}
					}

					if($mode == 1)
					{


						$text2 = "
						<td class='center'>
						<input type='radio' name='themecss' value='".$css['name']."' ".($pref['themecss'] == $css['name'] || (!$pref['themecss'] && $css['name'] == "style.css") ? " checked='checked'" : "")." />
						</td>
						<td>".$css['name']."
						</td>
						<td>".($css['info'] ? $css['info'] : ($css['name'] == "style.css" ? TPVLAN_23 : TPVLAN_24))."</td>\n";
					}

					$text .= ($text2) ? "<tr>".$text2."</tr>" : "";

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
				<td><b>".TPVLAN_41.":</b></td>
				<td colspan='2'>".$astext."</td>
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



		</div>


			<div id='core-thememanager-help'  >".$this->renderThemeHelp()."</div>

        </div>
		\n";

		return $text;
	}

    function renderPlugins($pluginOptions)
	{
		global $frm,$sql;

     	$tmp = (varset($pluginOptions['plugin'][1])) ? $pluginOptions['plugin'] :  $pluginOptions;   // if there is 1 entry, then it's not the same array.
        $text = "";

		foreach($tmp as $p)
		{
			$plug = trim($p['@attributes']['name']);

         	if(plugInstalled($plug))
			{
            	$text .= $plug." ".ADMIN_TRUE_ICON;
			}
			else
			{
			 //	echo $plug;
				if($sql -> db_Select("plugin", "plugin_id", " plugin_path = '".$plug."' LIMIT 1 "))
	 			{
					$row = $sql -> db_Fetch(MYSQL_ASSOC);
					$name = "installplugin[".$row['plugin_id']."]";
                	$text .=  $this->frm->admin_button($name, ADLAN_121." ".$plug."",'delete');
				}
				else
				{
					$text .= (varset($p['@attributes']['url']) && ($p['@attributes']['url']!='core')) ?  "<a rel='external' href='".$p['@attributes']['url']."'>".$plug."</a> " :  "<i>".$plug."</i>";
					$text .= ADMIN_FALSE_ICON;
				}

			}
            $text .= "&nbsp;&nbsp;&nbsp;";
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
		global $pref, $e107cache, $ns, $sql, $emessage;
		$themeArray = $this -> getThemes("id");

		$pref['sitetheme'] = $themeArray[$this -> id];
		$pref['themecss'] ='style.css';
        $pref['sitetheme_deflayout'] = $this->findDefault($themeArray[$this -> id]);
		$pref['sitetheme_layouts'] = is_array($this->themeArray[$pref['sitetheme']]['layouts']) ? $this->themeArray[$pref['sitetheme']]['layouts'] : array();
        $pref['sitetheme_custompages'] = $this->themeArray[$pref['sitetheme']]['custompages'];
        $pref['sitetheme_version'] = $this->themeArray[$pref['sitetheme']]['version'];
		$pref['sitetheme_releaseUrl'] = $this->themeArray[$pref['sitetheme']]['releaseUrl'];

        $sql -> db_Delete("menus", "menu_layout !='' ");

		$e107cache->clear_sys();
	 	if(save_prefs())
		{
        	$emessage->add(TPVLAN_3." <b>'".$themeArray[$this -> id]."'</b>", E_MESSAGE_SUCCESS);
		}
		else
		{
      		$emessage->add(TPVLAN_3." <b>'".$themeArray[$this -> id]."'</b>", E_MESSAGE_ERROR);
		}

		$this->theme_adminlog('01',$pref['sitetheme'].', '.$pref['themecss']);

	  // 	$ns->tablerender("Admin Message", "<br /><div style='text-align:center;'>".TPVLAN_3." <b>'".$themeArray[$this -> id]."'</b>.</div><br />");
	}

	function findDefault($theme)
	{
		if(varset($_POST['layout_default']))
		{
        	return $_POST['layout_default'];
		}

    	$l = $this->themeArray[$theme];

		if(!$l)
		{
           $l = $this->getThemeInfo($theme);
		}


		if($l['layouts'])
		{
			foreach($l['layouts'] as $key=>$val)
			{
	        	if(isset($val['@attributes']['default']) && ($val['@attributes']['default'] == "true"))
				{
	              return $key;
				}
			}
		}
		else
		{
           	return "";
		}
	}

	function setAdminTheme()
	{
		global $pref, $e107cache, $ns, $emessage;
		$themeArray = $this -> getThemes("id");
		$pref['admintheme'] = $themeArray[$this -> id];
		$pref['admincss'] = file_exists(e_THEME.$pref['admintheme'].'/admin_style.css') ? 'admin_style.css' : 'style.css';
		$e107cache->clear_sys();
		if(save_prefs())
		{
        	$emessage->add(TPVLAN_40." <b>'".$themeArray[$this -> id]."'</b>", E_MESSAGE_SUCCESS); // Default Message
			$this->theme_adminlog('02',$pref['admintheme'].', '.$pref['admincss']);
		}

	  //	$ns->tablerender("Admin Message", "<br /><div style='text-align:center;'>".TPVLAN_40." <b>'".$themeArray[$this -> id]."'</b>.</div><br />");
      //  $this->showThemes('admin');
	}

	function setStyle()
	{
		global $pref, $e107cache, $ns, $sql, $emessage;
		$pref['themecss'] = $_POST['themecss'];
		$pref['image_preload'] = $_POST['image_preload'];
		$pref['sitetheme_deflayout'] = $_POST['layout_default'];

		$e107cache->clear_sys();
		if(save_prefs())
		{
        	$emessage->add(TPVLAN_37, E_MESSAGE_SUCCESS); // Default Message
			$emessage->add($this -> setThemeConfig(),E_MESSAGE_SUCCESS); // Custom Message from theme config.
			$this->theme_adminlog('03',$pref['image_preload'].', '.$pref['themecss']);
		}
		else
		{
        	$emessage->add(TPVLAN_43, E_MESSAGE_ERROR);
		}
	}

	function setAdminStyle()
	{
		global $pref, $e107cache, $ns, $emessage;
		$pref['admincss'] = $_POST['admincss'];
		$pref['adminstyle'] = $_POST['adminstyle'];


		$e107cache->clear_sys();
		if(save_prefs())
		{
        	$emessage->add(TPVLAN_43, E_MESSAGE_SUCCESS);
			$this->theme_adminlog('04',$pref['adminstyle'].', '.$pref['admincss']);
		}
		else
		{
        	$emessage->add(TPVLAN_43, E_MESSAGE_ERROR);
		}
	}

    function SetCustomPages($array)
	{
		if(!is_array($array)){ return; }

		global $pref;
		$key = key($array);

		$pref['sitetheme_custompages'][$key] = array_filter(explode("\n",trim($array[$key])));

		if($pref['sitetheme_deflayout'] == 'legacyCustom')
		{
			$pref['sitetheme_custompages']['legacyCustom'] = array();
		}
		save_prefs();
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
    	$CUSTOMPAGES = "";

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
		$themeArray['xhtmlcompliant'] = ($xhtml == "true" ? "1.1" : false);

		preg_match('/csscompliant(\s*?=\s*?)(\S*?);/si', $themeContents, $match);
		$css = strtolower($match[2]);
		$themeArray['csscompliant'] = ($css == "true" ? "2.1" : false);

/*        preg_match('/CUSTOMPAGES(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		$themeArray['custompages'] = array_filter(explode(" ",$match[3]));*/

        $themeContentsArray = explode("\n",$themeContents);

  		if (!$themeArray['name'])
		{
			unset($themeArray);
		}


        $lays['legacyDefault']['@attributes'] = array('title'=>'Default','preview'=>'','previewFull'=>'','plugins'=>'', 'default'=>'true');

        if(!file_exists(e_THEME.$path."theme.xml"))  // load custompages from theme.php only when theme.xml doesn't exist.
		{
			foreach($themeContentsArray as $line)
			{
	        	if(strstr($line,"CUSTOMPAGES"))
				{
					eval(str_replace("$","\$",$line));
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
	        	$themeArray['custompages']['legacyCustom'] = explode(" ",$CUSTOMPAGES);
				$lays['legacyCustom']['@attributes'] = array('title'=>'Custom','preview'=>'','previewFull'=>'','plugins'=>'');
			}
		}

		$themeArray['path'] = $path;
		$themeArray['layouts'] = $lays;

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
		$vars['compatibility']			= varset($vars['@attributes']['compatibility']);
		$vars['releaseUrl']				= varset($vars['@attributes']['releaseUrl']);
		$vars['email']	 				= varset($vars['author']['@attributes']['email']);
      	$vars['website'] 				= varset($vars['author']['@attributes']['url']);
		$vars['author']				   	= varset($vars['author']['@attributes']['name']);
		$vars['info'] 					= $vars['description'];
		$vars['xhtmlcompliant'] 		= varset($vars['compliance']['@attributes']['xhtml']);
		$vars['csscompliant'] 			= varset($vars['compliance']['@attributes']['css']);
		$vars['path']					= $path;
		$vars['@attributes']['default'] = (strtolower($vars['@attributes']['default'])=='true') ? 1 : 0;

		unset($vars['authorEmail'],$vars['authorUrl'],$vars['xhtmlCompliant'],$vars['cssCompliant'],$vars['description']);

		// Compile layout information into a more usable format.
        $custom = array();

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



	function themeReleaseCheck($curTheme,$curVersion,$releaseUrl,$cache=TRUE)
	{
		global $e107cache;

	    if(!$releaseUrl)
		{
	    	return;
		}

		$e107cache->CachePageMD5 = md5($curTheme.$curVersion);

		if(($cache==TRUE) && ($cacheData = $e107cache->retrieve('themeUpdateCheck', 3600, TRUE)))
		{
	        require_once(e_HANDLER."message_handler.php");
			$emessage = &eMessage::getInstance();
			$emessage->add($cacheData);
			return;
		}

	    require_once(e_HANDLER.'xml_class.php');
		$xml = new xmlClass;

	 	if(substr($releaseUrl,-4) == ".php")
		{
        	$releaseUrl .= "?name=".$curTheme."&ver".$curVersion;
		}

		if($rawData = $xml -> loadXMLfile($releaseUrl, TRUE))
		{
	    	if(!$rawData['release'][1])
			{
	        	$rawData['release'] = $rawData;
			}

	        $txt = "";

	        foreach($rawData['release'] as $val)
			{
				$name 		= $val['@attributes']['foldername'];
				$version 	= $val['@attributes']['version'];
				$url 		= $val['@attributes']['url'];

	            if(($name == $curTheme)  && version_compare($version,$curVersion)==1)
				{
	             	$txt .= ADLAN_161." <a href='".$url."'>".$name ." v".$version."</a><br />";
					break;
				}
	        }

			if($txt)
			{
	            require_once (e_HANDLER."message_handler.php");
				$emessage = &eMessage::getInstance();
				$emessage->add($txt);
				if($cache==TRUE)
				{
					$e107cache->set('themeUpdateCheck', $txt, TRUE);
				}
			}
		}
	}

}
?>