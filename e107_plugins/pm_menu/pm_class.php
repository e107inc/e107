<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/pm_class.php
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).	
+---------------------------------------------------------------+
*/
class pm {

	function pm_check($pmid,$type){
	/*
	# Check to see if user is sender,receiver,or either
	# Paremeter #1	- integer $pmid, ID of PM
	# Paremeter #2	- string $type, 'from', 'to', 'either'
	# Return -	boolean, TRUE on success, FALSE on failure
	*/
		global $sql;
		if($type != ""){
			$from = "pm_from_user = '".USERNAME."' ";
			$to = "pm_to_user = '".USERNAME."' ";
			if($type == "from"){$qry = $from;}
			if($type == "to"){$qry = $to;}
			if($type == "either"){$qry = $from." OR ".$to;}
			if($sql -> db_Select("pm_messages","*",$qry)){
				return TRUE;
			}
		} 
		return FALSE;
	}
		
	function delete_pm($pmid) {
	/*
	# Delete PM
	# Paremeter #1	- integer $pmid, ID of PM to delete
	# Return -	boolean, TRUE on success, FALSE on failure
	*/
		global $sql;
		if(!$this -> pm_check($pmid,"to")){return FALSE;}
		return ($sql -> db_Delete("pm_messages", "pm_id='".$pmid."' "));
	}

	function Delete_Expired() {
	/*
	# Delete expired messages
	# Paremeters - NONE
	# Return -	string, "$read_deleted,$unread_deleted"
	*/
		global $pref;
		global $ns;
		$oneday=86400;
		$time=time();
		$pm_sql=new db;
		$text="test<br />";
		if($pref['pm_read_timeout']>0){
			$msg_read_timeout=$time-($oneday*$pref['pm_read_timeout']);
			$pm_sql -> db_Select("pm_messages", "pm_id", "pm_rcv_datestamp > 0 AND pm_sent_datestamp < '".$msg_read_timeout."' ");
			while(list($pm_msg_id) = $pm_sql-> db_Fetch()){
				if($this->delete_pm($pm_msg_id)){$read_deleted++;}
			}
		}
		
		if($pref['pm_unread_timeout']>0){
			$msg_unread_timeout=$time-($oneday*$pref['pm_unread_timeout']);
			$pm_sql -> db_Select("pm_messages", "pm_id", "pm_rcv_datestamp = 0 AND pm_sent_datestamp < '".$msg_unread_timeout."' ");
			while(list($pm_msg_id) = $pm_sql-> db_Fetch()){
				if($this->delete_pm($pm_msg_id)){$unread_deleted++;}
			}
		}
		return $read_deleted.",".$unread_deleted;
	}

	function UserclassDropdownList(){
	/*
	# Create dropdown list of userclasses
	# Paremeters - NONE
	# Return -	string, drowdown list as form element 'option'
	*/
		global $pref;
		$ret="<option SELECTED value=''>".PMLAN_33."\n";
		if(ADMINPERMS=="0" && $pref['pm_userclass'] == e_UC_MEMBER){
			$ret.="<option value='ALL'>".PMLAN_48."\n";
		}
		if($pref['pm_userclass'] == e_UC_MEMBER){
			$pm_sql=new db;
			$pm_sql -> db_Select("userclass_classes", "userclass_id,userclass_name","ORDER BY userclass_name","nowhere");
			while(list($ucid,$ucname) = $pm_sql-> db_Fetch()){
				if(check_class($ucname) || ADMINPERMS=="0"){
					$ret.="<option value='".$ucid.":".$ucname."'>".$ucname."\n";
				}
			}
		} else {
			$pm_sql=new db;
			$pm_sql -> db_Select("userclass_classes", "userclass_id,userclass_name","userclass_id='{$pref['pm_userclass']}' ORDER BY userclass_name");
			while(list($ucid,$ucname) = $pm_sql-> db_Fetch()){
				$ret.="<option value='".$ucid.":".$ucname."'>".$ucname."\n";
			}
		}
		return $ret;
	}

	function UserDropdownList(){
	/*
	# Create dropdown list of users
	# Paremeters - NONE
	# Return -	string, drowdown list as form element 'option'
	*/
		global $pref;
		$qry.=" ORDER BY user_name";
		$ret="<option SELECTED value=''>".PMLAN_33."\n";
		$pm_sql=new db;
		$pm_sql -> db_Select("user", "user_name,user_class",$qry,"nowhere");
		while(list($uname,$uclass) = $pm_sql-> db_Fetch()){
			if($pref['pm_userclass'] == e_UC_MEMBER || check_class($pref['pm_userclass'],$uclass)){
				$ret.="<option value='".$uname."'>".$uname."\n";
			}
		}
		return $ret;
	}

	function MarkMsgRead($msgid) {
	/*
	# Mark PM as read
	# Paremeter #1 - integer $msgid, ID of PM to mark as read
	# Return -	integer, timestamp of msg read
	*/
		$readtime=time();
		$pm_sql=new db;
		$pm_sql -> db_Update("pm_messages", "pm_rcv_datestamp='".$readtime."' WHERE pm_id='".$msgid."' AND pm_rcv_datestamp='0'");
		return $readtime;
	}

	function read_summary($start=0,$np_query) {
	/*
	# Show summary of received messages
	# Paremeter #1 - integer $start, begining record number
	# Return -	String, entire summary table
	*/
		define("FTHEME", (file_exists(THEME."forum/newthread.png") ? THEME."forum/" : e_IMAGE."forum/"));

		global $ns;
		$max_per_page = 10;
		$ret="";
		$obj=new convert;
		$pm_sql=new db;
		$rcv_total = $pm_sql -> db_Select("pm_messages", "*", "pm_to_user = '".USERNAME."' ORDER BY pm_sent_datestamp ");
		if($rcv_total > $max_per_page){
			require_once(e_HANDLER."np_class.php");
			$np = new nextprev;
		}
		$count = $pm_sql -> db_Select("pm_messages", "*", "pm_to_user = '".USERNAME."' ORDER BY pm_sent_datestamp DESC LIMIT {$start},{$max_per_page}");
//		echo $rcv_total;
		$ret .= "<form name='delpm' method='post'>";
		$ret .= "<table style='width:97%; text-align:center' class='fborder'>";
		$ret.="<tr>";
		$ret.="<td style='width:1%; text-align:center' class='fcaption'>&nbsp;</td>";
		$ret.="<td style='width:9%; text-align:center' class='fcaption'>&nbsp;</td>";
		$ret.="<td colspan='2' style='width:45%; text-align:center' class='fcaption'>".PMLAN_PM."</td>";
		$ret.="<td style='width:20%; text-align:center' class='fcaption'>".PMLAN_34."</td>";
		$ret.="<td style='width:10%; text-align:center' class='fcaption'>".PMLAN_35."</td>";
		$ret.="<td style='width:25%; text-align:center' class='fcaption'>&nbsp;</td>";
		$ret.="</tr>";

		while(list($pm_msg_id, $pm_from_user, $pm_to_user, $pm_send_datestamp,$pm_rcv_datestamp,$pm_subject,$pm_message) = $pm_sql-> db_Fetch()){
			$ret.="<tr>";
			$ret .= "<td style='width:1%;' class='forumheader3'><input style='tbox' type='checkbox' name='delid[{$pm_msg_id}]'></td>";
			$ret.="<td style='width:9%; text-align:left' class='forumheader3'>";
			$ret.=$this->avatarget($pm_from_user);
			$ret.="</td>";
			$new_img = ($pm_rcv_datestamp > 0) ? "nonew.png" : "new.png";
			$ret.="<td style='width:5%; text-align:center' class='forumheader3'><img src='".FTHEME."{$new_img}' alt='' style='float:left' /></td>";
			$ret.="<td style='width:40%; text-align:left' class='forumheader3'><a href='".e_SELF."?view.".$pm_msg_id."'>".$pm_subject."</a></td>";
			$ret.="<td style='width:20%; text-align:center' class='forumheader3'>".$obj->convert_date($pm_send_datestamp,"short")."</td>";
			$ret.="<td style='width:10%; text-align:center' class='forumheader3'>".$this->userlink($pm_from_user)."</td>";
			$row=$this->is_blocked($pm_from_user,USERNAME);
			if(!$row){
				$ret.="<td style='width:25%; text-align:left' class='forumheader3'>[<a href='".e_SELF."?block.".$pm_from_user."'>".str_replace(" ","&nbsp;",PMLAN_38)."</a>]";
			} else {
				$ret.="<td style='width:25%; text-align:left' class='forumheader3'> Blocked &nbsp;";
			}
			$ret.=" [<a href='".e_SELF."?del.".$pm_msg_id."'>".PMLAN_40."</a>]</td>";
			$ret.="</tr>\n";
		}
		if($count){
			$ret .= "<tr><td colspan='7' style='text-align:left;'><input class='tbox' type='submit' name='delsel' value='".PMLAN_58."'></td></tr>";
		}
		$ret.="</table>\n";
		if($rcv_total > $max_per_page){
			ob_end_flush();
			ob_start();
			$np -> nextprev(e_SELF,$start,$max_per_page,$rcv_total,"",$np_query);
			$ret .= ob_get_contents();
			ob_end_clean();
		}
		return $ret;
	}

	function view_pm($msgnum) {
	/*
	# Display PM contents
	# Paremeter #1 	- integer $msgnum, ID of PM message
	# Return -	String, message contents or 0 if was not marked as read
	*/
		$ret="";
		$obj=new convert;
		$aj = new textparse;
		global $pref;
		$delete_on_read=$pref['pm_delete_read'];
		$pm_sql=new db;
		$pm_sql -> db_Select("pm_messages", "*", "pm_id = '{$msgnum}' ");
		list($pm_msg_id, $pm_from_user, $pm_to_user, $pm_send_datestamp,$pm_rcv_datestamp,$pm_subject,$pm_message) = $pm_sql-> db_Fetch();
		///check if correct 'to' user
		if($pm_to_user != USERNAME && $pm_from_user != USERNAME){
			pm_show_message(PMLAN_37);
			return "FAIL";
		}
		//  Mark Message as Read
		if(!$pm_rcv_datestamp && $pm_to_user == USERNAME){
			$this->MarkMsgRead($msgnum);
			return "MARK";
		}
		if($delete_on_read && $pm_to_user == USERNAME){
			$this->delete_pm($msgnum);
		}
		$ret.="<table style='width:97%; text-align:center' class='fborder'><tr><td>\n";
		$ret.="<table style='width:100%' class='fborder'>\n";
		$ret.="<tr>\n";
		$ret.="<td colspan='2' style='width:100%; text-align:center;' class='fcaption'>".$aj->tpa($pm_subject)."</td>\n";
		$ret.="</tr><tr>\n";
		$ret.= "<td style='width:20%; vertical-align:top;' class='forumheader3'>";
		if($pm_to_user == USERNAME){
			$ret .= "<div class='mediumtext'>".PMLAN_35.":<br /><b>".$this->UserLink($pm_from_user)."</b>";
		} else {
			$ret .= "<div class='mediumtext'>".PMLAN_31.":<br /><b>".$this->UserLink($pm_to_user)."</b>";
		}	
			
		$ret .= "<br /><br /></div><div class='smallblacktext'>".PMLAN_43."<br />".$obj->convert_date($pm_send_datestamp,"long")."<br /><br /></div>\n";
		$ret.="<div class='smallblacktext'>".PMLAN_44."<br />";
		$ret .= ($pm_rcv_datestamp) ? $obj->convert_date($pm_rcv_datestamp,"long") : PMLAN_49;
		$ret .= "<br />";
		if(!$delete_on_read && $pm_to_user == USERNAME){
			$ret.="<br />[<a href='".$PHP_SELF."?del.".$pm_msg_id."'>".PMLAN_40."</a>]";
		}
		$ret.="<br /></div></td>\n";
		$view_message=$pm_message;
		$view_message=$aj->tpa($view_message);
		$view_message = str_replace("\\r\\n","<br />", $view_message);
		$ret.="<td style='width:80%; vertical-align:top' class='forumtable2'>".$view_message."</td>\n";
		$ret.="</tr></table>\n";
		if($pm_to_user == USERNAME){
			$ret.="<table style='width:100%'>\n";
			$ret.="<tr><td style='text-align:center;'>
			<form action='".e_SELF."' method='post'>
			<input class='button' type='submit' name='reply' value='".PMLAN_41."'>
			<input type='hidden' name='msgid' value='".$pm_msg_id."'>
			<input type='hidden' name='uname' value='".$pm_from_user."'>
			<input type='hidden' name='sub' value='".stripslashes($pm_subject)."'>
			<input type='hidden' name='quoted' value='".stripslashes($pm_message)."'>
			<input class='tbox' type='checkbox' name='quote'><span class='smallblacktext'> ".PMLAN_42."</span>
			</form></td></tr></table>";
		}
		$ret.="</td></tr></table>\n";
		return $ret;
	}

	function sent_summary() {
	/*
	# Show summary of sent messages
	# Paremeters  	- NONE
	# Return -	String, entire summary table
	*/
		$ret="";
		$obj=new convert;
		$pm_sql=new db;
		$pm_sql -> db_Select("pm_messages", "*", "pm_from_user = '".USERNAME."' ORDER BY pm_sent_datestamp DESC");
		
		$ret.="<table style='width:97%; text-align:center' class='fborder'>";
		$ret.="<tr>";
		$ret.="<td colspan='2' style='text-align:center' class='fcaption'>".PMLAN_PM."</td>";
		$ret.="<td style='text-align:center' class='fcaption'>".PMLAN_43."</td>";
		$ret.="<td style='text-align:center' class='fcaption'>".PMLAN_31."</td>";
		$ret.="<td style='text-align:center' class='fcaption'>".PMLAN_44."</td></tr>";
		while(list($pm_msg_id, $pm_from_user, $pm_to_user, $pm_send_datestamp,$pm_rcv_datestamp,$pm_subject,$pm_message) = $pm_sql-> db_Fetch()){
			$ret.="<tr>";
			if($pm_rcv_datestamp > 0){
				$ret.="<td style='width:5%; text-align:center' class='forumheader3'><img src='images/nonew.png' alt='' /></td>";
			} else {
				$ret.="<td style='width:5%; text-align:center' class='forumheader3'><img src='images/new.png' alt='' /></td>";
			}
			$ret.="<td style='width:40%; text-align:left' class='forumheader3'><a href='".e_SELF."?view.".$pm_msg_id."'>".$pm_subject."</a></td>";
			$ret.="<td style='width:20%; text-align:center' class='forumheader3'>".$obj->convert_date($pm_send_datestamp,"short")."</td>";
			$ret.="<td style='width:10%; text-align:center' class='forumheader3'>".$this->userlink($pm_to_user)."</td>";
			if($pm_rcv_datestamp>0){
				$ret.="<td style='width:25%; text-align:center' class='forumheader3'>".$obj->convert_date($pm_rcv_datestamp,"short")."</td>";
			} else {
				$ret.="<td style='width:25%; text-align:center' class='forumheader3'>".PMLAN_49."</td>";
			}
			$ret.="</tr>\n";
		}
		$ret.="</table>\n";
		return $ret;
	}

	function send_message($uid="",$reply_msg="",$pm_subject="",$pm_message="") {
	/*
	# Show send message form
	# Paremeter #1 	- string $uid, ID of user to send message to
	# Paremeter #2 	- string $reply_msg, msg if replying
	# Paremeter #3 	- string $pm_subject, subject of PM
	# Paremeter #4 	- string $pm_message, PM Message body
	# Return -	String, entire form
	*/
		require_once(e_HANDLER."emote.php");
		require_once(e_HANDLER."ren_help.php");
		$pm_sql=new db;
		$ret="";
		global $pref;

		$ret.="
		<table style'width:100%' class='fborder'>
		<tr><td colspan='2' style='width:60%' class='fcaption'>".PMLAN_28."</td></tr>
		";
		global $user_dropdown;
		if($reply_msg){
			$pm_from_user=$_POST['uname'];
			$pm_subject=$_POST['sub'];
			$pm_message=$_POST['quoted'];
			if(MAGIC_QUOTES_GPC){ $pm_message = stripslashes($pm_message); }
			if(substr($pm_subject,0,3) != "RE:"){
				$pm_subject="RE:".$pm_subject;
			}
			if(!$_POST['quote']){
				$pm_message="";
			} else {
				$pm_message="[quote=".$pm_from_user."]".$pm_message."[/quote]\n\n";
			}
		}
		$ret.="<form method='post' id='pm' action='".e_SELF."'>\n";
		$ret.="<input type='hidden' name='from' value='".USERNAME."'>\n";
		if($pm_from_user){
			$ret.="<input type='hidden' name='to' value='".$pm_from_user."'>\n";
		}
		$ret.="<tr><td class='forumheader3'>".PMLAN_31.":</td>";
		$ret.="<td class='forumheader3'>";
		if($pm_from_user){
			$ret.="<b>".$pm_from_user."</b>\n";
		} else {
			if($user_dropdown){
				if($uid){
					$ret.="<input type='hidden' name='to' value='".$this->real_name($uid)."'>\n";
					$ret.="<b>".$this->real_name($uid)."</b>\n";
				} else {
					$ret.="<input type='radio' name='sendtype' value='user' checked> ".PMLAN_46."&nbsp; <select class='tbox' name='to' size='1'>".$this->UserDropdownList()."</select>";
					if(($pref['pm_userclass'] < e_UC_NOBODY && USERCLASS) || ADMINPERMS=="0"){
						$ret.="<br /><input type='radio' name='sendtype' value='userclass'> ".PMLAN_47."&nbsp; <select class='tbox' name='class' size='1'>".$this->UserclassDropdownList()."</select>";
					}
				}
			} else {
				if($uid){
					$ret.="<input type='hidden' name='to' value='".$this->real_name($uid)."'>\n";
					$ret.="<b>".$this->real_name($uid)."</b>\n";
				} else {
					$ret .= "<input class='tbox' type='text' name='to' maxlength='30'>";
					$ret .= "&nbsp;&nbsp;&nbsp;<a class='button' href='".e_PLUGIN."pm_menu/pm_finduser.php' onclick=\"window.open('".e_PLUGIN."pm_menu/pm_finduser.php','pmsearch', 'toolbar=no,location=no,status=yes,scrollbars=yes,resizable=yes,width=350,height=350,left=100,top=100'); return false;\">".PMLAN_53."... </a>";
					if(($pref['pm_userclass'] < e_UC_NOBODY && USERCLASS) || ADMINPERMS=="0"){
						$ret.="<br /><input type='radio' name='sendtype' value='userclass'> ".PMLAN_47."&nbsp; <select class='tbox' name='class' size='1'>".$this->UserclassDropdownList()."</select>";
					}
				}
			}
		}
		$ret.="</td></tr>\n";
		$ret.="<tr><td class='forumheader3'>".PMLAN_29."</td><td class='forumheader3'><input class='tbox' type='text' name='subject' value='".$pm_subject."'></td></tr>\n";
		$ret.="<tr><td class='forumheader3'>".PMLAN_30."</td><td class='forumheader3'><textarea id='pm_text' name='pm_text' rows='10' cols='50' class='tbox'>".$pm_message."</textarea>
		<br />
        <input class='helpbox' type='text' id='helpb' name='helpb' size='100' />
			<br />
		".ren_help(1, "addtextpm", "helppm")."
		</td></tr>";
		
		if($pref['smiley_activate']==1){
			$ret.="
			<tr><td class='forumheader3'>
			<div style='text-align:center'>".PMLAN_32."<br /></td><td class='forumheader3'>";
			$ret.=r_emote();
			$ret.="
			</td></div>
			</tr>\n
			";
		}
		$ret.="<tr><td class='forumheader' colspan='2' style='text-align:center;'><input class='button' type='submit' name='postpm' value='".PMLAN_9."'></td></tr>\n";
		$ret.="</form>";
		$ret.="</table>";
		return $ret;
	}

	function pm_send_email($user_from,$user_to,$to_email,$subject){
		global $PLUGINS_DIRECTORY;
	/*
	# Send email to user announcing new PM
	# Paremeter #1 	- String $user_to, username 
	# Paremeter #2 	- String $user_from, username 
	# Paremeter #3 	- String $to_email, email address 
	# Paremeter #4 	- String $subject, PM subject 
	# Return -	NONE
	*/
		$read_link=SITEURL;
		if(substr(SITEURL,-1,1) != "/"){$read_link .= "/";}
		$read_link .= $PLUGINS_DIRECTORY."pm_menu/pm.php?read\n\n";

		if($to_email){
			require_once(e_HANDLER."mail.php");
			$msg=$user_to.",\n\n".PMLAN_50." ".PMLAN_35.": ".$user_from."\n\n";
			$msg.=PMLAN_29." ".$subject."\n\n";
			$msg.=PMLAN_51.": ".SITENAME."\n\n";
//			$msg.=SITEURL.$PLUGINS_DIRECTORY."pm_menu/pm.php?read\n\n";
			$msg.=$read_link;
			return sendemail($to_email, PMLAN_50, $msg);
		}
		return 0;
	}

	function pm_post_message_user($pm_touser,$pm_text,$pm_subject,$pm_fromuser=USERNAME){
	/*
	# Post PM message to database
	# Paremeter #1 	- String $pm_touser, username to send PM to
	# Paremeter #2 	- String $pm_text, text of PM
	# Paremeter #3 	- String $pm_subject, subject of PM
	# Paremeter #4 	- String $pm_fromuser, username of PM sender
	# Return -	int, 0 if success, 1 if PM blocked, 2 if user not found.
	*/
		global $ns;
		global $pref;
		$tt = new textparse;
		
		$pm_sql=new db;
		$pm_sql -> db_Select("user", "*", "user_name LIKE '".$pm_touser."' ");
		if($userdata = $pm_sql-> db_Fetch()) {
			extract($userdata);
			$real_userid=$user_name;
			$row=$this->is_blocked($pm_fromuser,$real_userid);
			if(is_array($row)){
				extract($row);
				$block_count++;
				$pm_sql -> db_Update("pm_blocks", "block_count='".$block_count."' WHERE block_id='".$block_id."' ");
				return 1;
			}
			ini_set("max_execution_time",30);
			$aj = new textparse;
			if($pm_subject == ""){$pm_subject=PMLAN_57;}
			$pm_text=$tt -> formtpa($pm_text);
			$pm_subject = $tt -> formtpa($pm_subject);

			$vars="0,'{$pm_fromuser}','{$real_userid}','".time()."',0,'{$pm_subject}','{$pm_text}'";
			$pm_sql -> db_Insert("pm_messages", $vars);
			if($pref['pm_sendemail']){
				$this->pm_send_email($pm_fromuser,$real_userid,$user_email,$pm_subject);
			}
			return 0;
		} else {
			return 2;
		}
	}

	function users_in_userclass($userclass){
	/*
	# Get all users in a userclass
	# Paremeter 	- string $userclass, userclass
	# Return -	array
	*/
		$pm_sql=new db;
		$ret=array();
		$pm_sql -> db_Select("user", "user_name,user_class");
		while(list($uname,$uclass) = $pm_sql-> db_Fetch()){
			if(check_class($userclass,$uclass)){
				$ret[]=$uname;
			}
		}
		return $ret;
	}


	function post_pm(){
	/*
	# Post PM to individual user or userclass
	# Paremeters	- NONE
	# Return -	String success,blocked,notfound
	*/

		if($_POST['sendtype']=="userclass"){
			list($ucnum,$ucname)=explode(":",$_POST['class']);
			if($ucnum=="ALL"){
				if(ADMINPERMS != "0"){
					return "0,0,0";
				}
				$pm_sql=new db;
				$pm_sql -> db_Select("user", "user_name");
				while(list($uname) = $pm_sql-> db_Fetch()){
					$res=$this->pm_post_message_user($uname,$_POST['pm_text'],$_POST['subject']);
					if($res==0){$success++;}
					if($res==1){$blocked++;}
					if($res==2){$notfound++;}
				}
			} else {
				if(!check_class($ucnum)){
					return "0,0,0";
				}
				$userlist=$this->users_in_userclass($ucnum);
				foreach($userlist as $uname){
					$res=$this->pm_post_message_user($uname,$_POST['pm_text'],$_POST['subject']);
					if($res==0){$success++;}
					if($res==1){$blocked++;}
					if($res==2){$notfound++;}
				}
			}
		} else {
			$res=$this->pm_post_message_user($_POST['to'],$_POST['pm_text'],$_POST['subject']);
			if($res==0){$success++;}
			if($res==1){$blocked++;}
			if($res==2){$notfound++;}
		}
		return $success.",".$blocked.",".$notfound;
	}

	function view_block() {
	/*
	# Show current PM blocking info
	# Paremeter 	- NONE - uses constant USERNAME
	# Return -	String showing all blocking info
	*/
		$ret="";
		$obj=new convert;
		$pm_sql=new db;
		$pm_sql -> db_Select("pm_blocks", "*", "block_to = '".USERNAME."' ");
		$ret.="<table style='width:100%' class='fborder'>";
		$ret.="<tr>";
		$ret.="<td style='width:25%; text-align:center' class='fcaption'>".PMLAN_59."</td>";
		$ret.="<td style='width:30%; text-align:center' class='fcaption'>".PMLAN_60."</td>";
		$ret.="<td style='width:20%; text-align:center' class='fcaption'>".PMLAN_61."</td>";
		$ret.="<td style='width:25%; text-align:center' class='fcaption'>&nbsp; &nbsp;</td>";
		while(list($block_id, $block_from, $block_to, $block_timestamp,$block_count) = $pm_sql-> db_Fetch()){
			$ret.="<tr>";
			$ret.="<td style='text-align:left' class='forumheader3'>".$block_from."</td>";
			$ret.="<td style='text-align:left' class='forumheader3'>".$obj->convert_date($block_timestamp,"short")."</td>";
			$ret.="<td style='text-align:left' class='forumheader3'>".$block_count."</td>";
			$ret.="<td style='text-align:left' class='forumheader3'>[<a href='".$PHP_SELF."?unblock.".$block_from."'>".PMLAN_39."</a>]</td>";
			$ret.="</tr>\n";
		}
		$ret.="</table>\n";
		return $ret;
	}

	function UserLink($username){
	/*
	# Get link to user info
	# Paremeter 	- String $username, username
	# Return -	String link to user.php with username if valid user, otherwise return username
	*/
		$pm_sql=new db;
		$pm_sql -> db_Select("user", "user_id","user_name='".$username."' ");
		list($_uid) = $pm_sql-> db_Fetch();
		if($_uid){
			return "<a href='".e_BASE."user.php?id.".$_uid."'>".$username."</a>";
		} else {
			return $username;
		}
	}

	function real_name($_id) {
	/*
	# Get name as stored in database
	# Paremeter 	- String $_id, userid
	# Return -	String username
	*/
		$pm_sql = new db;
		$pm_sql -> db_Select("user","user_name","user_id='".$_id."' ");
		if(list($uname)=$pm_sql->db_Fetch()){return $uname;}
	}

	function avatarget($username){
	/*
	# Get path to user avatar image
	# Paremeter 	- String $username, username
	# Return -	path to avatar image if exists, &nbsp; if no avatar
	*/
		require_once(e_HANDLER."avatar_handler.php");
		$pm_sql=new db;
		$pm_sql -> db_Select("user", "user_image", "user_name='".$username."' ");
		if(list($user_image)= $pm_sql -> db_Fetch()){
			if(ltrim(rtrim($user_image))==""){
				return "&nbsp;";
			} else {
				return "<img src='".avatar($user_image)."' alt='' />";
			}
		} else {
			return "&nbsp;";
		}
	}

	function is_blocked($from,$to) {
	/*
	# Test for existing PM blocking
	# Paremeter #1		- String $from, username
	# Parameter #2		- String $to, username
	# Return -	array if exists, NULL if not exists
	*/
		$pm_sql=new db;
		$pm_sql -> db_Select("pm_blocks","*","block_to = '".$to."' AND block_from = '".$from."' ");
		if($row=$pm_sql->db_Fetch()){
			return $row;
		} else {
			return "";
		}
	}


	function unblock_user($from,$to){
	/*
	# Remove PM blocking
	# Paremeter #1		- String $from, username
	# Parameter #2		- String $to, username
	# Return -	1 on success, 0 on failure
	*/
		global $sql;
		if($row=$this->is_blocked($from,$to)){
			extract($row);
			$sql -> db_Delete("pm_blocks", "block_id='".$block_id."' ");
			return 1;
		} else {
			return 0;
		}
	}

	function block_user($from,$to){
	/*
	# Add PM blocking
	# Paremeter #1		- String $from, username
	# Parameter #2		- String $to, username
	# Return -	boolean, TRUE on success, FALSE on failure
	*/
		global $sql;
		$obj=new convert;
		$row=$this->is_blocked($from,$to);
		if(is_array($row)){
			return FALSE;
		} else {
			$vars="0,'".$from."','".USERNAME."',".time().",0";
			$sql -> db_Insert("pm_blocks", $vars);
			return TRUE;
		}
	}
}
function headerjs(){
$script_txt= "	<script type=\"text/javascript\">
function addtextpm(sc){
	document.getElementById('pm_text').value += sc;
}
function helppm(help){
	document.getElementById('helpb').value = help;
}
</script>\n";
return $script_txt;
}
?>