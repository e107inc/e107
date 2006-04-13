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
|     $Source: /cvs_backup/e107_0.7/e107_admin/admin_log.php,v $
|     $Revision: 1.4 $
|     $Date: 2006-04-13 15:07:10 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");

require_once("auth.php");
$text = "";
$amount = 30;
$from = ($_GET['fm']) ? intval($_GET['fm']) : 0;

if(e_QUERY == 'purge')
{
	$sql->db_Delete('dblog');
}

$total = $sql -> db_Select("dblog", "*", "ORDER BY `dblog_datestamp` DESC", "no_where");
$query = "SELECT l.*, u.user_name FROM #dblog AS l LEFT JOIN #user AS u ON l.dblog_user_id = u.user_id  ORDER BY l.dblog_datestamp DESC LIMIT $from,$amount";
$sql -> db_Select_gen($query);

if(!is_object($gen)) {
	$gen = new convert;
}
	$parms = $total.",".$amount.",".$from.",".e_SELF.'?fm=[FROM]';
	$text .= "<div style='text-align:center'><br />".$tp->parseTemplate("{NEXTPREV={$parms}}")."<br /><br /></div>";
$text .= "<div id='admin_log'><table>\n";

//

$text .= "
  <tr>
    <td class='fcaption'>&nbsp;</td>
    <td class='fcaption' style='font-weight: bold;'>Date</td>
    <td class='fcaption' style='font-weight: bold;'>Title</td>
    <td class='fcaption' style='font-weight: bold;'>Description</td>
    <td class='fcaption' style='font-weight: bold;'>User IP</td>
    <td class='fcaption' style='font-weight: bold;'>User ID</td>
  </tr>\n";

while ($row = $sql -> db_Fetch()) {
	$datestamp = $gen->convert_date($row['dblog_datestamp'], 'short');
	$image = get_log_img($row['dblog_type']);
	$text .= "  <tr>\n";
	$text .= "    <td style='width: 16px;'>{$image}</td>\n";
	$text .= "    <td>{$datestamp}</td>\n";
	$text .= "    <td>{$row['dblog_query']}</td>\n";
	$text .= "    <td>{$row['dblog_remarks']}</td>\n";
	$text .= "    <td>{$row['dblog_ip']}</td>\n";
	$text .= ($row['user_name']) ? "    <td><a href='".e_BASE."user.php?id.{$row['dblog_user_id']}'>{$row['user_name']}</a></td>\n" : "    <td>{$row['dblog_user_id']}</td>\n";
	$text .= "  </tr>\n";
}

$text .= "</table></div>\n";

	$text .= "<div style='text-align:center'><br />".$tp->parseTemplate("{NEXTPREV={$parms}}")."</div>";

$ns->tablerender("Admin Log", $text);
require_once("footer.php");

function get_log_img($log_type) {
	switch ($log_type) {
		case E_LOG_INFORMATIVE:
			return "<img src='".e_IMAGE_ABS."admin_images/docs_16.png' alt='Informative Icon' title='Informative Message' />";
		break;
		case E_LOG_NOTICE:
			return "<img src='".e_IMAGE_ABS."admin_images/notice_16.png' alt='Notice Icon' title='Notice Message' />";
		break;
		case E_LOG_WARNING:
			return "<img src='".e_IMAGE_ABS."admin_images/blocked_16.png' alt='Warning Icon' title='Warning Message' />";
		break;
		case E_LOG_FATAL:
			return "<img src='".e_IMAGE_ABS."admin_images/nopreview_16.png' alt='Fatal Icon' title='Fatal Error Message' />";
		break;
	}
		return $log_type;
}

function headerjs() {
?>
<style type="text/css">
#admin_log td {
	border: 1px solid #000000;
	margin: 0px;
	padding: 2px;
}
#admin_log table {
	width: 99%;
	/*border-spacing: 0px;
	border-collapse: collapse;*/
}
</style>
<?php
}

?>