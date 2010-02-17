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
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms('0'))
{
	header('location:'.e_BASE.'index.php');
	exit;
}
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

$text .= "
  <tr>
    <td class='fcaption'>&nbsp;</td>
    <td class='fcaption' style='font-weight: bold;'>".LAN_ADMINLOG_1."</td>
    <td class='fcaption' style='font-weight: bold;'>".LAN_ADMINLOG_2."</td>
    <td class='fcaption' style='font-weight: bold;'>".LAN_ADMINLOG_3."</td>
    <td class='fcaption' style='font-weight: bold;'>".LAN_ADMINLOG_4."</td>
    <td class='fcaption' style='font-weight: bold;'>".LAN_ADMINLOG_5."</td>
  </tr>\n";

while ($row = $sql -> db_Fetch()) {
	$datestamp = $gen->convert_date($row['dblog_datestamp'], 'short');
	$image = get_log_img($row['dblog_type']);
	$text .= "  <tr>\n";
	$text .= "    <td style='width: 16px;'>{$image}</td>\n";
	$text .= "    <td>{$datestamp}</td>\n";
	$text .= "    <td>".$tp->toHtml($row['dblog_title'],FALSE,"defs")."</td>\n";
	$text .= "    <td>".$tp->toHtml($row['dblog_remarks'],FALSE,"defs")."</td>\n";
	$text .= "    <td>{$row['dblog_ip']}</td>\n";
	$text .= ($row['user_name']) ? "    <td><a href='".e_BASE."user.php?id.{$row['dblog_user_id']}'>{$row['user_name']}</a></td>\n" : "    <td>{$row['dblog_user_id']}</td>\n";
	$text .= "  </tr>\n";
}

$text .= "</table></div>\n";

	$text .= "<div style='text-align:center'><br />".$tp->parseTemplate("{NEXTPREV={$parms}}")."</div>";

$ns->tablerender(LAN_ADMINLOG_0, $text);
require_once("footer.php");

function get_log_img($log_type) {
	switch ($log_type) {
		case E_LOG_INFORMATIVE:
			return "<img src='".e_IMAGE_ABS."admin_images/docs_16.png' alt='".LAN_ADMINLOG_6."' title='".LAN_ADMINLOG_7."' />";
		break;
		case E_LOG_NOTICE:
			return "<img src='".e_IMAGE_ABS."admin_images/notice_16.png' alt='".LAN_ADMINLOG_8."' title='".LAN_ADMINLOG_9."' />";
		break;
		case E_LOG_WARNING:
			return "<img src='".e_IMAGE_ABS."admin_images/blocked.png' alt='".LAN_ADMINLOG_10."' title='".LAN_ADMINLOG_11."' style='width:16p;height:16px'  />";
		break;
		case E_LOG_FATAL:
			return "<img src='".e_IMAGE_ABS."admin_images/nopreview.png' alt='".LAN_ADMINLOG_12."' title='".LAN_ADMINLOG_13."' />";
		break;
		case E_LOG_PLUGIN;
			return "<img src='".e_IMAGE_ABS."admin_images/plugins_16.png' alt='".LAN_ADMINLOG_6."' title='".LAN_ADMINLOG_6."' />";
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
