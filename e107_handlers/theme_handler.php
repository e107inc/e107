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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/theme_handler.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-20 13:06:29 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/


class themeHandler{

	var $themeArray;
	var $action;
	var $id;

	/* constructor */

	function themeHandler() {
		$this -> themeArray = $this -> getThemes();
		if(e_QUERY) {
			list($this -> action, $this -> id) = explode('.', e_QUERY);
			if($this -> action == "preview") {
				$this -> themePreview();
			}
			if($this -> action == "set") {
				$this -> setTheme();
			}
		}
	}

	function getThemes($mode=FALSE) {
		$themeArray = array();
		$tloop = 1;
		$handle = opendir(e_THEME);
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && $file != "CVS" && $file != "templates" && is_dir(e_THEME.$file)) {
				if($mode == "id") {
					$themeArray[$tloop] = $file;
				} else {
					$themeArray[$file]['id'] = $tloop;
				}
				$tloop++;
				if(!$mode) {
					$handle2 = opendir(e_THEME.$file."/");
					while (false !== ($file2 = readdir($handle2))) {
						if ($file2 != "." && $file2 != ".." && $file != "CVS" && !is_dir(e_THEME.$file."/".$file2)) {
							$themeArray[$file]['files'][] = $file2;
							if(strstr($file2, "preview.")) {
								$themeArray[$file]['preview'] = e_THEME.$file."/".$file2;
							}
						}

						$fp=fopen(e_THEME.$file."/theme.php", "r");
						$themeContents = fread ($fp, filesize(e_THEME.$file."/theme.php"));
						fclose($fp);

						preg_match('/themename(\s=\s|=|\s=|=\s)"(.*?)";/', $themeContents, $match);
						$themeArray[$file]['name'] = $match[2];

						preg_match('/themeversion(\s=\s|=|\s=|=\s)"(.*?)";/', $themeContents, $match);
						$themeArray[$file]['version'] = $match[2];
				
						preg_match('/themeauthor(\s=\s|=|\s=|=\s)"(.*?)";/', $themeContents, $match);
						$themeArray[$file]['author'] = $match[2];

						preg_match('/themeemail(\s=\s|=|\s=|=\s)"(.*?)";/', $themeContents, $match);
						$themeArray[$file]['email'] = $match[2];

						preg_match('/themewebsite(\s=\s|=|\s=|=\s)"(.*?)";/', $themeContents, $match);
						$themeArray[$file]['website'] = $match[2];

						preg_match('/themedate(\s=\s|=|\s=|=\s)"(.*?)";/', $themeContents, $match);
						$themeArray[$file]['date'] = $match[2];

						preg_match('/themeinfo(\s=\s|=|\s=|=\s)"(.*?)";/', $themeContents, $match);
						$themeArray[$file]['info'] = $match[2];
						
					}
					closedir($handle2);
				}
			}
		}
		closedir($handle);
		return $themeArray;
	}

	function themeUpload() {
		if (!$_POST['ac'] == md5(ADMINPWCHANGE)) {
			exit;
		}
		global $ns;
		extract($_FILES);
		if(!is_writable(e_THEME)) {
			$ns->tablerender(TPVLAN_16, TPVLAN_20);
		} else {
			$pref['upload_storagetype'] = "1";
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

				$opref = $pref['upload_storagetype'];
				$pref['upload_storagetype'] = 1;
				$uploaded = file_upload(e_THEME);
				$pref['upload_storagetype'] = $opref;

				$archiveName = $uploaded[0]['name'];


				if($fileType == "zip") {
					require_once(e_HANDLER."pclzip.lib.php");
					$archive = new PclZip(e_THEME.$archiveName);
					$unarc = ($fileList = $archive -> extract(PCLZIP_OPT_PATH, e_THEME));
				} else {
					require_once(e_HANDLER."pcltar.lib.php");
					$unarc = ($fileList = PclTarExtract($archiveName, e_THEME));
				}

				if(!$unarc) {
					if($fileType == "zip") {
						$error = "PCLZIP extract error: '".$archive -> errorName(TRUE)."'";
					} else {
						$error = "PCLTAR extract error: ".PclErrorString().", code: ".intval(PclErrorCode());
					}
					$ns->tablerender(TPVLAN_16, TPVLAN_18." ".$archiveName." ".$error);
					require_once("footer.php");
					exit;
				}

				$folderName = substr($fileList[0]['stored_filename'], 0, (strpos($fileList[0]['stored_filename'], "/")));
				$ns->tablerender(TPVLAN_16, TPVLAN_19);

				@unlink(e_THEME.$archiveName);
				unset($this -> themeArray);
				$this -> themeArray = $this -> getThemes();

			}
		}
	}

	function showThemes() {
		if(!is_writable(e_THEME)) {
			$ns->tablerender(TPVLAN_16, EPL_ADLAN_44);
		} else {
			$text = "<div style='text-align:center'>
			<form enctype='multipart/form-data' method='post' action='".e_SELF."'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td class='forumheader3' style='width: 50%;'>".TPVLAN_13."</td>
			<td class='forumheader3' style='width: 50%;'>
			<input type='hidden' name='MAX_FILE_SIZE' value='1000000' />
			<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
			<input class='tbox' type='file' name='file_userfile[]' size='50' />
			</td>
			</tr>
			<tr>
			<td colspan='2' style='text-align:center' class='forumheader'>
			<input class='button' type='submit' name='upload' value='".TPVLAN_14."' />
			</td>
			</tr>
			</table>
			</form>
			<br />\n";
		}
		$text .= "<table style='".ADMIN_WIDTH."' class='fborder'>";
		foreach($this -> themeArray as $theme) {
			$author = ($theme['email'] ? "<a href='mailto:".$theme['email']."' title='$".$theme['email']."'>".$theme['author']."</a>" : $theme['author']);
			$website = ($theme['website'] ? "<a href='".$theme['website']."' rel='external'>".$theme['website']."</a>" : "");
			$preview = ($theme['preview'] ? "<img src='".$theme['preview']."' style='border: 1px solid #000;' alt='' />" : "<img src='".e_IMAGE."generic/nopreview.png' title='".TPVLAN_12."' alt='' />");
			$text .= "<tr>
			<td class='forumheader3' style='width:30%; text-align:center; vertical-align:top'>$preview
			<br />
			<br />
			<b>".$theme['name']."</b><br />".TPVLAN_11." ".$theme['version']."
			<br />
			</td>
			<td class='forumheader3' style='width:70%;vertical-align:top'>
			<table cellspacing='3' style='width:97%'>
			<tr><td style='vertical-align:top;width:24%'><b>".TPVLAN_4."</b>:</td><td style='vertical-align:top'> $author</td></tr>
			<tr><td style='vertical-align:top'><b>".TPVLAN_5."</b>:</td><td style='vertical-align:top'> $website</td></tr>
			<tr><td style='vertical-align:top'><b>".TPVLAN_6."</b>:</td><td style='vertical-align:top'>".$theme['date']."</td></tr>
			<tr><td style='vertical-align:top'><b>".TPVLAN_7."</b>:</td><td style='vertical-align:top'>".$theme['info']."</td></tr>
			<tr><td style='vertical-align:top'><b>".TPVLAN_8."</b>:</td><td style='vertical-align:top'>
			[ <a href='".e_SELF."?preview.".$theme['id']."'>".TPVLAN_9."</a> ] [ <a href='".e_SELF."?set.".$theme['id']."'>".TPVLAN_10."</a> ]
			</td></tr>
			</table>
			</td>
			</tr>\n";
		}
		$text .= "</table>";
		global $ns;
		$ns->tablerender("Themes", $text);
	}

	function themePreview() {
		echo "<script type='text/javascript'>document.location.href='".e_BASE."news.php?themepreview.".$this -> id."'</script>\n";
		exit;
	}

	function showPreview() {
		@include(e_LANGUAGE."admin/lan_theme.php");
		@include(e_LANGUAGEDIR."English/admin/lan_theme.php");
		$text = "<br /><div class='indent'>".TPVLAN_1.".</div><br />";
		global $ns;
		$ns->tablerender(TPVLAN_2, $text);
	}

	function setTheme() {
		global $pref, $e107cache, $ns;
		$themeArray = $this -> getThemes("id");
		$pref['sitetheme'] = $themeArray[$this -> id];
		$e107cache->clear();
		save_prefs();
		$ns->tablerender("Admin Message", "<br /><div style='text-align:center;'>".TPVLAN_3." <b>'".$themeArray[$this -> id]."'</b>.<br />");
	}

}