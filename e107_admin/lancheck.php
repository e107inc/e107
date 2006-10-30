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
|     $Source: /cvs_backup/e107_0.7/e107_admin/lancheck.php,v $
|     $Revision: 1.10 $
|     $Date: 2006-10-30 18:16:54 $
|     $Author: e107coders $
|	  With code from Izydor and Lolo.
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("0")) {
	header("location:".e_BASE."index.php");
	 exit;
}
$e_sub_cat = 'language';
require_once("auth.php");

	$qry = explode("|",e_QUERY);
	$f = $qry[0];
	$lan = $qry[1];
	$mode = $qry[2];

// Write the language file.
if(isset($_POST['submit'])){

  unset($input);
  $kom_start = chr(47)."*";
  $kom_end = "*".chr(47);
  if (!$mode) {
    $writeit = e_BASE.$LANGUAGES_DIRECTORY.$lan."/".$f;
  }
  else {
    $writeit = str_replace("English",$lan,$f);
  }
  $old_kom = "";
  $in_kom=0;
  $data = file($writeit);
  foreach($data as $line){
    //echo "line ->".$line."<br />";
    if (strpos($line,$kom_start) !== False && $old_kom == "") {
      $in_kom=1;
    }
    if ($in_kom) { $old_kom.=$line; }
    if (strpos($line,$kom_end) !== False && $in_kom) {$in_kom = 0;}
    }

  //echo "old_kom : ".$old_kom;

  $message = "<div style='height:250px;overflow:auto'><br />";
  $input .= chr(60)."?php\n";
  if ($old_kom == "") {
    $input .= chr(47)."*\n+---------------------------------------------------------------+\n|        e107 website system\n|        $writeit $lan language file\n|        Translated using translator plugin by Izydor (www.izydor.net)\n|\n|        ©Steve Dunstan 2001-2002\n|        http://e107.org\n|        jalist@e107.org\n|\n|        Released under the terms and conditions of the\n|        GNU General Public License (http://gnu.org).\n+---------------------------------------------------------------+\n*";
    $input .= chr(47)."\n\n";
  }
  else {$input.=$old_kom;}
  for ($i=0; $i<count($_POST['newlang']); $i++) {

    $notdef_start = "";
    $notdef_end = "\n";
    $deflang = stripslashes($_POST['newlang'][$i]);
    //echo "newdef -->".$_POST['newdef'][$i]."<br />";
    if (strpos($_POST['newdef'][$i],"ndef++") !== False ) {
      $defvar = str_replace("ndef++","",$_POST['newdef'][$i]);
      $notdef_start = "if (!defined(".chr(34).$defvar.chr(34).")) {";
      $notdef_end = "}\n";
      //echo "write (newdef,defvar,ndst,ndend) --> ".$_POST['newdef'][$i]." <> ".$defvar." <> ".$notdef_start." <> ".$notdef_end."<br />";
    }
    else {
      $defvar = $_POST['newdef'][$i];
    }

    $message .= $notdef_start.'define("'.htmlentities($defvar).'","'.$deflang.'");<br />'.$notdef_end;
    $input .= $notdef_start."define(".chr(34).$defvar.chr(34).", ".chr(34).$deflang.chr(34).");".$notdef_end;
  };
  $message .="<br />";
  $message .="</div>";
  $input .= "\n\n?>";

  // Write it.
  //echo "writeit  ->".$writeit;
  $fp = @fopen($writeit,"w");
  //$fp = @fopen($writeit,"r");
  if(!@fwrite($fp, $input)){
    $caption = TR_LAN_12;
    $message = TR_LAN_13;
  }else{
    $caption = TR_LAN_14." <b>$lan/".$writeit."</b>";
  }
 fclose($writeit);
 $ns -> tablerender($caption, $message);
 require_once(e_ADMIN."footer.php");
 exit;
}

// ============================================================================

// Edit the Language File.

if($f && $f != 'plugin' && $f != 'theme'){

	if (!$mode)
	{
    	$dir1 =  e_BASE.$LANGUAGES_DIRECTORY."English/";
    	$f1=$f;
    	$dir2 =  e_BASE.$LANGUAGES_DIRECTORY.$lan."/";
    	$f2=$f;
	}
	else
	{
    	$dir1 =  substr($f , 0 , strrpos($f,"/")+1);
    	$f1=substr($f,strrpos($f,"/")+1,strlen($f)-strrpos($f,"/"));

    	if ($mode == 'file')
		{
      		$dir2 =  $dir1;
      		$f2=str_replace("English",$lan,$f1);
    	}
    	elseif ($mode == 'dir')
		{
     		$dir2 = str_replace("English",$lan,$dir1);
     		$f2=$f1;
    	}

  	}

	edit_lanfiles($dir1,$dir2,$f1,$f2);

}

// ===========================================================================

$core_plugins = array(
	"alt_auth","banner_menu","blogcalendar_menu","calendar_menu","chatbox_menu",
	"clock_menu","comment_menu","compliance_menu","content","counter_menu",
	"featurebox","forum","gsitemap","integrity_check","lastseen","links_page",
	"linkwords","list_new","log","login_menu","newforumposts_main","newsfeed",
	"newsletter","online_extended_menu","online_menu","other_news_menu","pdf",
	"pm","poll","powered_by_menu","rss_menu","search_menu","sitebutton_menu",
	"trackback","tree_menu","userlanguage_menu","usertheme_menu"
	);

$core_themes = array("crahan","e107v4a","human_condition","interfectus","jayya",
	"khatru","kubrick","lamb","leaf","reline","sebes","vekna_blue");


if(isset($_POST['language_sel']) && isset($_POST['language'])){

	$ns -> tablerender(LAN_CHECK_3.": ".$_POST['language'],check_core_lanfiles($_POST['language']));
	$ns -> tablerender(LAN_CHECK_3.": ".$_POST['language']."/admin",check_core_lanfiles($_POST['language'],"/admin"));

	$plug_text = "<table class='fborder' style='".ADMIN_WIDTH."'>
	<tr>
	<td class='fcaption'>".LAN_PLUGIN."</td>
	<td class='fcaption'>Original File</td>
	<td class='fcaption'>".$_POST['language']." File</td>
	<td class='fcaption'>".LAN_OPTIONS."</tr>";

	foreach($core_plugins as $plugs)
	{
		if(is_readable(e_PLUGIN.$plugs))
		{
			$plug_text .= check_lanfiles('P',$plugs,"English",$_POST['language']);
		}
	}
	$plug_text .= "</table>";
	$ns -> tablerender(ADLAN_CL_7,$plug_text);

    $theme_text = "<table class='fborder' style='".ADMIN_WIDTH."'>
	<tr>
	<td class='fcaption'>Theme</td>
	<td class='fcaption'>Original File</td>
	<td class='fcaption'>".$_POST['language']." File</td>
	<td class='fcaption'>".LAN_OPTIONS."</tr>";
	foreach($core_themes as $them)
	{
		if(is_readable(e_THEME.$them))
		{
			$theme_text .= check_lanfiles('T',$them,"English",$_POST['language']);
		}
	}
	$theme_text .= "</table>";

	$ns -> tablerender("Themes",$theme_text);
  require_once(e_ADMIN."footer.php");
  exit;
}

/*
if(isset($_POST['plugin_sel']) && isset($_POST['plugin'])){
  $ns -> tablerender(TR_LAN_4.TR_LAN_28.$_POST['plugin'].TR_LAN_30.$_POST['target_lang'],check_lanfiles('P',$_POST['plugin'],"English",$_POST['target_lang']));
  require_once(e_ADMIN."footer.php");
  exit;
}

if(isset($_POST['theme_sel']) && isset($_POST['theme'])){
  $ns -> tablerender(TR_LAN_4.TR_LAN_29.$_POST['theme'].TR_LAN_30.$_POST['target_lang'],check_lanfiles('T',$_POST['theme'],"English",$_POST['target_lang']));
  require_once(e_ADMIN."footer.php");
  exit;
}
*/
// ============ Choose plugin  =====
/*if($f == 'plugin'){

  $handle=opendir(e_PLUGIN);
  while ($file = readdir($handle)){
    if(is_dir(e_PLUGIN.$file) && $file !='CVS' && $file != "." && $file != ".." && $file != "/" ){
      $pluginlist[] = $file;
    }
  }

  closedir($handle);
  sort($pluginlist);


  $handle=opendir(e_LANGUAGEDIR);
  while ($file = readdir($handle)){
    if(is_dir(e_LANGUAGEDIR.$file) && $file !='CVS' && $file !="English" && $file != "." && $file != ".." && $file != "/" ){
      $lanlist[] = $file;
    }
  }
  closedir($handle);

  $text = "<div style='text-align:center'>
  <form method='post' action='".e_SELF."' id='plug_choose'>
  <table style='width:85%' class='fborder'>
  ";

  $text .="<tr>
  <td style='width:60%' class='forumheader3'>".TR_LAN_22."</td>
  <td style='width:40%' class='forumheader3'>";
  $text .= "<select style='width:150px' class='tbox' name='plugin' ><option></option>";
  for ($i=0; $i<count($pluginlist); $i++) {
    $text .="<option value=\"".$pluginlist[$i]."\" $selected>".str_replace('_menu','',$pluginlist[$i])."</option>";
  }

  $text .= " </select>";
  $text .="</td></tr> ";

  $text .="<tr>
  <td style='width:60%' class='forumheader3'>".TR_LAN_3."</td>
  <td style='width:40%' class='forumheader3'>";
  $text .= "<select style='width:150px' class='tbox' name='target_lang' ><option></option>";
  for ($i=0; $i<count($lanlist); $i++) {
    $text .="<option value=\"".$lanlist[$i]."\" $selected>".str_replace('_menu','',$lanlist[$i])."</option>";
  }

  $text .= " </select>";
  $text .="</td></tr> ";

  $text .="<tr style='vertical-align:top'>
  <td colspan='2' style='text-align:center' class='forumheader'>";
  $text .= "<input class='button' type='submit' name='plugin_sel' value='".TR_LAN_2."' />";
  $text .= "</td>
  </tr>
  </table>
  </form>
  </div>";

  $ns -> tablerender(TR_LAN_23, $text);
  require_once(e_ADMIN."footer.php");
  exit;
}

// ============ Choose theme  =====
if($f == 'theme'){
  //echo "Wybieramy theme ".e_THEME;
  $handle=opendir(e_THEME);
  while ($file = readdir($handle)){
    if(is_dir(e_THEME.$file) && $file !='CVS' && $file != "." && $file != ".." && $file != "/" ){
      $themelist[] = $file;
    }
  }
  closedir($handle);
  sort($themelist);

  $handle=opendir(e_LANGUAGEDIR);
  while ($file = readdir($handle)){
    if(is_dir(e_LANGUAGEDIR.$file) && $file !='CVS' && $file !="English" && $file != "." && $file != ".." && $file != "/" ){
      $lanlist[] = $file;
    }
  }
  closedir($handle);


  $text = "<div style='text-align:center'>
  <form method='post' action='".e_SELF."' id='theme_choose'>
  <table style='width:85%' class='fborder'>
  ";

  $text .="<tr>
  <td style='width:60%' class='forumheader3'>".TR_LAN_24."</td>
  <td style='width:40%' class='forumheader3'>";
  $text .= "<select style='width:150px' class='tbox' name='theme' ><option></option>";
  for ($i=0; $i<count($themelist); $i++) {
    $text .="<option value=\"".$themelist[$i]."\" $selected>".$themelist[$i]."</option>";
  }

  $text .= " </select>";
  $text .="</td></tr> ";

  $text .="<tr>
  <td style='width:60%' class='forumheader3'>".TR_LAN_3."</td>
  <td style='width:40%' class='forumheader3'>";
  $text .= "<select style='width:150px' class='tbox' name='target_lang' ><option></option>";
  for ($i=0; $i<count($lanlist); $i++) {
    $text .="<option value=\"".$lanlist[$i]."\" $selected>".str_replace('_menu','',$lanlist[$i])."</option>";
  }

  $text .= " </select>";
  $text .="</td></tr> ";

  $text .="<tr style='vertical-align:top'>
  <td colspan='2' style='text-align:center' class='forumheader'>";
  $text .= "<input class='button' type='submit' name='theme_sel' value='".TR_LAN_2."' />";
  $text .= "</td>
  </tr>
  </table>
  </form>
  </div>";

  $ns -> tablerender(TR_LAN_25, $text);
  require_once(e_ADMIN."footer.php");
  exit;
}
*/

function get_lan_phrases($lang,$dir=Null){

	$ret = array();
	// Read English lan_ files
  	$base_dir = (!$dir) ? e_LANGUAGEDIR.$lang : $dir.$lang;

	if($r = opendir($base_dir))
	{
		while($file = readdir($r))
		{
      		$fname = $base_dir."/".$file;
      		if(preg_match("#^lan_#",$file) && is_file($fname))
			{
        		$data = file($fname);
        		$ret=$ret + fill_phrases_array($data,$file);
      		}
    	}
  		closedir($r);
	}

	return $ret;
}



function check_core_lanfiles($checklan,$subdir=''){
	global $lanfiles,$_POST;

	$English = get_lan_phrases("English".$subdir);
	$check = get_lan_phrases($checklan.$subdir);

	$text .= "<table class='fborder' style='".ADMIN_WIDTH."'>
	<tr>
	<td class='fcaption'>Original File</td>
	<td class='fcaption'>".$_POST['language']." File</td>
	<td class='fcaption'>".LAN_OPTIONS."</tr>";

	$keys = array_keys($English);
	sort($keys);
	foreach($keys as $k){
    $lnk = $k;
    if(array_key_exists($k,$check))
	{
		$text .= "<tr><td class='forumheader3' style='width:45%'>{$lnk}</td>";
      	$subkeys = array_keys($English[$k]);
      	sort($subkeys);
      	$er="";
      	foreach($subkeys as $sk){
        	if(!array_key_exists($sk,$check[$k]) || $check[$k][$sk] == "" ){
          		$er .= ($er) ? "<br />" : "";
          		$er .= $sk." ".LAN_CHECK_5;
        	}
      	}
      	if($er)
		{
        	$text .= "<td class='forumheader2' style='width:50%'><div class='smalltext'>{$er}</div></td>";
      	}
		else
		{
        	$text .= "<td class='forumheader3' style='width:50%'><div class='smalltext'>".LAN_CHECK_6."</div></td>";
      	}
    }
	else
	{
      $text .= "<tr>
				<td class='forumheader3' style='width:45%'>{$lnk}</td>
				<td class='forumheader' style='width:50%'>".LAN_CHECK_4."</td>";
    }
    // Leave in EDIT button for all entries - to allow re-translation of bad entries.
    $text .="<td class='forumheader3' style='width:5%;text-align:center'>
    <input class='tbox' type='button' style='width:60px' name='but_$i' value=\"".LAN_EDIT."\" onclick=\"window.location='".e_SELF."?".$k."|".$_POST['language']."'\" /> ";
    $text .="</td></tr>";
  }
  $text .= "</table>";

  return $text;
}


function get_lan_file_phrases($dir1,$dir2,$file1,$file2){

  //echo "function get_lan_file_phrases".$dir1." <> ".$dir2." <> ".$file1." <> ".$file2."<br />";

  $ret = array();
  $fname = $dir1.$file1;
  $type='oryg';
  //echo $fname."<br />";
  if(is_file($fname)){
    $data = file($fname);
    $ret=$ret + fill_phrases_array($data,$type);
  }

  $fname = $dir2.$file2;
  $type='tran';
  //echo $fname."<br />";
  if(is_file($fname)){
    $data = file($fname);
    $ret=$ret + fill_phrases_array($data,$type);
  }
  return $ret;
}


function get_comp_lan_phrases($comp_dir,$lang,$mode='root')
{
     $ret = array();
	// Check main /languages/ directory
		if($r = opendir($comp_dir)){
    		while($file = readdir($r))
			{
      			$fname = $comp_dir.$file;
      			//echo "_fname ".$fname."<br />";
      			if(preg_match("#".$lang."#",$file) && is_file($fname))
				{
        			//echo "fname ".$fname."<br />";
        			$data = file($fname);
        			$ret=$ret + fill_phrases_array($data,$file);
     			}
    		}
  			closedir($r);
  		}


	return $ret;
}

// for plugins and themes - checkes what kind of language files directory structure we have
function check_lanfiles($mode,$comp_name,$base_lan="English",$target_lan){
	global $ns,$sql;

  //echo "function check_lanfiles".$mode." <> ".$comp_name." <> ".$target_lan."<br />";
    $folder['P'] = e_PLUGIN.$comp_name;
	$folder['T'] = e_THEME.$comp_name;
	$comp_dir = $folder[$mode];

	if(is_readable($comp_dir."/languages/".$comp_name."_English.php"))
	{
    	$fname = $comp_dir."/languages/".$comp_name."_English.php";
	}
	else
	{
		$fname = $comp_dir."/languages/English.php";
	}

	$adfname = $comp_dir."/languages/admin";
	$dname = $comp_dir."/languages/English";
	$known=0;
//   echo $fname." :: ".$dname;
  // structure : plugin_directory/languages/lang.php
  if(is_file($fname) || $r = opendir($adfname)) {
    //$text = "To jest plik + admin katalog --> ".$adfname;

    $known=1;
    $baselang = get_comp_lan_phrases($comp_dir."/languages/","English");
    $check = get_comp_lan_phrases($comp_dir."/languages/",$target_lan);

    $text = "";
    $keys = array_keys($baselang);
    sort($keys);
    foreach($keys as $k){
      $lnk = $k;
      //echo "klucz ".$k."<br />";
      $k_check = str_replace("English",$target_lan,$k);
      if(array_key_exists($k_check,$check)){
        $text .= "<tr>
			<td class='forumheader3' style='width:20%'>".$comp_name."</td>
			<td class='forumheader3' style='width:25%'>{$lnk}</td>";
        $subkeys = array_keys($baselang[$k]);
        //sort($subkeys);
        $er="";
        foreach($subkeys as $sk){
          if(!array_key_exists($sk,$check[$k_check]) || $check[$k_check][$sk] == "" ){
            $er .= ($er) ? "<br />" : "";
            $er .= $sk." ".LAN_CHECK_5;
          }
        }
        if($er){
          $text .= "<td class='forumheader2' style='width:50%'><div class='smalltext'>{$er}</div></td>";
        } else {
          $text .= "<td class='forumheader3' style='width:50%'><div class='smalltext'>".LAN_CHECK_6."</div></td>";
        }
      } else {
        $text .= "<tr>
				<td class='forumheader3' style='width:20%'>".$comp_name."</td>
				<td class='forumheader3' style='width:25%'>{$lnk}</td>
				<td class='forumheader' style='width:50%'>".LAN_CHECK_4."</td>";
      }
      // Leave in EDIT button for all entries - to allow re-translation of bad entries.
      $text .="<td class='forumheader3' style='width:5%;text-align:center'>
      <input class='tbox' type='button' style='width:60px' name='but_$i' value=\"".LAN_EDIT."\" onclick=\"window.location='".e_SELF."?".$comp_dir."/languages/".$lnk."|".$target_lan."|file'\" /> ";
      $text .="</td></tr>";
    }


  }

  if($r = opendir($dname)) {
    //$text .= "To jest katalog --> ".$dname;
    $known = 1;
    $baselang = get_lan_phrases("English",$comp_dir."/languages/");
    $check = get_lan_phrases($target_lan,$comp_dir."/languages/");

    $keys = array_keys($baselang);
    sort($keys);
    foreach($keys as $k){
      $lnk = $k;
      //echo "klucz ".$k."<br />";
      if(array_key_exists($k,$check)){
        $text .= "<tr>
        <td class='forumheader3' style='width:20%'>".$comp_name."</td>
		<td class='forumheader3' style='width:25%'>{$lnk}</td>";
        $subkeys = array_keys($baselang[$k]);
        //sort($subkeys);
        $er="";
        foreach($subkeys as $sk){
          if(!array_key_exists($sk,$check[$k]) || $check[$k][$sk] == "" ){
            $er .= ($er) ? "<br />" : "";
            $er .= $sk." ".LAN_CHECK_5;
          }
        }
        if($er){
          $text .= "<td class='forumheader2' style='width:50%'><div class='smalltext'><b>{$k}</b><br />{$er}</div></td>";
        } else {
          $text .= "<td class='forumheader3' style='width:50%'><div class='smalltext'><b>{$k}</b><br />".LAN_CHECK_6."</div></td>";
        }
      } else {
        $text .= "<tr>
	<td class='forumheader3' style='width:20%'>".$comp_name."</td>
		<td class='forumheader3' style='width:25%'>{$lnk}</td>
		<td class='forumheader' style='width:50%'>".LAN_CHECK_4."</td>";
      }
      // Leave in EDIT button for all entries - to allow re-translation of bad entries.
      $text .="<td class='forumheader3' style='width:5%;text-align:center'>
      <input class='tbox' type='button' style='width:60px' name='but_$i' value=\"".LAN_EDIT."\" onclick=\"window.location='".e_SELF."?".$comp_dir."/languages/English/".$lnk."|".$target_lan."|dir'\" /> ";
      $text .="</td></tr>";
    }

  }

  if (!$known) {$text = TR_LAN_26." : --> ".$fname." :: ".$dname;}
  return $text;
}

function edit_lanfiles($dir1,$dir2,$f1,$f2){
  global $ns,$sql,$lan;

  //echo "function edit_lanfiles".$dir1." <> ".$dir2." <> ".$f1." <> ".$f2."<br />";

  $trans = get_lan_file_phrases($dir1,$dir2,$f1,$f2);
  $keys = array_keys($trans);
  sort($keys);

  //Check if directory is writeable
  if(!is_writable($dir2)){
    $text = "<br /><div style='text-align:center'>".TR_LAN_7.$dir2.$f2.TR_LAN_8;
    $text .= "<br /><br /><input class='button' type='button' name='back' value='".TR_LAN_9."' onclick=\"window.location='".e_SELF."' \" /></div>";
    $ns -> tablerender(TR_LAN_6, $text);
    require_once(e_ADMIN."footer.php");
    exit;
  }

  $text = "<div style='text-align:center'>
  <form method='post' action='".e_SELF."?".e_QUERY."' id='transform'>
  <table style='".ADMIN_WIDTH."' class='fborder'>";

  $subkeys = array_keys($trans['oryg']);
  foreach($subkeys as $sk){
    $rowamount = round(strlen($trans['oryg'][$sk])/34)+1;
    $hglt1=""; $hglt2="";
    if ($trans['tran'][$sk] == "") {$hglt1="<b><i><p style='color:red'>"; $hglt2="</p></b></i>";};
    $text .="<tr>
    <td class='forumheader3' style='width:10%;vertical-align:top'>".$hglt1.htmlentities($sk).$hglt1."</td>
    <td class='forumheader3' style='width:40%;vertical-align:top'>".htmlentities(str_replace("ndef++","",$trans['oryg'][$sk])) ."</td>";
    $text .="<td class='forumheader3' style='width:50%;vertical-align:top'>";
    $text .="<textarea  class='tbox' name='newlang[]' rows='$rowamount' cols='45' style='height:100%'>".str_replace("ndef++","",$trans['tran'][$sk])."</textarea>";
    //echo "oryg --> ".$trans['oryg'][$sk]."<br />";
    if (strpos($trans['oryg'][$sk],"ndef++") !== False) {
      //echo "+oryg --> ".$trans['oryg'][$sk]." <> ".strpos($trans['oryg'][$sk],"ndef++")."<br />";
      $text .= "<input type='hidden' name='newdef[]' value='ndef++".$sk."' />";
    }
    else { $text .= "<input type='hidden' name='newdef[]' value='".$sk."' />";}
    $text .="</td></tr>";
  };

  $text .="<tr style='vertical-align:top'>
  <td colspan='3' style='text-align:center' class='forumheader'>";
  $text .= "<input class='button' type='submit' name='submit' value='".TR_LAN_2."' />";
  $text .= "</td>
  </tr>
  </table>
  </form>
  </div>";

  $caption = TR_LAN_10." <b>".$dir2.$f2."</b> ".TR_LAN_11." <b>".$lan."</b>";
  $ns -> tablerender($caption, $text);
  require_once(e_ADMIN."footer.php");
  exit;

}

    function fill_phrases_array($data,$type) {

      $retloc = array();

      foreach($data as $line){
        //echo "line--> ".$line."<br />";
        if (strpos($line,"define(") !== False &&
            strpos($line,");") === False) {$indef=1;$bigline="";/*echo "big1 -->".$line."<br />";*/}
        if ($indef) {$bigline.=str_replace("\n","",$line);/*echo "big2 -->".$line."<br />";*/}
        if (strpos($line,"define(") === False &&
            strpos($line,");") !== False) {$indef=0;$we_have_bigline=1;/*echo "big3 -->".$line."<br />";*/}
        if ((strpos($line,"define(") !== False &&
            strpos($line,");") !== False &&
            substr(ltrim($line),0,2) != "//") ||
            $we_have_bigline ) {
          if ($we_have_bigline) {$we_have_bigline=0;$line=$bigline;/*echo "big -->".$line."<br />";*/}
          $ndef = "";
          //echo "_ndefline -->".$line."<br />";
          if (strpos($line,"defined(") !== False ) {
            $ndef = "ndef++";
            $line = substr($line,strpos($line,"define("));}
            //echo "ndefline -->".$line."<br />";
          if(preg_match("#\"(.*?)\".*?\"(.*)\"#",$line,$matches) ||
             preg_match("#\'(.*?)\'.*?\"(.*)\"#",$line,$matches) ||
             preg_match("#\"(.*?)\".*?\'(.*)\'#",$line,$matches) ||
             preg_match("#\'(.*?)\'.*?\'(.*)\'#",$line,$matches) ||
             preg_match("#\((.*?)\,.*?\"(.*)\"#",$line,$matches) ||
             preg_match("#\((.*?)\,.*?\'(.*)\'#",$line,$matches)){
            //echo "get_lan -->".$matches[1]." :: ".$ndef.$matches[2]."<br />";
            $retloc[$type][$matches[1]]=stripslashes($ndef.$matches[2]);
            }
         }
      }
      return $retloc;
    }


function lancheck_adminmenu() {

	include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_language.php");

	global $action,$pref;
	if ($action == "") {
		$action = "tools";
	}

	if($action == "modify"){
    	$action = "db";
	}
	$var['main']['text'] = LAN_PREFS;
	$var['main']['link'] = e_ADMIN."language.php";

	if(isset($pref['multilanguage']) && $pref['multilanguage']){
		$var['db']['text'] = LANG_LAN_03;
		$var['db']['link'] = e_ADMIN."language.php?db";
	}

	$var['tools']['text'] = ADLAN_CL_6;
	$var['tools']['link'] = e_ADMIN."language.php?tools";


	show_admin_menu(ADLAN_132, $action, $var);
}

?>
