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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_cat_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-03 23:31:40 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT CAT ----------------------------------------------------------
if(!$CONTENT_CAT_TABLE_START){
				$CONTENT_CAT_TABLE_START = "
				<div style='text-align:center'>
				<table style='width:95%' border='0'>\n";
}
if(!$CONTENT_CAT_TABLE){
				$CONTENT_CAT_TABLE = "
				<tr>
					<td>
						<table style='width:100%' border='0'>
							<tr>								
								<td style='vertical-align:top; white-space:nowrap; width:5%;'>{CONTENT_CAT_TABLE_ICON}</td>
								<td style='width:90%; background-color:transparent; padding-left:10px;'>
									{CONTENT_CAT_TABLE_HEADING}<br />
									{CONTENT_CAT_TABLE_SUBHEADING}<br />
									<span class='smalltext'>
									{CONTENT_CAT_TABLE_DATE} / {CONTENT_CAT_TABLE_AUTHORDETAILS} {CONTENT_CAT_TABLE_EPICONS}<br />
									</span>
									<span class='smalltext'>{CONTENT_CAT_TABLE_TEXT}</span>
								</td>
								<td class='smalltext' style='vertical-align:top; text-align:right; padding-top:2px; white-space:nowrap; width:5%;'>{CONTENT_CAT_TABLE_AMOUNT}</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr><td><div class='spacer'><br /></div></td></tr>\n";
}
if(!$CONTENT_CAT_TABLE_END){
				$CONTENT_CAT_TABLE_END = "
				</table>
				</div>\n";
}
// ##### ----------------------------------------------------------------------

// ##### CONTENT CAT_LIST -----------------------------------------------------
if(!$CONTENT_CAT_LIST_TABLE){
				$CONTENT_CAT_LIST_TABLE = "
				<div style='text-align:center'>
				<table style='width:95%' border='0'>
				<tr>
					<td style='width:5%; padding-top:6px; white-space:nowrap; text-align:center; vertical-align:top; '>
						{CONTENT_CAT_LIST_TABLE_ICON}
					</td>
					<td style='vertical-align:top; padding-top:2px;'>
						<table style='width:100%; vertical-align:top;'>
						<tr>
							<td style='padding:2px; vertical-align:top; border:0;'>
								{CONTENT_CAT_LIST_TABLE_HEADING}
							</td>
						</tr>
						<tr>
							<td style='padding:2px; vertical-align:top; border:0;'>
								<i>{CONTENT_CAT_LIST_TABLE_SUBHEADING}</i>
							</td>
						</tr>
						<tr>
							<td style='padding:2px; vertical-align:top; border:0;'>
								<span class='smalltext'>
								{CONTENT_CAT_LIST_TABLE_DATE} / {CONTENT_CAT_LIST_TABLE_AUTHORDETAILS}
								{CONTENT_CAT_LIST_TABLE_EPICONS} {CONTENT_CAT_LIST_TABLE_COMMENT}
								</span>
							</td>
						</tr>
						<tr>
							<td style='padding:2px; vertical-align:top; border:0;'>
								<span class='smalltext'>{CONTENT_CAT_LIST_TABLE_RATING}</span>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<span class='smalltext'>{CONTENT_CAT_LIST_TABLE_TEXT}</span>&nbsp;
					</td>
				</tr>
				</table>
				</div>";
}
// ##### ----------------------------------------------------------------------

// ##### CONTENT CAT_LIST SUB -------------------------------------------------
if(!$CONTENT_CAT_LISTSUB_TABLE_START){
				$CONTENT_CAT_LISTSUB_TABLE_START = "
				<div style='text-align:center'>
				<table style='width:95%' border='0'>";
				//<tr><td>".CONTENT_LAN_28."</td></tr>
}
if(!$CONTENT_CAT_LISTSUB_TABLE){
				$CONTENT_CAT_LISTSUB_TABLE = "
				<tr>
					<td>
						<table style='width:100%' border='0'>
							<tr>
								<td style='vertical-align:top; white-space:nowrap; width:20px; padding:0;'>{CONTENT_CAT_LISTSUB_TABLE_ICON}</td>
								<td class='smalltext' style='padding-left:10px;'>
									{CONTENT_CAT_LISTSUB_TABLE_HEADING} <i>{CONTENT_CAT_LISTSUB_TABLE_SUBHEADING}</i>
								</td>
								<td class='smalltext' style='width:5%; white-space:nowrap;'>
									{CONTENT_CAT_LISTSUB_TABLE_AMOUNT}
								</td>
							</tr>
						</table>
					</td>
				</tr>\n";
}
if(!$CONTENT_CAT_LISTSUB_TABLE_END){
				$CONTENT_CAT_LISTSUB_TABLE_END = "
				</table>
				</div>";
}
// ##### ----------------------------------------------------------------------

?>