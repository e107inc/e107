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
|     $Source: /cvs_backup/e107_0.7/e107_admin/language.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-12-26 21:26:26 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
require_once("auth.php");
if(!getperms("ML")){ header("location:".e_BASE."index.php"); exit;}
require_once(e_HANDLER."form_handler.php");
$rs = new form;


 // still requires some "LAN" work - once fully tested.

    $tabs = table_list(); // array("news","content","links");
    $lanlist = get_langlist();  // list of languages.

if(isset($_POST['submit_prefs']) ){

    $pref['multilanguage'] = $_POST['multilanguage'];

    save_prefs();
    $ns -> tablerender("Saved", "<div style='text-align:center'>Saved</div>");

}
// ----------------- delete tables ---------------------------------------------
if(isset($_POST['del_existing']) && $_POST['lang_choices']){
        if($_POST['confirm_remove_table']){

            $lang = strtolower($_POST['lang_choices']);
            foreach ($tabs as $del_table) {
               if(db_Table_exists($lang."_".$del_table)){
                       $message .= (mysql_query("DROP TABLE ".$mySQLprefix.$lang."_".$del_table)) ? $_POST['lang_choices']." ".$del_table." deleted<br />" : $_POST['lang_choices']." $del_table couldn't be deleted<br />";
               }
            }

        }else{
            $message = "You must check 'confirm delete' to delete a table";
        }

    $ns -> tablerender("Result", $message);
}


// ----------create tables -----------------------------------------------------

if(isset($_POST['create_tables']) && $_POST['language']){

    $table_to_copy = array();
    $lang_to_create = array();
    $message = "";

    foreach ($tabs as $value) {
        if($_POST[$value]){
            $lang = strtolower($_POST['language']);

            if(copy_table($value,$lang."_".$value,$_POST['drop'])){
                $message .= " ".$_POST['language']." ".$value." created<br />";
            }else{
                $message .= (!$_POST['drop'])? " ".$_POST['language']." ".$value." ".LANG_LAN_00."<br />": $_POST['language']." ".$value." ".LANG_LAN_01."<br />";
            }
        }elseif(db_Table_exists($lang."_".$value)){
            if($_POST['remove']){ // Remove table.
                $message .= (mysql_query("DROP TABLE ".$mySQLprefix.$lang."_".$value)) ? $_POST['language']." ".$value." deleted<br />" : $_POST['language']." $value ".LANG_LAN_02."<br />";
            }else{   // leave table.
                $message = $_POST['language']." ".$value." was disabled but left intact.";
            }
        }
    }
      $ns -> tablerender("Result", $message);
}

// ------------- render form ---------------------------------------------------



    $caption = "Languages";
    $text = MLAD_LAN_4."<br /><br />";


    // Choose Language to Edit:
    $text = "<div class='fborder' style='width:100%;text-align:center;height:150px;overflow:auto'>";

    $text .= "<table style='width:100%;text-align:center'>\n";
    $text .= "<tr><td class='fcaption'>".ADLAN_132."</td>";
    $text .= "<td class='fcaption'>".LANG_LAN_03."</td><td class='fcaption'>".LANG_LAN_04."</td>";
    $text .= "</tr>\n\n";
    sort($lanlist);
     for($i=0;$i<count($lanlist);$i++){
        $installed = 0;

        $text .= "<tr><td class='forumheader3' style='width:30%'>".$lanlist[$i]."</td><td class='forumheader3'>\n";
           foreach ($tabs as $tab_name) {
            if(db_Table_exists(strtolower($lanlist[$i])."_".$tab_name)){
                $text .= $tab_name.", ";
                $installed++;
            }

          }

        $text .= (!$installed)? "<div style='text-align:center'><i>".LANG_LAN_05."</i></div>" : "";
        $text .= "</td><td class='forumheader3' style='width:10%;white-space:nowrap;text-align:right'>\n";
        $text .= $rs -> form_open("post", e_SELF."?modify","lang_form_".str_replace(" ","_",$lanlist[$i]));
        $text .= "<div>\n";

        if($installed){
            $text .= " <input type='submit' class='button' name='edit_existing' value='".ADLAN_78."' />\n";
            $text .= " <input type='submit' class='button' name='del_existing' value='".ADLAN_79."' />\n";
        }else{
            $text .= "<input type='submit' class='button' name='edit_existing' value='".ADLAN_82."' />\n";
        }
        $text .= "<input type='hidden' name='lang_choices' value='".$lanlist[$i]."' />";
        $text .= "</div>";
        $text .= $rs -> form_close();
        $text .= "</td></tr>";

    }

    $text .= "</table></div>";

    $ns -> tablerender($caption, $text);

if(!$_POST['language'] && !$_POST['edit_existing']){
     multilang_prefs();
}
    unset($text);





        // Grab Language configuration. ---
    if($_POST['edit_existing']){


        $text = $rs -> form_open("post", e_SELF);
        $text .= "<div style='text-align:center'>";
        $text .= "<table class='fborder' style='width:96%'>\n";

        foreach ($tabs as $table_name) {
            $installed = strtolower($_POST['lang_choices'])."_".$table_name;
            if(!eregi($installed,$_POST['lang_choices'])){
              $text .= "<tr>
                      <td style='width:30%' class='forumheader3'>".ucfirst(str_replace("_"," ",$table_name))."</td>\n
                      <td style='width:70%' class='forumheader3'>\n";
              $selected = (db_Table_exists($installed)) ? "checked='checked'" : "";
              $text .= "<input type=\"checkbox\" name=\"$table_name\" value=\"1\" $selected />";
              $text .= "</td></tr>\n";
            }

        }

        $text .= "<tr><td class='forumheader3' colspan='2'>&nbsp;";
        $text .= "<input type='hidden' name='language' value='".$_POST['lang_choices']."' />";
        $text .="</td></tr>";

// ===========================================================================

// Drop tables ?
        $text .= "<tr><td class='forumheader3'><b>".LANG_LAN_07."</b></td>
        <td class='forumheader3'>".$rs -> form_checkbox("drop", 1)."\n
        <span class=\"smalltext\" >".LANG_LAN_08."</span></td></tr>\n";

        $text .= "<tr><td class='forumheader3'><b>".LANG_LAN_10."</b></td>
        <td class='forumheader3'>".$rs -> form_checkbox("remove", 1)."\n
        <span class=\"smalltext\" >".LANG_LAN_11."</span></td></tr>\n";

        $text .="<tr>\n
                <td colspan='2' style='width:100%; text-align: center;' class='forumheader' >\n
                ".$rs -> form_button("submit", "create_tables", LANG_LAN_06, "", LANG_LAN_06, "disabled");
           //     $text .= " ".$rs -> form_button("submit", "e107ml_delete", MLAD_LAN_34, "", MLAD_LAN_34);
                $text .= "</td>\n
        </tr>\n";
        $text .= "</table></div>\n";

        $text .= $rs -> form_close();
        $ns -> tablerender($_POST['lang_choices'], $text);
      }



require_once(e_ADMIN."footer.php");

// ---------------------------------------------------------------------------
function multilang_prefs(){
    global $ns,$pref;
  $text = "<div style='text-align:center'>
   <form method='post' action='".e_SELF."' name='linkform'>
   <table style='width:97%' class='fborder'>
   <tr>
   <td style='width:40%' class='forumheader3'>".LANG_LAN_12.": </td>
   <td style='width:60%' class='forumheader3'>";
   $checked = ($pref['multilanguage'] == 1) ? "checked='checked'" : "";
   $text .= "<input type='checkbox' name='multilanguage'  style='width:80%' value='1' $checked>
   </td>
   </tr>
   ";


   $text .="<tr style='vertical-align:top'>
   <td colspan='2' style='text-align:center' class='forumheader'>";
   $text .= "<input class='button' type='submit' name='submit_prefs' value='Save' />";
   $text .= "</td>
   </tr>
   </table>
   </form>
   </div>";

   $caption = LANG_LAN_13; // "Multi-Language Preferences";
   $ns -> tablerender($caption, $text);

}





// ----------------------------------------------------------------------------

 function db_Table_exists($table) {
                global $mySQLdefaultdb;
                $tables = mysql_list_tables($mySQLdefaultdb);
                while (list($temp) = mysql_fetch_array($tables)) {
                    if($temp == strtolower(MPREFIX.$table)) {
                    return TRUE;
                    }
                }
                return FALSE;
 }
// ----------------------------------------------------------------------------
// Cam's Alternative - requires MySQL 4.1+
// eg. copy_table("news","french_news",1);

function copy_table($oldtable,$newtable,$drop){

    $request = ($drop)? "DROP TABLE IF EXISTS $newtable":"";
    $request .= "CREATE TABLE ".MPREFIX.strtolower($newtable)." LIKE ".MPREFIX.$oldtable;
    if(mysql_query($request)){
        return TRUE;
    }else{
        return FALSE;
    }

}

// ----------------------------------------------------------------------------

function table_list(){  // grab default language lists.
    global $mySQLdefaultdb;

    $exclude[] = "banlist";         $exclude[] = "banner";
    $exclude[] = "cache";           $exclude[] = "core";
    $exclude[] = "online";          $exclude[] = "parser";
    $exclude[] = "plugin";          $exclude[] = "user";
    $exclude[] = "upload";          $exclude[] =  "userclass_classes";
    $exclude[] = "rbinary";         $exclude[] = "session";
    $exclude[] = "tmp";             $exclude[] = "flood";
    $exclude[] = "stat_info";       $exclude[] = "stat_last";
    $exclude[] = "submit_news";     $exclude[] = "rate";
    $exclude[] = "stat_counter";

    $exclude[] = "french_news"; // still trying to find an efficient way to remove
                               //  tables of non-default languages eg french_news. any ideas?

 //   print_r($search);

    $tables = mysql_list_tables($mySQLdefaultdb);
        while (list($temp) = mysql_fetch_array($tables)) {
        $e107tab = str_replace(MPREFIX,"",$temp);
           if(str_replace($exclude,"",$e107tab)){
               $tabs[] = $e107tab;
           }
        }

              return $tabs;
}

// ----------------------------------------------------------------------------

function get_langlist(){
        global $pref;
        $handle=opendir(e_LANGUAGEDIR);
        while ($file = readdir($handle)){
            if(!strstr($file,".") && $file!= $pref['sitelanguage'] && $file!="CVS"){
                    $lanlist[] = $file;

            }
        }
        closedir($handle);

      return $lanlist;

}


?>