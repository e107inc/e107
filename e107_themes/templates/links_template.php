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
|     $Source: /cvs_backup/e107_0.7/e107_themes/templates/links_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:12:45 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
// ##### MAIN TABLE -------------------------------------------------------------------------------
if(!$LINK_MAIN_TABLE_START){
                $LINK_MAIN_TABLE_START = "
                <div style='text-align:center'>
                <table class='fborder' style='width:95%'>\n";
}
if(!$LINK_MAIN_TABLE){
                $LINK_MAIN_TABLE .= "
                <tr>
                <td class='forumheader3' style='background-color:transparent; border:#517DB1 1px solid; width:10%; text-align:center' rowspan='2'>
                        {LINK_MAIN_ICON}
                </td>
                <td class='forumheader' style='width:90%'>
                        {LINK_MAIN_HEADING}
                </td>
                </tr>
                <tr>
                <td class='forumheader3'>
                        {LINK_MAIN_DESC}
                        <span class='smalltext'>(
                                {LINK_MAIN_NUMBER}
                        )</span>
                </td>
                </tr>\n";

}
if(!$LINK_MAIN_TABLE_END){
                $LINK_MAIN_TABLE_END = "
                <tr><td class='forumheader3' colspan='2' style='text-align:right;'>{LINK_MAIN_TOTAL}</td></tr>
                <tr><td class='forumheader3' colspan='2' style='text-align:right;'>{LINK_MAIN_SHOWALL}</td></tr>
                </table>
                </div>\n";
}
// ##### ------------------------------------------------------------------------------------------


// ##### CATEGORY LIST ----------------------------------------------------------------------------
if(!$LINK_CAT_TABLE_START){
                $LINK_CAT_TABLE_START = "
                <div style='text-align:center'>
                <table class='fborder' cellspacing='0' cellpadding='0' style='width:95%'><tr><td>\n";
}
if(!$LINK_CAT_TABLE){
                $LINK_CAT_TABLE .= "
                <table class='fborder' style='width:100%'>
                <tr>
                <td rowspan='2' class='forumheader3' style='background-color:transparent; border:#517DB1 1px solid; width:10%; text-align:center'>
                        {LINK_CAT_BUTTON}
                </td>
                <td class='forumheader' style='width:90%'>
                        {LINK_CAT_APPEND}{LINK_CAT_NAME}</a> <i>{LINK_CAT_URL}</i>
                </td>
                <td class='forumheader' style='white-space:nowrap;'>
                        {LINK_CAT_REFER}
                </td>
                </tr>
                <tr>
                <td colspan='2' class='forumheader3' style='line-height:130%;'>
                        {LINK_CAT_DESC}<br />
                </td>
                </tr>
                </table>\n";

}
if(!$LINK_CAT_TABLE_END){
                $LINK_CAT_TABLE_END = "
                </td>
                </tr>
                <tr>
                        <td class='forumheader3' colspan='2' style='text-align:right;'>
                                {LINK_CAT_SUBMIT}
                        </td>
                </tr>
                </table>
                </div>\n";
}
// ##### ------------------------------------------------------------------------------------------

// ##### SUBMIT -----------------------------------------------------------------------------------
if(!$LINK_SUBMIT_TABLE_START){
        $LINK_SUBMIT_TABLE_START = "
        <div style='text-align:center'>
        <form method='post' action='".e_SELF."'>
        <table class='fborder' cellspacing='0' cellpadding='0' style='width:95%'>";
}
if(!$LINK_SUBMIT_TABLE){
                $LINK_SUBMIT_TABLE .= "
                <tr>
                        <td colspan='2' style='text-align:center' class='forumheader2'>".LAN_93."</td>
                </tr>
                <tr>
                        <td class='forumheader3' style='width:30%'>".LAN_86."</td>
                        <td class='forumheader3' style='width:70%'>
                                {LINK_SUBMIT_CAT}
                        </td>
                </tr>
                <tr>
                        <td class='forumheader3' style='width:30%'><u>".LAN_94."</u></td>
                        <td class='forumheader3' style='width:30%'>
                                <input class='tbox' type='text' name='link_name' size='60' value='' maxlength='100' />
                        </td>
                </tr>
                <tr>
                        <td class='forumheader3' style='width:30%'><u>".LAN_95."</u></td>
                        <td class='forumheader3' style='width:30%'>
                                <input class='tbox' type='text' name='link_url' size='60' value='' maxlength='200' />
                        </td>
                </tr>
                <tr>
                        <td class='forumheader3' style='width:30%'><u>".LAN_96."</u></td>
                        <td class='forumheader3' style='width:30%'>
                                <textarea class='tbox' name='link_description' cols='59' rows='3'></textarea>
                        </td>
                </tr>
                <tr>
                        <td class='forumheader3' style='width:30%'>".LAN_97."</td>
                        <td class='forumheader3' style='width:30%'>
                                <input class='tbox' type='text' name='link_button' size='60' value='' maxlength='200' />
                        </td>
                </tr>
                <tr>
                        <td colspan='2' style='text-align:center' class='forumheader3'><span class='smalltext'>".LAN_106."</span></td>
                </tr>
                <tr>
                        <td colspan='2' style='text-align:center' class='forumheader'>
                                <input class='button' type='submit' name='add_link' value='".LAN_98."' />
                        </td>
                </tr>
                ";

}
if(!$LINK_SUBMIT_TABLE_END){
        $LINK_SUBMIT_TABLE_END = "
        </table></form></div>\n";
}
// ##### ------------------------------------------------------------------------------------------


?>