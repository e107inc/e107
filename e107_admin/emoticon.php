<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - Emotions Settings & Packs
 *
 *
*/

require_once("../class2.php");
if (!getperms("F"))
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('emoticon', true);

$e_sub_cat = 'emoticon';

e107::getConfig('emote')->clearPrefCache('emote');

require_once("auth.php");

$mes = e107::getMessage();

if(!$sql->db_Count("core", "(*)", "WHERE e107_name = 'emote_default' AND e107_value !='' "))
{	// Set up the default emotes
//	$tmp = 'a:28:{s:9:"alien!png";s:6:"!alien";s:10:"amazed!png";s:7:"!amazed";s:9:"angry!png";s:11:"!grr !angry";s:12:"biglaugh!png";s:4:"!lol";s:11:"cheesey!png";s:10:":D :oD :-D";s:12:"confused!png";s:10:":? :o? :-?";s:7:"cry!png";s:19:"&| &-| &o| :(( !cry";s:8:"dead!png";s:21:"x) xo) x-) x( xo( x-(";s:9:"dodge!png";s:6:"!dodge";s:9:"frown!png";s:10:":( :o( :-(";s:7:"gah!png";s:10:":@ :o@ :o@";s:8:"grin!png";s:10:":D :oD :-D";s:9:"heart!png";s:6:"!heart";s:8:"idea!png";s:10:":! :o! :-!";s:7:"ill!png";s:4:"!ill";s:7:"mad!png";s:13:"~:( ~:o( ~:-(";s:12:"mistrust!png";s:9:"!mistrust";s:11:"neutral!png";s:10:":| :o| :-|";s:12:"question!png";s:2:"?!";s:12:"rolleyes!png";s:10:"B) Bo) B-)";s:7:"sad!png";s:4:"!sad";s:10:"shades!png";s:10:"8) 8o) 8-)";s:7:"shy!png";s:4:"!shy";s:9:"smile!png";s:10:":) :o) :-)";s:11:"special!png";s:3:"%-6";s:12:"suprised!png";s:10:":O :oO :-O";s:10:"tongue!png";s:21:":p :op :-p :P :oP :-P";s:8:"wink!png";s:10:";) ;o) ;-)";}';
//	$insert = array("e107_name"=>"emote_default", "e107_value"=>$tmp);
//	$sql->db_Replace("core", $insert);
}


// Change the active emote pack
if (isset($_POST['active']))
{
	if ($pref['smiley_activate'] != $_POST['smiley_activate'])
	{
		$pref['smiley_activate'] = $_POST['smiley_activate'];
		e107::getLog()->add($pref['smiley_activate'] ? 'EMOTE_02' : 'EMOTE_03', $pref['emotepack'], E_LOG_INFORMATIVE, '');
		save_prefs();
		$update = true;

	}
	else
	{
		$mes->addInfo(LAN_NO_CHANGE);
	}

}

//e107::getRender()->tablerender($caption, $mes->render() . $text);

/* get packs */
require_once(e_HANDLER."file_class.php");
$fl = e107::getFile();
$emote = new emotec;
$one_pack = FALSE;
$filtered = e107::getParser()->filter($_POST);

// Check for pack-related buttons pressed
foreach($filtered as $key => $value)
{
	if(strstr($key, "subPack_"))
	{
		$subpack = str_replace("subPack_", "", $key);
		$emote->emoteConf($subpack);
		break;
	}

	if(strstr($key, "XMLPack_"))
	{
		$subpack = str_replace("XMLPack_", "", $key);
		$emote->emoteXML($subpack);
		break;
	}

	if(strstr($key, "defPack_"))
	{
		$pref['emotepack'] = str_replace("defPack_", "", $key);
		if(save_prefs())
		{
			$mes->addSuccess(LAN_UPDATED);
		}
		else
		{
			$mes->addInfo(LAN_NO_CHANGE);
		}
		e107::getLog()->add('EMOTE_01', $pref['emotepack'], E_LOG_INFORMATIVE, '');
		break;
	}

	if(strstr($key, "scanPack_"))
	{
		$one_pack = str_replace("scanPack_", "", $key);
		break;
	}
}

// $ns->tablerender($caption, $mes->render() . $text);


$check = TRUE;
//$check = $emote -> installCheck();
$check = $emote->installCheck($one_pack);
if($check!==FALSE)
{
	$emote -> listPacks();
}



class emotec
{
	var $packArray;			// Stores an array of all available emote packs (as subdirectory names)

	function __construct()
	{
		/* constructor */
		$fl = e107::getFile();
		$this -> packArray = $fl->get_dirs(e_IMAGE."emotes");

		if(isset($_POST['sub_conf']))
		{	// Update stored pack configuration
		  $this -> saveConf();
		}
	}


	// List available emote packs
	function listPacks()
	{
		$pref = e107::getPref();
		$frm = e107::getForm();
		$fl = e107::getFile();
		$ns = e107::getRender();
		$mes = e107::getMessage();		

		$text = "
	
		<ul class='nav nav-tabs'>
			<li class='active'><a href='#emoticon-activate' data-toggle='tab'>".EMOLAN_1."</a></li>
			<li><a href='#emoticon-packages' data-toggle='tab'>".EMOLAN_13."</a></li>
		</ul>
		
		<div class='tab-content'>
			<div class='tab-pane active' id='emoticon-activate'>
			<fieldset>
				<form method='post' action='".e_SELF."'>
					<legend>".EMOLAN_1."</legend>
						<table class='table adminform'>
							<colgroup>
								<col style='width:30%' />
								<col style='width:70%' />
							</colgroup>
							<tbody>
								<tr>
									<td>
										".EMOLAN_4.":
									</td>
									<td>
										<div class='auto-toggle-area autocheck'>
											".$frm->checkbox('smiley_activate', 1, varset($pref['smiley_activate'],0))."
										</div>
									</td>
								</tr>
							</tbody>
						</table>
						<div class='buttons-bar center'>
						".$frm->admin_button('active','active','update',LAN_UPDATE)."
						</div>
					<fieldset>
				</form>
			</div>
		";

		$text .= "
			<div class='tab-pane' id='emoticon-packages'>
			<form method='post' action='".e_SELF."#etabTabContainer=emoticon-packages'>
				<fieldset>
					<legend>".EMOLAN_13."</legend>
					<table class='table adminlist' style='margin-top:10px'>
						<colgroup>
							<col style='width:15%' />
							<col style='width:50%' />
							<col style='width:10%' />
							<col style='width:25%' />
						</colgroup>
						<thead>
							<tr>
								<th class='center'>".LAN_NAME."</th>
								<th class='center'>".EMOLAN_3."</th>
								<th class='center'>".LAN_STATUS."</th>
								<th class='center'>".LAN_OPTIONS."</th>
							</tr>
						</thead>
						<tbody>
		";

		$reject = '~^emoteconf|\.html$|\.php$|\.txt$';		// Files to exclude
		foreach($this -> packArray as $pack)
		{
			$can_scan = FALSE;
			$emoteArray = $fl -> get_files(e_IMAGE.'emotes/'.$pack, $reject);

			$text .= "
			<tr>
			<td class='forumheader'>{$pack}</td>
			<td class='forumheader'>
			";

			foreach($emoteArray as $emote)
			{
				if (strstr($emote['fname'], ".pak")
				|| strstr($emote['fname'], ".xml")
				|| strstr($emote['fname'], "phpBB"))
				{
					$can_scan = TRUE;		// Allow re-scan of config files
				}
				//elseif  (!strstr($emote['fname'], ".txt") && !strstr($emote['fname'], ".bak") && !strstr($emote['fname'], ".html") && !strstr($emote['fname'], ".php") )
				else
				{  // Emote file found (might get other non-image files, but shouldn't)
					$text .= "<img src='".$emote['path'].$emote['fname']."' alt='' /> ";
				}
			}

			$text .= "
								</td>
								<td class='center middle'>".($pref['emotepack'] == $pack ? "<span class='label label-success'>".LAN_ACTIVE."</span>" : "<button class='btn btn-primary' type='submit' name='defPack_".$pack."' value='".EMOLAN_11."'><span>".EMOLAN_11."</span></button>")."</td>
								<td>";
								
								
								
						
			$text .= $frm->admin_button('subPack_'.$pack,'edit','default',LAN_CONFIGURE);
			
			if ($can_scan && ($pack != 'default'))
			{
			//	$text .= "<button class='btn btn-default submit' type='submit' name='scanPack_".$pack."'><span>".EMOLAN_26."</span></button>";
				$text .= $frm->admin_button('scanPack_'.$pack,'active','default',EMOLAN_26);
			}
			
			$text .= $frm->admin_button('XMLPack_'.$pack,'submit','default',EMOLAN_28);
			$text .= "
								</td>
							</tr>
			";
		}

		$text .= "
						</tbody>
					</table>
				</fieldset>
			</form>
			</div>
		</div>

		";

		$ns->tablerender(EMOLAN_PAGE_TITLE, $mes->render().$text);
	}


	// Configure an individual emote pack
	function emoteConf($packID)
	{
		global $e107, $sysprefs;
		$frm = e107::getForm();
		$tp = e107::getParser();
		$fl = e107::getFile();
		$ns = e107::getRender(); 

		$packID = $tp->filter($packID);
		$corea = "emote_".$packID;

		$emotecode = $sysprefs -> getArray($corea);	
		$reject = '~^emoteconf|\.html$|\.php$|\.txt$|\.pak$|\.xml|\.phpBB';		// Files to exclude
		$emoteArray = $fl -> get_files(e_IMAGE."emotes/".$packID, $reject);

		$eArray = array();
		foreach($emoteArray as $value)
		{
			$eArray[] = array('path' => $value['path'], 'fname' => $value['fname']);
		}

		//XXX Not sure if we need to know rhe number of files found - count($eArray) - <div class='info-bar'><strong>Total ".count($eArray)." files found</strong></div>
		$text = "
		<form method='post' action='".e_SELF."#etabTabContainer=emoticon-packages'>
			<fieldset id='core-emoticon-configure'>
				<legend class='e-hideme'>".LAN_EDIT."</legend>
				<div class='info-bar' style='padding-bottom:10px'>".$tp->lanVars(EMOLAN_31, count($eArray))."</div>
				<table class='table adminlist'>
					<colgroup>
						<col style='width:20px' />
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<thead>
						<tr>
							<th>".EMOLAN_5."</th>
							<th>".LAN_NAME."</th>
							<th class='last'>".EMOLAN_6."<span class='field-help'> ( ".EMOLAN_7." )</span></th>
						</tr>
					</thead>
					<tbody>
		";

		foreach($eArray as $emote)
		{
			$ename = $emote['fname'];
			$evalue = str_replace(".", "!", $ename);
			$file_back = '';
			$text_back = '';

			if (!isset($emotecode[$evalue]))
			{
			 	$file_back = '&nbsp;&nbsp;<span class="label label-danger error">'.EMOLAN_37.'</span>';
			}
			elseif (!$emotecode[$evalue])
			{
			   $text_back = '&nbsp;&nbsp;<span class="label label-danger error">'.EMOLAN_38.'</span>';
			}
			$text .= "
					<tr>
						<td class='center'><img src='".$emote['path'].$ename."' alt='' /></td>
						<td>".$ename."{$file_back}</td>
						<td>
							<input class='tbox input-text' type='text' name='{$evalue}' value='".$tp->toForm(varset($emotecode[$evalue],''))."' maxlength='200' />{$text_back}
						</td>
					</tr>
			";
		}

		$text .= "
				</tbody>
			</table>
			<div class='buttons-bar center'>
				<input type='hidden' name='packID' value='{$packID}' />";
				
				
				$text .= $frm->admin_button('sub_conf', 'no-value', 'update', LAN_SAVE);
				$text .= $frm->admin_button('cancel','no-value', 'cancel' ,LAN_CANCEL);
				
		$text .= "
			</div>
		</fieldset>
	</form>";

		$ns->tablerender(EMOLAN_PAGE_TITLE.SEP.LAN_EDIT.": '".$packID."'", $text);
	}


	// Generate an XML file - packname.xml in root emoticon directory
	function emoteXML($packID, $strip_xtn = TRUE)
	{
		global $sysprefs;
		$mes = e107::getMessage();
		$fl = e107::getFile();
		$tp = e107::getParser();

		$packID = $tp->filter($packID);

		$fname = e_IMAGE."emotes/".$packID."/emoticons.xml";
		$backname = e_IMAGE."emotes/".$packID."/emoticons.bak";

		$corea = "emote_".$packID;
		$emotecode = $sysprefs -> getArray($corea);

		$reject = '~^emoteconf|\.html$|\.php$|\.txt$|\.pak$|\.xml|\.phpBB';		// Files to exclude
		$emoteArray = $fl -> get_files(e_IMAGE."emotes/".$packID, $reject);

		$eArray = array();
		foreach($emoteArray as $value)
		{
			$eArray[] = $value['fname'];
		}

		$f_string = "<?xml version=\"1.0\"?".">\n<messaging-emoticon-map >\n\n\n";

		foreach($eArray as $emote)
		{
			// Optionally strip file extension
			$evalue = str_replace(".", "!", $emote);
			if ($strip_xtn) $ename = substr($emote,0,strrpos($emote,'.'));
			$f_string .= "<emoticon file=\"{$ename}\">\n";
			foreach (explode(' ',$tp -> toForm($emotecode[$evalue])) as $v)
			{
				if (trim($v)) $f_string .= "\t<string>{$v}</string>\n";
			}
			$f_string .= "</emoticon>\n";
		}

		$f_string .= "\n</messaging-emoticon-map>\n";

		if (is_file($backname)) unlink($backname);		// Delete any old backup

		if (is_file($fname)) rename($fname,$backname);

		if (file_put_contents($fname,$f_string) === FALSE)
		{
			$mes->addWarning('<strong>'.EMOLAN_30.'</strong>:'.str_replace(e_IMAGE, e_IMAGE_ABS, $fname));
		}
		else
		{
			$mes->addSuccess('<strong>'.EMOLAN_29.'</strong>:'.str_replace(e_IMAGE, e_IMAGE_ABS, $fname));
		}
	}


	// Save configuration for an emote pack that's been edited
	function saveConf()
	{
		$sql = e107::getDb();
		$tp = e107::getParser();


		$packID = $_POST['packID'];
		unset($_POST['sub_conf'], $_POST['packID']);
		$encoded_emotes = $tp->toDB($_POST);
	//	$tmp = addslashes(serialize($encoded_emotes));
		$tmp = e107::getArrayStorage()->WriteArray($encoded_emotes);

		if ($sql->db_Select("core", "*", "e107_name='emote_".$packID."'"))
		{
			e107::getMessage()->addAuto($sql->update("core", "`e107_value`='{$tmp}' WHERE `e107_name`='emote_".$packID."' "), 'update', LAN_SETSAVED, false, false);
		}
		else
		{
			e107::getMessage()->addAuto($sql->insert("core", "'emote_".$packID."', '$tmp' "), 'insert', LAN_SETSAVED, false, false);
		}
	}


	// Identify currently selected emote pack. Read in any new ones
	// Return false to disable listing of packs
	function installCheck($do_one = FALSE)
	{
		global $e107;
		$sql = e107::getDb(); 
		$fl = e107::getFile();
		$ns = e107::getRender();
		$mes = e107::getMessage();

		// Pick up a list of emote packs from the database
		$pack_local = array();
		if ($sql->select("core","*","`e107_name` LIKE 'emote_%'"))
		{
			while ($row = $sql->fetch())
		  	{
		    	$pack_local[substr($row['e107_name'],6)] = TRUE;
		  	}
		}

		foreach($this -> packArray as $value)
		{
			if(strpos($value,' ')!==FALSE)
			{	// Highlight any directory names containing spaces - not allowed
				$msg = "
					<strong>".EMOLAN_17."</strong> ".EMOLAN_18.":
					<div>".LAN_NAME.": {$value}</div>
					<div>".EMOLAN_20.": ".e_IMAGE_ABS."emotes/</div>
				";
				$mes->addError($msg);
				$ns->tablerender(EMOLAN_PAGE_TITLE.' - '.EMOLAN_21, $mes->render());
				return FALSE;
			}

			if (array_key_exists($value,$pack_local))
			{
				unset($pack_local[$value]);
			}

			if (($do_one == $value) || !$do_one &&  (!$sql -> db_Select("core", "*", "e107_name='emote_".$value."' ")))
			{  // Pack info not in DB, or to be re-scanned
			  $no_error = TRUE;
			  $File_type = EMOLAN_32.":";
				// Array of all files in the directory of the selected emote pack
				$fileArray = $fl -> get_files(e_IMAGE."emotes/".$value);		// We actually want all the files in the directory
				$confFile = '';
				foreach($fileArray as $k => $file)
				{
					if(strstr($file['fname'], ".xml"))
					{
						$confFile = array('file' => $file['fname'], 'type' => "xml");
					}
					else if(strstr($file['fname'], ".pak"))
					{
						$confFile = array('file' => $file['fname'], 'type' => "pak");
					}
					else if(strstr($file['fname'], ".php"))
					{
						$confFile = array('file' => $file['fname'], 'type' => "php");
					}
					if ($confFile)
					{
					  unset($fileArray[$k]);
					  break;
					}
				}

				/* .pak file */
				if($confFile['type'] == "pak")
				{
					$filename = e_IMAGE."emotes/".$value."/".$confFile['file'];
					$pakconf = file ($filename);
					$contentArray = array();
					foreach($pakconf as $line)
					{
					  if(trim($line) && strstr($line, "=+") && !strstr($line, ".txt") && !strstr($line, ".html") && !strstr($line, "cvs")) $contentArray[] = $line;
					}
					$confArray = array();
					foreach($contentArray as $pakline)
					{
					  $tmp = explode("=+:", $pakline);
					  $confIC = str_replace(".", "!", $tmp[0]);
					  $confArray[$confIC] = trim($tmp[2]);
					}
					// $tmp = addslashes(serialize($confArray));
					$tmp = e107::serialize($confArray);
					$File_type = EMOLAN_22.":";
				}
				/* end  */

				/* .xml file  */
				if($confFile['type'] == "xml")
				{
					$filename = e_IMAGE."emotes/".$value."/".$confFile['file'];
					$contents = file_get_contents($filename);
					$confArray = array();
					$xml_type = 0;

					if ((strpos($contents, "<icon>") !== FALSE) && (strpos($contents, "<icondef>") !== FALSE))
					{ 	// xep-0038 format
					/* Example:
					  <icon>
						<text>:-)</text>
						<text>:)</text>
						<object mime="image/png">happy.png</object>
						<object mime="audio/x-wav">choir.wav</object>
					  </icon>*/
					  preg_match_all("#\<icon>(.*?)\<\/icon\>#si", $contents, $match);

					  $xml_type = 1;
						// $match[0] - complete emoticon entry
						// $match[1] - match string and object specification
					  $item_index = 1;
					}
					elseif (strpos($contents, "<emoticon") !== FALSE)
					{	//  "Original" E107 format (as used on KDE, although they may be changing to XEP-0038)
						//echo "Decoding standard XML file<br />";
					  preg_match_all("#\<emoticon file=\"(.*?)\"\>(.*?)\<\/emoticon\>#si", $contents, $match);

					  $xml_type = 2;
						// $match[0] - complete emoticon entry
						// $match[1] - filename (may or may not not have file extension/suffix)
						// $match[2] - match string(s) representing emote
					  $item_index = 2;
					}

					if ($xml_type)
					{
					  for($a=0; $a < count($match[0]); $a++)
					  {
					    $e_file = '';
					    switch ($xml_type)
						{
						  case 1 :		// xep-0038
							// Pull out a file name (only support first image type) - its in $fmatch[1]
							if (preg_match("#\<object\s*?mime\=[\"\']image\/.*?\>(.*?)\<\/object\>#si",$match[1][$a],$fmatch))
							{
							  $e_file = $fmatch[1];
//							  echo "xep-0038 file: ".$e_file."<br />";
							  // Pull out all match strings - need to pick out any language definitions for posterity
							  // but currently accept all language strings
							  preg_match_all("#\<text(?:\s*?\>|\s*?xml\:lang\=\"(.*?)\"\>)(.*?)\<\/text\>#si", $match[1][$a], $match2);
							  // $match2[1] is the languages
							  // $match2[2] is the match strings
							  $codet = implode(" ",$match2[2]);
							}
							break;
						  case 2 :
						    $e_file = $match[1][$a];
							// Now pull out all the 'match' strings
							preg_match_all("#\<string\>(.*?)\<\/string\>#si", $match[2][$a], $match2);
							$codet = implode(" ",$match2[1]);
							break;
						}
						// $e_file has the emote file name
						// $match2 has an array of substitution strings


						$file = '';
						foreach($fileArray as $emote)
						{ // Check that the file exists
					      if (strpos($e_file,".") === FALSE)
						  {  // File extension not specified - accept any file extension for match
							if(strpos($emote['fname'], $e_file.".") === 0)
							{
						      $file = str_replace(".", "!", $emote['fname']);
							  break;
							}
						  }
						  else
						  {    // File extension specified - do simple match
							if($emote['fname'] == $e_file)
							{
						      $file = str_replace(".", "!", $emote['fname']);
							  break;
						    }
						  }
					    }
					  // Only add if the file exists. OK if no definition - might want to be added
						if ($file)
						{
							$confArray[$file] = $codet;
						}
					  }
					}
					else
					{
					  //echo "Unsupported XML File Format<br /><br />";
					  $mes->addWarning(EMOLAN_33);
					  $no_error = FALSE;
					}


					// Save pack info in the database
				//	$tmp = addslashes(serialize($confArray));
					$tmp = e107::getArrayStorage()->WriteArray($confArray);
					$File_type = EMOLAN_23.":";
				}

				if($confFile['type'] == "php")
				{
					include_once(e_IMAGE."emotes/".$value."/".$confFile['file']);
					$File_type = EMOLAN_24.":";
					$tmp = $_emoteconf;		// Use consistent name
				}

				if ($no_error)
				{
				  if ($do_one)
				  {	// Assume existing pack
				    $sql->update("core", "`e107_value`='{$tmp}' WHERE `e107_name`='emote_".$value."'");
				  }
				  else
				  {	// Assume new pack
				    $sql->insert("core", "'emote_".$value."', '{$tmp}' ");
				  }
				  $mes->addInfo("<strong>{$File_type}</strong> '{$value}'");
				}
				else
				{  // Error occurred
				  $mes->addError(EMOLAN_27.": '{$value}'");
				}
			}
		}



		if (count($pack_local))
		{
		   	foreach ($pack_local as $p => $d)
			{
				if($p == '0')
				{
					$p = '';
				}

		  		if($sql->delete("core","`e107_name` = 'emote_{$p}'"))
			    {
			        $mes->addInfo(EMOLAN_34.":".$p.EMOLAN_35);
			    }
			}
		}

	  	return TRUE;
	}

}
require_once("footer.php");
/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */
function headerjs()
{
/*	require_once(e_HANDLER.'js_helper.php');

	$ret = "
		<script type='text/javascript'>
			//add required core lan - delete confirm message
		</script>
		<script type='text/javascript' src='".e_JS."core/admin.js'></script>
	";

	return $ret;*/
}
?>