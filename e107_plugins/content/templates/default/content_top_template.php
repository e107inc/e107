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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_top_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-03 23:31:40 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT TOP --------------------------------------------------
if(!$CONTENT_TOP_TABLE_START){
				$CONTENT_TOP_TABLE_START = "
				<div style='text-align:center'>
				<table style='width:95%'>";
}
if(!$CONTENT_TOP_TABLE){
				$CONTENT_TOP_TABLE = "
				<tr>
					<td style='width:5%; white-space:nowrap; text-align:center; vertical-align:top; '>
						{CONTENT_TOP_TABLE_ICON}
					</td>
					<td style='vertical-align:top; padding-top:2px;'>
						<table style='width:100%; vertical-align:top;' border='0'>
						<tr>
							<td style='width:70%; padding:2px; vertical-align:top; border:0;'>
								{CONTENT_TOP_TABLE_HEADING}
							</td>
							<td style='text-align:right; width:30%; white-space:nowrap; padding:2px; vertical-align:top; border:0;'>
								<span class='smalltext'>{CONTENT_TOP_TABLE_RATING}</span>
							</td>
						</tr>
						<tr>
							<td colspan='2' style='padding:2px; vertical-align:top; border:0;'>
								<span class='smalltext'>{CONTENT_TOP_TABLE_AUTHOR}</span>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<div class='spacer'><br /></div>
					</td>
				</tr>";
}
if(!$CONTENT_TOP_TABLE_END){
				$CONTENT_TOP_TABLE_END = "
				</table>
				</div>";
}
// ##### ----------------------------------------------------------------------

?>