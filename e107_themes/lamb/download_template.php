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
|     $Source: /cvs_backup/e107_0.7/e107_themes/lamb/download_template.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-02-20 18:29:08 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
// ##### CAT TABLE --------------------------------------------------------------------------------
$DOWNLOAD_CAT_TABLE_RENDERPLAIN = TRUE;
if(!$DOWNLOAD_CAT_TABLE_START){
	$DOWNLOAD_CAT_TABLE_START = "
	<img src='".e_THEME."lamb/images/download.png' alt='' style='vertical-align: middle;' /> <span class='dlmain'>Downloads</span>\n";
}
if(!$DOWNLOAD_CAT_PARENT_TABLE){

                $DOWNLOAD_CAT_PARENT_TABLE .= "<br /><br /><br />
               <div class='dlcat'>{DOWNLOAD_CAT_MAIN_ICON} {DOWNLOAD_CAT_MAIN_NAME}</div>
                ";
}

if(!$DOWNLOAD_CAT_CHILD_TABLE){

                $DOWNLOAD_CAT_CHILD_TABLE .= "
               
				{DOWNLOAD_CAT_SUB_ICON}
               
                        {DOWNLOAD_CAT_SUB_NEW_ICON} &#183; {DOWNLOAD_CAT_SUB_NAME}
                        ";

                        if($DOWNLOAD_CAT_SUBSUB_NAME){
                                $DOWNLOAD_CAT_CHILD_TABLE .= "
                                <br /><span class='defaulttext'>
                                {DOWNLOAD_CAT_SUBSUB_LAN}
                                {DOWNLOAD_CAT_SUBSUB_NAME}
                                </span>";
                        }

                $DOWNLOAD_CAT_CHILD_TABLE .= "
                ";

}
if(!$DOWNLOAD_CAT_TABLE_END){
                $DOWNLOAD_CAT_TABLE_END = "<br /><br /><br /><br />
                {DOWNLOAD_CAT_SEARCH}
             ";
}
// ##### ------------------------------------------------------------------------------------------



// ##### LIST TABLE -------------------------------------------------------------------------------
$DOWNLOAD_LIST_TABLE_RENDERPLAIN = TRUE;
if(!$DOWNLOAD_LIST_TABLE_START){

                $DOWNLOAD_LIST_TABLE_START = "
<img src='".e_THEME."lamb/images/download.png' alt='' style='vertical-align: middle;' /> <span class='dlmain'>Downloads: {DOWNLOAD_CATEGORY}</span><br /><br />{DOWNLOAD_CATEGORY_DESCRIPTION}<br /><br />

<form method='post' action='".e_SELF."?".e_QUERY."'>
<span class='defaulttext'>".LAN_dl_38."</span>
<select name='order' class='tbox'>".
($order == "download_datestamp" ? "<option value='download_datestamp' selected='selected'>".LAN_dl_22."</option>" : "<option value='download_datestamp'>".LAN_dl_22."</option>").
($order == "download_requested" ? "<option value='download_requested' selected='selected'>".LAN_dl_18."</option>" : "<option value='download_requested'>".LAN_dl_18."</option>").
($order == "download_name" ? "<option value='download_name' selected='selected'>".LAN_dl_23."</option>" : "<option value='download_name'>".LAN_dl_23."</option>").
($order == "download_author" ? "<option value='download_author' selected='selected'>".LAN_dl_24."</option>" : "<option value='download_author'>".LAN_dl_24."</option>")."
</select>&nbsp;&nbsp;&nbsp;

<span class='defaulttext'>".LAN_dl_37."</span>
<select name='view' class='tbox'>".
($view == 5 ? "<option selected='selected'>5</option>" : "<option>5</option>").
($view == 10 ? "<option selected='selected'>10</option>" : "<option>10</option>").
($view == 15 ? "<option selected='selected'>15</option>" : "<option>15</option>").
($view == 20 ? "<option selected='selected'>20</option>" : "<option>20</option>").
($view == 50 ? "<option selected='selected'>50</option>" : "<option>50</option>")."
</select>
&nbsp;
                        
&nbsp;
<span class='defaulttext'>".LAN_dl_39."</span>
<select name='sort' class='tbox'>".
($sort == "ASC" ? "<option value='ASC' selected='selected'>".LAN_dl_25."</option>" : "<option value='ASC'>".LAN_dl_25."</option>").
($sort == "DESC" ? "<option value='DESC' selected='selected'>".LAN_dl_26."</option>" : "<option value='DESC'>".LAN_dl_26."</option>")."
</select>
&nbsp;
<input class='button' type='submit' name='goorder' value='".LAN_dl_27."' /><br /><br />

<table style='width:100%'>\n

<tr>
<td style='width:35%; text-align:left; font-weight: bold;'>".LAN_dl_28."</td>
<td style='width:20%; text-align:center; font-weight: bold;'>".LAN_dl_24."</td>
<td style='width:10%; text-align:center; font-weight: bold;'>".LAN_dl_21."</td>
<td style='width:5%; text-align:center; font-weight: bold;'>".LAN_dl_29."</td>
<td style='width:10%; text-align:center; font-weight: bold;'>".LAN_dl_12."</td>
<td style='width:5%; text-align:center; font-weight: bold;'>".LAN_dl_8."</td>
</tr>";

}

if(!$DOWNLOAD_LIST_TABLE){
                $DOWNLOAD_LIST_TABLE .= "
<tr>
<td style='text-align:left;'>{DOWNLOAD_LIST_NEWICON} {DOWNLOAD_LIST_NAME}</td>
<td style='text-align:center;'>{DOWNLOAD_LIST_AUTHOR}</td>
<td style='text-align:center;'>{DOWNLOAD_LIST_FILESIZE}</td>
<td style='text-align:center;'>{DOWNLOAD_LIST_REQUESTED}</td>
<td style='text-align:center;'>{DOWNLOAD_LIST_RATING}</td>
<td style='text-align:center;'>{DOWNLOAD_LIST_LINK} {DOWNLOAD_LIST_ICON}</td>
</tr>\n";
}

if(!$DOWNLOAD_LIST_TABLE_END){
                $DOWNLOAD_LIST_TABLE_END = "</table>\n</form><br /><br />
<div class='smalltext' style='text-align:right;'>{DOWNLOAD_LIST_TOTAL_AMOUNT} {DOWNLOAD_LIST_TOTAL_FILES}</div>\n";
}
// ##### ------------------------------------------------------------------------------------------


// ##### VIEW TABLE -------------------------------------------------------------------------------
$DOWNLOAD_VIEW_TABLE_RENDERPLAIN = TRUE;
if(!$DOWNLOAD_VIEW_TABLE_START){
                $DOWNLOAD_VIEW_TABLE_START = "

<img src='".e_THEME."lamb/images/download.png' alt='' style='vertical-align: middle;' /> <span class='dlmain'>Downloads: {DOWNLOAD_CATEGORY}</span><br /><br /><br />\n";
}

if(!$DOWNLOAD_VIEW_TABLE){
                $DOWNLOAD_VIEW_TABLE .= "
<div class='dlcat'>{DOWNLOAD_VIEW_NAME_LINKED}</div>
{DOWNLOAD_VIEW_AUTHOR_LAN}: {DOWNLOAD_VIEW_AUTHOR} ( {DOWNLOAD_VIEW_AUTHOREMAIL} ) ( {DOWNLOAD_VIEW_AUTHORWEBSITE} )<br /><br />
{DOWNLOAD_VIEW_DESCRIPTION}<br /><br />

 ";    
       

                if($DOWNLOAD_VIEW_IMAGE){
                $DOWNLOAD_VIEW_TABLE .= "
        <br /><span class='mediumtext'>{DOWNLOAD_VIEW_IMAGE}</span> | ";
                }

        $DOWNLOAD_VIEW_TABLE .= "
                <span class='mediumtext'>{DOWNLOAD_VIEW_FILESIZE_LAN}: {DOWNLOAD_VIEW_FILESIZE} | {DOWNLOAD_VIEW_REQUESTED_LAN}: {DOWNLOAD_VIEW_REQUESTED} | {DOWNLOAD_REPORT_LINK} 
				<br />{DOWNLOAD_VIEW_RATING}</span><br /><br />
";
}

if(!$DOWNLOAD_VIEW_TABLE_END){
                $DOWNLOAD_VIEW_TABLE_END = "
                </table>
                </div>\n";
}
// ##### ------------------------------------------------------------------------------------------

?>