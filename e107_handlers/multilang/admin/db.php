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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/multilang/admin/db.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-11-10 06:03:26 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

 // still requires some "LAN" work - once fully tested.


    $tabs = array("news","content","links");


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
                $message .= (!$_POST['drop'])? " ".$_POST['language']." ".$value." could not be created.(already exist)<br />": $_POST['language']." ".$value." was deleted(if existing) and created.<br />";
            }
        }elseif(db_Table_exists($lang."_".$value)){
            if($_POST['remove']){ // Remove table.
                $message .= (mysql_query("DROP TABLE ".$mySQLprefix.$lang."_".$value)) ? $_POST['language']." ".$value." deleted<br />" : $_POST['language']." $value couldn't be deleted<br />";
            }else{   // leave table.
                $message = $_POST['language']." ".$value." was disabled but left intact.";
            }
        }
    }
      $ns -> tablerender("Result", $message);
}

// ------------- render form ---------------------------------------------------

    $caption = MLAD_LAN_1;
    $text = MLAD_LAN_4."<br /><br />";


    // Choose Language to Edit:
    $text = "<div style='width:100%;text-align:center'>";
    $text .= $rs -> form_open("post", e_SELF."?db");
    $text .= "<table class='border' style='width:96%;text-align:center'>";
    $text .= "<tr><td class='forumheader' style='text-align:center'>";
    $text .= "Available Languages: ";
    $text .= "<select class='tbox' name='lang_choices'>";
    for($i=0;$i<$nbrlan;$i++){
        $p = 0;
        foreach ($tabs as $tab_name) {
           $p = (db_Table_exists(strtolower($lanlist[$i])."_".$tab_name)) ? $p+1 : $p;
        }
        $used   = ($p!=0)? " -active":"";
        $style  = ($p!=0)? "background-color:yellow" : "";

        $selected = ($_POST['lang_choices'] == $lanlist[$i]) ? "selected='selected'":"";
        $text .= "<option style='$style' value='".$lanlist[$i]."' $selected>".$lanlist[$i]." $used</option>";
    }
    $text .= "</select>";
    $text .= " <input type='submit' class='button' name='edit_existing' value='edit' />";
    $text .= " <input type='submit' class='button' name='del_existing' value='delete all' />";
    $text .= " <input type='checkbox' name='confirm_remove_table' value='1' /> Confirm delete";
    $text .= "</td></tr></table>";
    $text .= $rs -> form_close();

        // Grab Language configuration. ---
    if($_POST['edit_existing']){

        $text .= $rs -> form_open("post", e_SELF."?db");
        $text .= "<table class='fborder' style='width:96%'>\n";

        foreach ($tabs as $table_name) {
            $installed = strtolower($_POST['lang_choices'])."_".$table_name;
            $text .= "<tr>
                    <td style='width:30%' class='forumheader3'>".ucfirst($table_name)."</td>\n
                    <td style='width:70%' class='forumheader3'>\n";
            $selected = (db_Table_exists($installed)) ? "checked='checked'" : "";
            $text .= "<input type=\"checkbox\" name=\"$table_name\" value=\"1\" $selected />";
            $text .= "</td></tr>\n";

        }

        $text .= "<tr><td class='forumheader3' colspan='2'>&nbsp;";
        $text .= "<input type='hidden' name='language' value='".$_POST['lang_choices']."' />";
        $text .="</td></tr>";

// ===========================================================================

// Drop tables ?
        $text .= "<tr><td class='forumheader3'>".MLAD_LAN_29."</td>
        <td class='forumheader3'>".$rs -> form_checkbox("drop", 1)."\n
        <span class=\"smalltext\" >".MLAD_LAN_30."</span></td></tr>\n";

        $text .= "<tr><td class='forumheader3'>".MLAD_LAN_32."</td>
        <td class='forumheader3'>".$rs -> form_checkbox("remove", 1)."\n
        <span class=\"smalltext\" >".MLAD_LAN_33."</span></td></tr>\n";

        $text .="<tr>\n
                <td colspan='2' style='width:100%; text-align: center;' class='forumheader' >\n
                ".$rs -> form_button("submit", "create_tables", MLAD_LAN_15, "", MLAD_LAN_15, "disabled");
           //     $text .= " ".$rs -> form_button("submit", "e107ml_delete", MLAD_LAN_34, "", MLAD_LAN_34);
                $text .= "</td>\n
        </tr>\n";
        $text .= "</table>\n";

$text .= $rs -> form_close();

      }

$ns -> tablerender($caption, $text);

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

// Cam's Alternative - requires MySQL 4.1+  ----------------------------------
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






?>