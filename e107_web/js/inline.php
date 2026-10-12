<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

$_E107['debug'] = false;
$_E107['no_forceuserupdate'] = true;
$_E107['no_menus'] = true;
$_E107['allow_guest'] = false; // allow crons to run while in members-only mode.
$_E107['no_maintenance'] = true;
// error_reporting(0); // suppress all errors
require_once("../../class2.php");


class e107InlineEdit
{

	function __construct()
	{

		$token = e107::getParser()->filter($_POST['token']);

		$editable = isset($_SESSION[e_parse_shortcode::EDITABLE_SESSION_KEY][$token]) ? $_SESSION[e_parse_shortcode::EDITABLE_SESSION_KEY][$token] : array();
		$perms = isset($editable['perms']) ? trim((string) $editable['perms']) : '';

		if(!ADMIN || !e_AJAX_REQUEST || empty($editable) || $perms === '' || !getperms($perms))
		{
			$ret['msg'] = "Access Denied";
			$ret['status'] = 'error';
			echo json_encode($ret);
			return;

		}

		$keys = array('sc','id','token');
		foreach($keys as $k)
		{
			if(empty($_POST[$k])){ return;	}
		}

			// unset($_SESSION['editable'][$token]);
		$shortcode = e107::getParser()->filter($_POST['sc']);

		$ret    = array();
		$id     = intval($_POST['id']);
		$table  = $editable['table'];
		$field  = $editable['shortcodes'][$shortcode]['field'];
		$pid    = $editable['pid'];
		$type   = $editable['shortcodes'][$shortcode]['type'];


		if(empty($field) || empty($pid) || empty($table))
		{
			$ret['msg'] = "Missing Data";
			$ret['status'] = 'error';
			echo json_encode($ret);
			return;
		}


		$content = e107::getParser()->toDB($_POST['content']);

		$srch 	= array("<!-- bbcode-html-start -->","<!-- bbcode-html-end -->","[html]","[/html]");
		$content = str_replace($srch,'',$content);
		$content = trim($content);

		if($type == 'html')
		{
			$content = '[html]'.$content.'[/html]';
		}
		else
		{
			$content = strip_tags($content);
		}

		$update = array(
			$field => $content,
			'WHERE' => $pid ." = ".$id . " LIMIT 1"
		);

		//	print_r($table);
		//	print_r($update);

		if(e107::getDb()->update($table, $update) !== false)
		{
			$ret['msg'] = "Saved"; // LAN_UPDATED; or LAN_SAVED
			$ret['status'] = 'ok';
		}
		else //FIXME only display error when query fails..
		{
			$ret['msg'] = "Saving Failed"; // LAN_UPDATED_FAILED;
			$ret['status'] = 'error';
			return;
		}


		echo json_encode($ret);
	}




}

new e107InlineEdit;


exit;

