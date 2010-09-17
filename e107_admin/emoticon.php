<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
if (!getperms("F")) 
{
	header("location:".e_BASE."index.php");
	exit;
}

$e_sub_cat = 'emoticon';

require_once("auth.php");

if(!$sql->db_Count("core", "(*)", "WHERE e107_name = 'emote_default'"))
{	// Set up the default emotes
	$tmp = 'a:28:{s:9:"alien!png";s:6:"!alien";s:10:"amazed!png";s:7:"!amazed";s:9:"angry!png";s:11:"!grr !angry";s:12:"biglaugh!png";s:4:"!lol";s:11:"cheesey!png";s:10:":D :oD :-D";s:12:"confused!png";s:10:":? :o? :-?";s:7:"cry!png";s:19:"&| &-| &o| :(( !cry";s:8:"dead!png";s:21:"x) xo) x-) x( xo( x-(";s:9:"dodge!png";s:6:"!dodge";s:9:"frown!png";s:10:":( :o( :-(";s:7:"gah!png";s:10:":@ :o@ :o@";s:8:"grin!png";s:10:":D :oD :-D";s:9:"heart!png";s:6:"!heart";s:8:"idea!png";s:10:":! :o! :-!";s:7:"ill!png";s:4:"!ill";s:7:"mad!png";s:13:"~:( ~:o( ~:-(";s:12:"mistrust!png";s:9:"!mistrust";s:11:"neutral!png";s:10:":| :o| :-|";s:12:"question!png";s:2:"?!";s:12:"rolleyes!png";s:10:"B) Bo) B-)";s:7:"sad!png";s:4:"!sad";s:10:"shades!png";s:10:"8) 8o) 8-)";s:7:"shy!png";s:4:"!shy";s:9:"smile!png";s:10:":) :o) :-)";s:11:"special!png";s:3:"%-6";s:12:"suprised!png";s:10:":O :oO :-O";s:10:"tongue!png";s:21:":p :op :-p :P :oP :-P";s:8:"wink!png";s:10:";) ;o) ;-)";}';
	$sql->db_Insert("core", "'emote_default', '{$tmp}' ");
}


// Change the active emote pack
if (isset($_POST['active']))
{
if ($pref['smiley_activate'] != $_POST['smiley_activate']) 
{
	$pref['smiley_activate'] = $_POST['smiley_activate'];
	save_prefs();
	$update = true;
  }
  admin_update($update);
}


/* get packs */
require_once(e_HANDLER."file_class.php");
$fl = new e_file;
$emote = new emotec;
$one_pack = FALSE;


// Check for pack-related buttons pressed
foreach($_POST as $key => $value)
{
	if(strstr($key, "subPack_"))
	{
		$subpack = str_replace("subPack_", "", $key);
		$emote -> emoteConf($subpack);
		break;
	}

	if(strstr($key, "XMLPack_"))
	{
		$subpack = str_replace("XMLPack_", "", $key);
		$emote -> emoteXML($subpack);
		break;
	}

	if(strstr($key, "defPack_"))
	{
		$pref['emotepack'] = str_replace("defPack_", "", $key);
		save_prefs();
		break;
	}

	if(strstr($key, "scanPack_"))
	{
		$one_pack = str_replace("scanPack_", "", $key);
		break;
	}
}



$check = TRUE;
//$check = $emote -> installCheck();
$check = $emote -> installCheck($one_pack);
if($check!==FALSE)
{
	$emote -> listPacks();
}



class emotec
{
	var $packArray;			// Stores an array of all available emote packs (as subdirectory names)

	function emotec()
	{
		/* constructor */
		global $fl;
		$this -> packArray = $fl -> get_dirs(e_IMAGE."emotes");

		if(isset($_POST['sub_conf']))
		{	// Update stored pack configuration
		  $this -> saveConf();
		}
	}


	// List available emote packs
	function listPacks()
	{
		global $ns, $fl, $pref;

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td style='width:30%' class='forumheader3'>".EMOLAN_4.": </td>
		<td style='width:70%' class='forumheader3'>".($pref['smiley_activate'] ? "<input type='checkbox' name='smiley_activate' value='1'  checked='checked' />" : "<input type='checkbox' name='smiley_activate' value='1' />")."</td>
		</tr>

		<tr>
		<td colspan='2' style='text-align:center' class='forumheader'>
		<input class='button' type='submit' name='active' value='".LAN_UPDATE."' />
		</td>
		</tr>
		</table>
		</form>
		</div>
		";

		$ns -> tablerender(EMOLAN_1, $text);


		$text = "
		<form method='post' action='".e_SELF."'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td class='forumheader' style='width: 20%;'>".EMOLAN_2."</td>
		<td class='forumheader' style='width: 50%;'>".EMOLAN_3."</td>
		<td class='forumheader' style='width: 10%; text-align: center;'>".EMOLAN_8."</td>
		<td class='forumheader' style='width: 20%;'>".EMOLAN_9."</td>
		</tr>
		";

		$reject = array('^\.$','^\.\.$','^\/$','^CVS$','thumbs\.db','.*\._$', 'emoteconf*', '\.bak$');
		foreach($this -> packArray as $pack)
		{
		  $can_scan = FALSE;
			$emoteArray = $fl -> get_files(e_IMAGE."emotes/".$pack, "", $reject);

			$text .= "
			<tr>
			<td class='forumheader' style='width: 20%;'>{$pack}</td>
			<td class='forumheader' style='width: 20%;'>
			";

			foreach($emoteArray as $emote)
			{
			  if (strstr($emote['fname'], ".pak") 
			  || strstr($emote['fname'], ".xml") 
			  || strstr($emote['fname'], "phpBB"))
			  {
				$can_scan = TRUE;		// Allow re-scan of config files
			  }
			  elseif  (!strstr($emote['fname'], ".txt") && !strstr($emote['fname'], ".bak") && !strstr($emote['fname'], ".html") && !strstr($emote['fname'], ".php") )
			  {  // Emote file found
				$text .= "<img src='".$emote['path'].$emote['fname']."' alt='' /> ";
			  }
			}

			$text .= "</td>
			<td class='forumheader3' style='width: 10%; text-align: center;'>".($pref['emotepack'] == $pack ? EMOLAN_10 : "<input class='button' type='submit' name='defPack_".$pack."' value=\"".EMOLAN_11."\" />")."</td>
			<td class='forumheader3' style='width: 20%; text-align: center;'>
				<input class='button' type='submit' name='subPack_".$pack."' value=\"".EMOLAN_12."\" />";
			if ($can_scan && ($pack != 'default'))
			{
			  $text .= "<br /><br /><input class='button' type='submit' name='scanPack_".$pack."' value=\"".EMOLAN_26."\" />";
			}
			$text .= "<br /><br /><input class='button' type='submit' name='XMLPack_".$pack."' value=\"".EMOLAN_28."\" />";
			$text .= "</td></tr>";
		}

		$text .= "
		</table>
		</form>
		";
		$ns -> tablerender(EMOLAN_13, $text);
	}


	// Configure an individual emote pack
	function emoteConf($packID)
	{
		global $ns, $fl, $pref, $sysprefs, $tp;
		$corea = "emote_".$packID;

		$emotecode = $sysprefs -> getArray($corea);

		$reject = array('^\.$','^\.\.$','^\/$','^CVS$','thumbs\.db','.*\._$', 'emoteconf*', '*\.txt', '*\.html', '*\.pak', '*php*', '.cvsignore', '\.bak$');
		$emoteArray = $fl -> get_files(e_IMAGE."emotes/".$packID, "", $reject);

		$eArray = array();
		foreach($emoteArray as $value)
		{
		  if(!strstr($value['fname'], ".php") 
		  && !strstr($value['fname'], ".txt") 
		  && !strstr($value['fname'], ".pak") && !strstr($value['fname'], ".bak") 
		  && !strstr($value['fname'], ".xml") 
		  && !strstr($value['fname'], "phpBB") && !strstr($value['fname'], ".html"))
		  {
			$eArray[] = array('path' => $value['path'], 'fname' => $value['fname']);
		  }
		}

		$text = "
		<form method='post' action='".e_SELF."'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td class='forumheader' style='width: 20%;'>".EMOLAN_2."</td>
		<td class='forumheader' style='width: 20%; text-align: center;'>".EMOLAN_5."</td>
		<td class='forumheader' style='width: 60%;'>".EMOLAN_6." <span class='smalltext'>( ".EMOLAN_7." )</a></td>
		</tr>
		";

		foreach($eArray as $emote)
		{
			$ename = $emote['fname'];
			$evalue = str_replace(".", "!", $ename);
			$file_back = '';
			$text_back = '';

			if (!isset($emotecode[$evalue]))
			{
			  $file_back = ' background-color: #FF8000;';
			}
			elseif (!$emotecode[$evalue])
			{
			   $text_back = ' background-color: #FF8000';
			}
			$text .= "
			<tr>
			<td class='forumheader3' style='width: 20%;{$file_back}'>".$ename."</td>
			<td class='forumheader3' style='width: 20%; text-align: center;'><img src='".$emote['path'].$ename."' alt='' /></td>
			<td class='forumheader3' style='width: 60%;{$text_back}'><input style='width: 80%' class='tbox' type='text' name='{$evalue}' value='".$tp -> toForm(varset($emotecode[$evalue],''))."' maxlength='200' /></td>
			</tr>
			";
		}

		$text .= "
		<tr>
		<td class='forumheader'>".count($eArray)." files</td>
		<td style='text-align: center;' colspan='2' class='forumheader'><input class='button' type='submit' name='sub_conf' value='".EMOLAN_14."' /></td>
		</tr>

		</table>
		<input type='hidden' name='packID' value='{$packID}' />
		</form>";
		$ns -> tablerender(EMOLAN_15.": '".$packID."'", $text);
	}


	// Generate an XML file - packname.xml in root emoticon directory
	function emoteXML($packID, $strip_xtn = TRUE)
	{
		global $fl, $pref, $sysprefs, $tp;
		
		$fname = e_IMAGE."emotes/".$packID."/emoticons.xml";
		$backname = e_IMAGE."emotes/".$packID."/emoticons.bak";
		
		$corea = "emote_".$packID;
		$emotecode = $sysprefs -> getArray($corea);

		$reject = array('^\.$','^\.\.$','^\/$','^CVS$','thumbs\.db','.*\._$', 'emoteconf*', '*\.txt', '*\.html', '*\.pak', '*php*', '.cvsignore', '\.bak$');
		$emoteArray = $fl -> get_files(e_IMAGE."emotes/".$packID, "", $reject);

		$eArray = array();
		foreach($emoteArray as $value)
		{
		  if(!strstr($value['fname'], ".php") 
		  && !strstr($value['fname'], ".txt") 
		  && !strstr($value['fname'], ".pak") && !strstr($value['fname'], ".bak") 
		  && !strstr($value['fname'], ".xml") 
		  && !strstr($value['fname'], "phpBB") && !strstr($value['fname'], ".html"))
		  {
			$eArray[] = $value['fname'];
		  }
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
		  echo "<br /><div style='text-align: center;'>".EMOLAN_30."<b>".$fname."</b></div><br />";
		}
		else
		{
		  echo "<br /><div style='text-align: center;'>".EMOLAN_29."<b>".$fname."</b></div><br />";
		}
	}


	// Save configuration for an emote pack that's been edited
	function saveConf()
	{
		global $ns, $sql, $tp;

		$packID = $_POST['packID'];
		unset($_POST['sub_conf'], $_POST['packID']);
/*
		foreach($_POST as $key => $value)		// $key is file name, with '.' already replaced by '!'
		{
		  unset($_POST[$key]);
//		  $key = str_replace("_", "!", $key);	// Why?
//		  $key = str_replace(".", "!", $key);	// Don't think we need this, either
		  $_POST[$key] = $value;
		}
*/
		$encoded_emotes = $tp -> toDB($_POST);
		$tmp = addslashes(serialize($encoded_emotes));

		if ($sql->db_Select("core", "*", "e107_name='emote_".$packID."'")) 
		{
		  admin_update($sql->db_Update("core", "e107_value='$tmp' WHERE e107_name='emote_".$packID."' "), 'update', EMOLAN_16);
		} 
		else 
		{
		  admin_update($sql->db_Insert("core", "'emote_".$packID."', '$tmp' "), 'insert', EMOLAN_16);
		}
	}


	// Identify currently selected emote pack. Read in any new ones
	// Return false to disable listing of packs
	function installCheck($do_one = FALSE)
	{
		global $sql, $fl;

		// Pick up a list of emote packs from the database
		$pack_local = array();
		if ($sql->db_Select("core","*","`e107_name` LIKE 'emote_%'",TRUE))
		{
		  while ($row = $sql->db_Fetch())
		  {
		    $pack_local[substr($row['e107_name'],6)] = TRUE;
		  }
		}
		
		foreach($this -> packArray as $value)
		{
			if(strpos($value,' ')!==FALSE)
			{	// Highlight any directory names containing spaces - not allowed
				global $ns;
				$msg = "
				<div style='text-align:center;'><b>".EMOLAN_17."<br />".EMOLAN_18."</b><br /><br />
					<table class='fborder'>
					<tr>
						<td class='fcaption'>".EMOLAN_19."</td>
						<td class='fcaption'>".EMOLAN_20."</td>
					</tr>
					<tr>
						<td class='forumheader3'>".$value."</td>
						<td class='forumheader3'>".e_IMAGE."emotes/</td>
					</tr>
					</table>
				</div>";
				$ns->tablerender(EMOLAN_21, $msg);
				return FALSE;
			}

		  if (array_key_exists($value,$pack_local))
		  {
		    unset($pack_local[$value]);
		  }
			
			if (($do_one == $value) || !$do_one &&  (!$sql -> db_Select("core", "*", "e107_name='emote_".$value."' ")))
			{  // Pack info not in DB, or to be re-scanned
			  $no_error = TRUE;
			  $File_type = 'Unknown';
				// Array of all files in the directory of the selected emote pack
				$fileArray = $fl -> get_files(e_IMAGE."emotes/".$value);
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
					$tmp = addslashes(serialize($confArray));
					$File_type = EMOLAN_22;
				}
				/* end  */

				/* .xml file  */
				if($confFile['type'] == "xml")
				{
					$filename = e_IMAGE."emotes/".$value."/".$confFile['file'];

//					$handle = fopen ($filename, "r");
//					$contents = fread ($handle, filesize ($filename));	// Get the XML file for the emote pack
//					fclose ($handle);
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
					  if ($file) $confArray[$file] = $codet;
					  }
					}
					else
					{
					  echo "Unsupported XML File Format<br /><br />";
					  $no_error = FALSE;
					}


					// Save pack info in the database
					$tmp = addslashes(serialize($confArray));
					$File_type = EMOLAN_23;
				}

				if($confFile['type'] == "php")
				{
					echo EMOLAN_25."'".$value."'<br />";
					include_once(e_IMAGE."emotes/".$value."/".$confFile['file']);
					$File_type = EMOLAN_24;
					$tmp = $_emoteconf;		// Use consistent name
				}
				
				if ($no_error)
				{
				  if ($do_one)
				  {	// Assume existing pack
				    $sql->db_Update("core", "`e107_value`='{$tmp}' WHERE `e107_name`='emote_".$value."'");
				  }
				  else
				  {	// Assume new pack
				    $sql->db_Insert("core", "'emote_".$value."', '{$tmp}' ");
				  }
				  echo "<div style='text-align: center;'><b>".$File_type." '</b> ".$value."'</div>";
				}
				else
				{  // Error occurred
				  echo "<div style='text-align: center;'><b>".EMOLAN_27." '</b> ".$value."'</div>";
				}
			}
		}
	  if (count($pack_local))
	  {
	    foreach ($pack_local as $p => $d)
		{
	      echo "Missing files for pack: ".$p." - deleted in database<br />";
		  $sql->db_Delete("core","`e107_name` = 'emote_{$p}'");
		}
	  }
	  return TRUE;
	}
  
}
require_once("footer.php");

?>
