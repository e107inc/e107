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
|     $Source: /cvs_backup/e107_0.8/e107_admin/db.php,v $
|     $Revision: 1.6 $
|     $Date: 2008-11-14 03:28:31 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
if (!getperms('0')) {
	header('location:'.e_BASE.'index.php');
	exit;
}
$e_sub_cat = 'database';

if (isset($_POST['db_update'])) {
	header("location: ".e_ADMIN."e107_update.php");
	exit;
}

if (isset($_POST['verify_sql'])) {
	header("location: ".e_ADMIN."db_verify.php");
	exit;
}

require_once("auth.php");



if(isset($_POST['delpref']) || (isset($_POST['delpref_checked']) && isset($_POST['delpref2']))  )
{
	del_pref_val();
}


if(isset($_POST['pref_editor']) || isset($_POST['delpref']) || isset($_POST['delpref_checked']))
{
	pref_editor();
	require_once("footer.php");
	exit;
}


if (isset($_POST['optimize_sql'])) {
	optimizesql($mySQLdefaultdb);
	require_once("footer.php");
	exit;
}


if (isset($_POST['backup_core'])) {
	backup_core();
	message_handler("MESSAGE", DBLAN_1);
}

if(isset($_POST['delplug']))
{
	delete_plugin_entry();

}

if (isset($_POST['plugin_scan']) || e_QUERY == "plugin" || $_POST['delplug']) {
	plugin_viewscan();
	require_once("footer.php");
	exit;
}

if (isset($_POST['verify_sql_record']) || isset($_POST['check_verify_sql_record']) || isset($_POST['delete_verify_sql_record']) ) {
	verify_sql_record();
	require_once("footer.php");
	exit;
}





$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>\n
	<table style='".ADMIN_WIDTH."' class='fborder'>

	<tr>
	<td style='width:70%' class='forumheader3'>".DBLAN_15."</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='db_update' value='".DBLAN_16."' /></td>
	</tr>

	<tr>
	<td style='width:70%' class='forumheader3'>".DBLAN_4."</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='verify_sql' value='".DBLAN_5."' /></td>
	</tr>

	<tr>
	<td style='width:70%' class='forumheader3'>".DBLAN_6."</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='optimize_sql' value='".DBLAN_7."' /></td>
	</tr>

	<tr>
	<td style='width:70%' class='forumheader3'>".DBLAN_28."</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='plugin_scan' value=\"".DBLAN_29."\" /></td>
	</tr>

	<tr>
	<td style='width:70%' class='forumheader3'>".DBLAN_19."</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='pref_editor' value='".DBLAN_20."' /></td>
	</tr>

	<tr>
	<td style='width:70%' class='forumheader3'>".DBLAN_8."</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='backup_core' value='".DBLAN_9."' />
	<input type='hidden' name='sqltext' value='$sqltext' />
	</td></tr>

	<tr>
	<td style='width:70%' class='forumheader3'>".DBLAN_35."</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='verify_sql_record' value='".DBLAN_36."' /></td>
	</tr>

	</table>
	</form>
	</div>";

$ns->tablerender(DBLAN_10, $text);

function backup_core() {
	global $pref, $sql;
	$tmp = base64_encode((serialize($pref)));
	if (!$sql->db_Insert("core", "'pref_backup', '{$tmp}' ")) {
		$sql->db_Update("core", "e107_value='{$tmp}' WHERE e107_name='pref_backup'");
	}
}

function optimizesql($mySQLdefaultdb) {

	$result = mysql_list_tables($mySQLdefaultdb);
	while ($row = mysql_fetch_row($result)) {
		mysql_query("OPTIMIZE TABLE ".$row[0]);
	}

	$str = "
		<div style='text-align:center'>
		<b>".DBLAN_11." $mySQLdefaultdb ".DBLAN_12.".</b>

		<br /><br />

		<form method='POST' action='".e_SELF."'>
		<input class='button' type='submit' name='back' value='".DBLAN_13."' />
		</form>
		</div>
		<br />";
	$ns = new e107table;
	$ns->tablerender(DBLAN_14, $str);

}


function plugin_viewscan()
{
  $error_messages = array(0 => DBLAN_31, 1 =>DBLAN_32, 2 =>DBLAN_33, 3 => DBLAN_34);
  $error_image = array("integrity_pass.png","integrity_fail.png","warning.png","blank.png");

		global $sql, $pref, $ns, $tp;
		require_once(e_HANDLER."plugin_class.php");
		$ep = new e107plugin;
		$ep->update_plugins_table(); // scan for e_xxx changes and save to plugin table.
		$ep->save_addon_prefs();  // generate global e_xxx_list prefs from plugin table.

		$ns -> tablerender(DBLAN_22, "<div style='text-align:center'>".DBLAN_23."<br /><br /><a href='".e_SELF."'>".DBLAN_13."</a></div>");

		$text = "<form method='post' action='".e_ADMIN."db.php' id='plug_edit'>
				<div style='text-align:center'>  <table class='fborder' style='".ADMIN_WIDTH."'>
				<tr><td class='fcaption'>".DBLAN_24."</td>
				<td class='fcaption'>".DBLAN_25."</td>
				<td class='fcaption'>".DBLAN_26."<br />".DBLAN_30."</td>
				<td class='fcaption'>".DBLAN_27."</td>
				</tr>";

        $sql -> db_Select("plugin", "*", "plugin_id !='' order by plugin_path ASC"); // Must order by path to pick up duplicates. (plugin names may change).
		while($row = $sql-> db_Fetch())
		{
			$text .= "<tr>
				<td class='forumheader3'>".$tp->toHtml($row['plugin_name'],FALSE,"defs,emotes_off")."</td>
                <td class='forumheader3'>".$row['plugin_path']."</td>
				<td class='forumheader3'>";
				
			if (trim($row['plugin_addons']))
			{
			  $nl_code = '';
			  foreach(explode(',',$row['plugin_addons']) as $this_addon)
			  {
			    $ret_code = 3;		// Default to 'not checked
			    if ((strpos($this_addon,'e_') === 0) && (substr($this_addon,-4,4) != '_sql'))
			    {
//			      echo "Checking: ".$row['plugin_path'].":".$this_addon."<br />";
			      $ret_code = $ep->checkAddon($row['plugin_path'],$this_addon);		// See whether spaces before opening tag or after closing tag
			    }
				$text .= "<div style='border-bottom:1px dotted #cccccc'>";
				$text .= "<img src='".e_IMAGE."fileinspector/".$error_image[$ret_code]."' alt=\"".$error_messages[$ret_code]."\" title=\"".$error_messages[$ret_code]."\" style='vertical-align:middle;height:16px;width:16px' />\n";
			    $text .= trim($this_addon);	// $ret_code - 0=OK, 1=content error, 2=access error
			    $text .= "</div>";
			  }
			}
			
			$text .= "</td>
				<td class='forumheader3' style='text-align:center'>";
            if($previous == $row['plugin_path'])
			{
				$delid 	= $row['plugin_id'];
				$delname = $row['plugin_name'];
				$text .= "<input class='button' type='submit' title='".LAN_DELETE."' value='Delete Duplicate' name='delplug[$delid]' onclick=\"return jsconfirm('".LAN_CONFIRMDEL." ID:$delid [$delname]')\" />\n";
			}
			else
			{
            	$text .= ($row['plugin_installflag'] == 1) ? DBLAN_27 : " "; // "Installed and not installed";
			}
			$text .= "</td>
			</tr>";
			$previous = $row['plugin_path'];
		}
//		$text .= "<tr><td colspan='4' class='forumheader3'>".DBLAN_30."</td></tr>";
        $text .= "</table></div></form>";
        $ns -> tablerender(ADLAN_CL_7, $text);

}


function pref_editor()
{
		global $pref,$ns,$tp;
		ksort($pref);

		$text = "<form method='post' action='".e_ADMIN."db.php' id='pref_edit'>
				<div style='text-align:center'>
				<table class='fborder' style='".ADMIN_WIDTH."'>
                <tr>
					<td class='fcaption'>".LAN_DELETE."</td>
					<td class='fcaption'>".DBLAN_17."</td>
					<td class='fcaption'>".DBLAN_18."</td>
					<td class='fcaption'>".LAN_OPTIONS."</td>
				</tr>";

         foreach($pref as $key=>$val)
		{
			$ptext = (is_array($val)) ? "<pre>".print_r($val,TRUE)."</pre>" : htmlspecialchars($val);
            $ptext = $tp -> textclean($ptext, 80);

			$text .= "
				<tr>
				<td class='forumheader3' style='width:40px;text-align:center'><input type='checkbox' name='delpref2[$key]' value='1' /></td>
				<td class='forumheader3'>".$key."</td>
                <td class='forumheader3' style='width:50%'>".$ptext."</td>
				<td class='forumheader3' style='width:20px;text-align:center'>
					<input type='image' title='".LAN_DELETE."' src='".ADMIN_DELETE_ICON_PATH."' name='delpref[$key]' onclick=\"return jsconfirm('".LAN_CONFIRMDEL." [$key]')\" />
       			</td>
			</tr>";
		}
        $text .= "<tr><td class='forumheader' colspan='4' style='text-align:center'>
			<input class='button' type='submit' title='".LAN_DELETE."' value=\"".DBLAN_21."\" name='delpref_checked' onclick=\"return jsconfirm('".LAN_CONFIRMDEL."')\" />
			</td>
			</tr>
		</table></div></form>";
        $text .= "<div style='text-align:center'><br /><a href='".e_SELF."'>".DBLAN_13."</a></div>\n";
        $ns -> tablerender(DBLAN_20, $text);

		return $text;

}



function del_pref_val(){
	global $pref,$ns,$e107cache;
	$del = array_keys($_POST['delpref']);
	$delpref = $del[0];

	if($delpref)
	{
   		unset($pref[$delpref]);
    	$deleted_list .= "<li>".$delpref."</li>";
	}
	if($_POST['delpref2']){

    	foreach($_POST['delpref2'] as $k=>$v)
		{
            $deleted_list .= "<li>".$k."</li>";
			unset($pref[$k]);
		}
	}

	$message = "<div><br /><ul>".$deleted_list."</ul></div>
	<div style='text-align:center'><br /><a href='".e_SELF."'>".DBLAN_13."</a></div>";
 	save_prefs();
	$e107cache->clear();
    $ns -> tablerender(LAN_DELETED,$message);

}

function delete_plugin_entry()
{
	global $sql,$ns;
	$del = array_keys($_POST['delplug']);
	$message = ($sql -> db_Delete("plugin", "plugin_id='".intval($del[0])."' LIMIT 1")) ? LAN_DELETED : LAN_DELETED_FAILED;
    $caption = ($message == LAN_DELETED) ? LAN_DELETED : LAN_ERROR;
    $ns -> tablerender($caption,$message);
}

function verify_sql_record(){
	global $ns, $sql, $sql2, $tp;

	if(!is_object($sql)){ $sql = new db; }
	if(!is_object($sql2)){ $sql2 = new db; }
	if(!is_object($sql3)){ $sql3 = new db; }

	$tables = array();
	$tables[] = 'rate';
	$tables[] = 'comments';

	if(isset($_POST['delete_verify_sql_record'])){

		$text = "<div style='font-weight:bold; text-align:center;'>";
		$text .= "ok, so you want to delete some records? not a problem at all!<br />";
		$text .= "but, since this is still an experimental procedure, i won't actually delete anything<br />";
		$text .= "instead, i will show you the queries that would be performed<br />";
		$text .= "<br />";

		foreach($_POST['del_dbrec'] as $k=>$v){
			
			if($k=='rate'){

				$keys = implode(", ", array_keys($v));
				$qry .= "DELETE * FROM rate WHERE rate_id IN (".$keys.")<br />";

			}elseif($k=='comments'){

				$keys = implode(", ", array_keys($v));
				$qry .= "DELETE * FROM comments WHERE comment_id IN (".$keys.")<br />";

			}

		}
		$text .= $qry;

		$text .= "<br />
		<form method='post' action='".e_SELF."'>
		<table border=0 align='center'>
			<tr><td><input class='button' type='submit' name='back' value='".DBLAN_13."' /></td></tr>
		</table>
		</form>
		</div>";

		$ns->tablerender($caption, $text);

		return;
	}

	if(!isset($_POST['check_verify_sql_record'])){
		//select table to verify
		$text = "
		<form method='post' action='".e_SELF."'>
		<table border=0 align='center'>
		<tr><td>".DBLAN_37."<br /><br />";
			foreach($tables as $t){
				$text .= "<input type='checkbox' name='table_{$t}' />{$t}<br /";
			}
			$text .= "
			<br />
			<input class='button' name='check_verify_sql_record' type='submit' value='".DBLAN_38."' />
			<input class='button' type='submit' name='back' value='".DBLAN_13."' />
		</td></tr>
		</table>
		</form>";

		$ns->tablerender(DBLAN_39, $text);
	}else{


		//function to sort the results
		function verify_sql_record_cmp($a, $b) {
			
			$orderby=array('type'=>'asc', 'itemid'=>'asc');

			$result= 0;
			foreach( $orderby as $key => $value ) {
				if( $a[$key] == $b[$key] ) continue;
				$result = ($a[$key] < $b[$key])? -1 : 1;
				if( $value=='desc' ) $result = -$result;
				break;
			}
			return $result;
		}

		//function to display the results
		//$err holds the error data
		//$ctype holds the tablename
		function verify_sql_record_displayresult($err, $ctype){

			usort($err, 'verify_sql_record_cmp');

			$text = '';
			if(is_array($err) && !empty($err)){

				$text .= "
				<table class='fborder' style='".ADMIN_WIDTH."'>
				<tr><td class='fcaption' colspan='4'>".DBLAN_40." ".$ctype."</td></tr>
				<tr>
					<td class='fcaption' style='width:20%;'>".DBLAN_41."</td>
					<td class='fcaption' style='width:10%;'>".DBLAN_42."</td>
					<td class='fcaption' style='width:50%;'>".DBLAN_43."</td>
					<td class='fcaption' style='width:20%;'>".DBLAN_44."</td>
				</tr>";

				foreach($err as $k=>$v){
					$delkey = $v['sqlid'];
					$text .= "
					<tr>
						<td class='forumheader3'>".$v['type']."</td>
						<td class='forumheader3'>".$v['itemid']."</td>
						<td class='forumheader3'>".($v['table_exist'] ? DBLAN_45 : DBLAN_46)."</td>
						<td class='forumheader3'><input type='checkbox' name=\"del_dbrec[$ctype][$delkey][]\" value='1' /> ".DBLAN_47."</td>
					</tr>";
				}
				$text .= "
				<tr>
					<td class='fcaption' colspan='3'></td>
					<td class='fcaption'>
						<input class='button' name='delete_verify_sql_record' type='submit' value='".DBLAN_48."' />
						<input class='button' type='submit' name='back' value='".DBLAN_13."' />
					</td>
				</tr>
				</table><br />";
			}

			return $text;
		}

		function verify_sql_record_gettables(){
			global $sql2;

			//array which will hold all db tables
			$dbtables = array();

			//get all tables in the db
			$sql2 -> db_Select_gen("SHOW TABLES");
			while($row2=$sql2->db_Fetch()){
				$dbtables[] = $row2[0];
			}
			return $dbtables;
		}

		$text = '';

		//validate rate table records
		if(isset($_POST['table_rate'])){

			$query = "
			SELECT r.*
			FROM #rate AS r
			WHERE r.rate_id!=''
			ORDER BY r.rate_table, r.rate_itemid";
			$data = array('type'=>'rate', 'table'=>'rate_table', 'itemid'=>'rate_itemid', 'id'=>'rate_id');

			if(!$sql -> db_Select_gen($query)){
				$text = DBLAN_49;
			}else{

				//the master error array
				$err=array();

				//array which will hold all db tables
				$dbtables = verify_sql_record_gettables();

				while($row=$sql->db_Fetch()){

					$ctype		= $data['type'];
					$cid		= $row[$data['id']];
					$citemid	= $row[$data['itemid']];
					$ctable		= $row[$data['table']];

					//if the rate_table is an existing table, we need to do more validation
					//else if the rate_table is not an existing table, this is an invalid reference
					if(in_array($ctable, $dbtables)){

						$sql3 -> db_Select_gen("SHOW COLUMNS FROM {$ctable}");
						while($row3=$sql3->db_Fetch()){
							//find the auto_increment field, since that's the most likely key used
							if($row3['Extra']=='auto_increment'){
								$aif = $row3['Field'];
								break;
							}
						}

						//we need to check if the itemid (still) exists in this table
						//if the record is not found, this could well be an obsolete record
						//if the record is found, we need to keep this record since it's a valid reference
						if(!$sql2 -> db_Select("{$ctable}", "*", "{$aif}='{$citemid}' ORDER BY {$aif} ")){
							$err[] = array('type'=>$ctable, 'sqlid'=>$cid, 'table'=>$ctable, 'itemid'=>$citemid, 'table_exist'=>TRUE);
						}

					}else{
						$err[] = array('type'=>$ctable, 'sqlid'=>$cid, 'table'=>$ctable, 'itemid'=>$citemid, 'table_exist'=>FALSE);
					}
				}

				$text .= verify_sql_record_displayresult($err, $ctype);
			}
		}

		//validate comments table records
		if(isset($_POST['table_comments'])){

			$query = "
			SELECT c.*
			FROM #comments AS c
			WHERE c.comment_id!=''
			ORDER BY c.comment_type, c.comment_item_id";
			$data = array('type'=>'comments', 'table'=>'comment_type', 'itemid'=>'comment_item_id', 'id'=>'comment_id');

			if(!$sql -> db_Select_gen($query)){
				$text = DBLAN_49;
			}else{

				//the master error array
				$err=array();

				//array which will hold all db tables
				$dbtables = verify_sql_record_gettables();

				//get all e_comment files and variables
				require_once(e_HANDLER."comment_class.php");
				$cobj = new comment;
				$e_comment = $cobj->get_e_comment();

				while($row=$sql->db_Fetch()){

					$ctype		= $data['type'];
					$cid		= $row[$data['id']];
					$citemid	= $row[$data['itemid']];
					$ctable		= $row[$data['table']];

					//for each comment we need to validate the referencing record exists
					//we need to check if the itemid (still) exists in this table
					//if the record is not found, this could well be an obsolete record
					//if the record is found, we need to keep this record since it's a valid reference

					// news
					if($ctable == "0"){
						if(!$sql2 -> db_Select("news", "*", "news_id='{$citemid}' ")){
							$err[] = array('type'=>'news', 'sqlid'=>$cid, 'table'=>$ctable, 'itemid'=>$citemid, 'table_exist'=>TRUE);
						}
					//	article, review or content page
					}elseif($ctable == "1"){

					//	downloads
					}elseif($ctable == "2"){
						if(!$sql2 -> db_Select("download", "*", "download_id='{$citemid}' ")){
							$err[] = array('type'=>'download', 'sqlid'=>$cid, 'table'=>$ctable, 'itemid'=>$citemid, 'table_exist'=>TRUE);
						}

					//	poll
					}elseif($ctable == "4"){
						if(!$sql2 -> db_Select("polls", "*", "poll_id='{$citemid}' ")){
							$err[] = array('type'=>'polls', 'sqlid'=>$cid, 'table'=>$ctable, 'itemid'=>$citemid, 'table_exist'=>TRUE);
						}

					//	userprofile
					}elseif($ctable == "profile"){
						if(!$sql2 -> db_Select("user", "*", "user_id='{$citemid}' ")){
							$err[] = array('type'=>'user', 'sqlid'=>$cid, 'table'=>$ctable, 'itemid'=>$citemid, 'table_exist'=>TRUE);
						}

					//else if this is a plugin comment
					}elseif(isset($e_comment[$ctable]) && is_array($e_comment[$ctable])){
						$var = $e_comment[$ctable];
						$qryp='';
						//new method must use the 'qry' variable
						if(isset($var) && $var['qry']!=''){
							if($installed = $sql2 -> db_Select("plugin", "*", "plugin_path = '".$var['plugin_path']."' AND plugin_installflag = '1' ")){
								$qryp = str_replace("{NID}", $citemid, $var['qry']);
								if(!$sql2 -> db_Select_gen($qryp)){
									$err[] = array('type'=>$ctable, 'sqlid'=>$cid, 'table'=>$ctable, 'itemid'=>$citemid, 'table_exist'=>TRUE);
								}
							}
						//old method
						}else{
							if(!$sql2 -> db_Select($var['db_table'], $var['db_title'], $var['db_id']." = '{$citemid}' ")){
								$err[] = array('type'=>$ctable, 'sqlid'=>$cid, 'table'=>$ctable, 'itemid'=>$citemid, 'table_exist'=>TRUE);
							}
						}
					//in all other cases
					}else{
						$err[] = array('type'=>$ctable, 'sqlid'=>$cid, 'table'=>$ctable, 'itemid'=>$citemid, 'table_exist'=>FALSE);
					}

				}

				$text .= verify_sql_record_displayresult($err, $ctype);
			}
		}

		$text = "<form method='post' name='deleteform' action='".e_SELF."?".e_QUERY."'>".$text."</form>";
		$ns->tablerender(DBLAN_50,$text);
	}
}

require_once("footer.php");

?>