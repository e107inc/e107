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
|     $Source: /cvs_backup/e107_0.7/e107_themes/templates/userposts_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:12:45 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
// ##### USERPOSTS_COMMENTS TABLE -----------------------------------------------------------------
if(!$USERPOSTS_COMMENTS_TABLE_START){
                $USERPOSTS_COMMENTS_TABLE_START = "
                <div style='text-align:center'>
                <table class='fborder' style='width:95%'>\n";
}
if(!$USERPOSTS_COMMENTS_TABLE){
                $USERPOSTS_COMMENTS_TABLE = "
                <tr>
                        <td class='forumheader3' style='vertical-align:middle; background-color:transparent; border:#517DB1 1px solid; width:10%; text-align:center'>
                                {USERPOSTS_COMMENTS_ICON}
                        </td>
                        <td class='forumheader3' style='padding:0; line-height:130%; vertical-align:top;'>
                                <table class='fborder' style='vertical-align:top; height:98%; width:100%; line-height:130%;' cellspacing='0' cellpadding='0'>
                                        <tr>
                                                <td class='forumheader' style='line-height:130%; font-size:12px;'>
                                                        {USERPOSTS_COMMENTS_HREF_PRE}<b>{USERPOSTS_COMMENTS_HEADING}</b></a> <span class='smalltext'>({USERPOSTS_COMMENTS_DATESTAMP}) ({USERPOSTS_COMMENTS_TYPE})</span>
                                                </td>
                                        </tr>
                                        <tr>
                                                <td class='forumheader3' style='line-height:130%;'>
                                                        {USERPOSTS_COMMENTS_COMMENT}&nbsp;
                                                </td>
                                        </tr>
                        </table>
                        </td>
                </tr>
                ";
}
if(!$USERPOSTS_COMMENTS_TABLE_END){
                $USERPOSTS_COMMENTS_TABLE_END = "
                </table>
                </div>\n";
}
// ##### ------------------------------------------------------------------------------------------

// ##### USERPOSTS FORUM TABLE --------------------------------------------------------------------
if(!$USERPOSTS_FORUM_TABLE_START){
                $USERPOSTS_FORUM_TABLE_START = "
                <div style='text-align:center'>
                <form method='post' action='".e_SELF."?".e_QUERY."'>
                <table class='fborder' style='width:95%'>\n";
}
if(!$USERPOSTS_FORUM_TABLE){
                $USERPOSTS_FORUM_TABLE .= "
                <tr>
                        <td class='forumheader3' style='vertical-align:middle; background-color:transparent; border:#517DB1 1px solid; width:10%; text-align:center'>
                                {USERPOSTS_FORUM_ICON}
                        </td>
                        <td class='forumheader3' style='padding:0; line-height:130%; vertical-align:top;'>
                                <table class='fborder' style='vertical-align:top; height:98%; width:100%; line-height:130%;' cellspacing='0' cellpadding='0'>
                                        <tr>
                                                <td class='forumheader' style='line-height:130%; font-size:12px;'>
                                                        {USERPOSTS_FORUM_TOPIC_HREF_PRE}<b>{USERPOSTS_FORUM_TOPIC_PRE} {USERPOSTS_FORUM_TOPIC}</b></a>
                                                        <span class='smalltext'>({USERPOSTS_FORUM_NAME_HREF_PRE}<b>{USERPOSTS_FORUM_NAME}</b></a>)</span>
                                                        <span class='smalltext'>({USERPOSTS_FORUM_DATESTAMP})</span>
                                                </td>
                                        </tr>
                                        <tr>
                                                <td class='forumheader3' style='line-height:130%;'>
                                                        {USERPOSTS_FORUM_THREAD}
                                                </td>
                                        </tr>
                        </table>
                        </td>
                </tr>
                \n";
}
if(!$USERPOSTS_FORUM_TABLE_END){
                $USERPOSTS_FORUM_TABLE_END = "
                <tr>
                        <td class='forumheader' colspan='2' style='text-align:right;'>
                                {USERPOSTS_FORUM_SEARCH}
                        </td>
                </tr>
                </table>
                </form>
                </div>\n";
}
// ##### ------------------------------------------------------------------------------------------


?>