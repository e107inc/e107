<?php
// $form_ext_name = extended-user-field string
// Usage of $form_ext_name - "Name|type|values|default|applicable to|visible to"
//
//  default is the default value
//        applicable to refers to who will see it in the userpref screen
//  visible to refers to who will see it in the user screen (ie able to hide certain fields)
//  applicable to and visible to are the class id (a number)
//
// eg.      myfield|radio|$value1,$value2,$value3|$value3|0|255
// eg.      myfield|dropdown|$value1,$value2,$value3
// eg.      myfield|text|$size
// eg.      myfield|table|$db_table,$dbfield_value,$dbfield_displayname.
//          eg. User|table|db_user,user_id,user_name
// $tdclass = table style class  eg. forumheader3.
// $alignit = alignment of form element. left,right,center.


function user_extended_name($extended){
    // strips out the other information to just reveal the extended user-field name.
                        $ut = explode("|",$extended);
                        $ret = ($ut[0] != "") ? str_replace("_"," ",$ut[0]) : trim($extended);
                        return $ret;
                        }


function user_extended_edit($u_fieldnum,$form_ext_name,$tdclass="",$alignit="left"){

	global $pref,$key,$sql,$user_pref,$signup_ext,$_POST;
	$ut = explode("|",$form_ext_name);
	$u_name = ($ut[0] != "") ? str_replace("_"," ",$ut[0]) : trim($form_ext_name);
	$u_type = trim($ut[1]);
	$u_value = $ut[2];
	$v_default = $ut[3];
	$u_visible = $ut[4];
	if($ut[4] && check_class($ut[4])==FALSE){ return; }
	$ufield_name="ue_{$u_fieldnum}";

	$ret ="<tr><td class='".$tdclass."' style='vertical-align:top'>".$u_name.req($pref[$signup_ext])."</td>\n";
	$ret .="<td class='".$tdclass."' style='text-align:".$alignit."'><div style='text-align:left;width:10%;white-space:nowrap'>";
	$tmp = explode(",",$u_value);

	switch ($u_type) {
		case "radio":

		for ($i=0; $i<count($tmp); $i++) {
			$checked = ($tmp[$i] == $user_pref[$ufield_name] || ($tmp[$i] == $v_default && !$user_pref[$ufield_name])) ? " checked='checked'" : "";
			if(!USER){ $checked = ($_POST[$ufield_name] == $tmp[$i] || ($tmp[$i] == $v_default && !$_POST[$ufield_name]))? " checked='checked'" : ""; }
			$ret .="<input  type='radio' name='".$ufield_name."'  value='".$tmp[$i]."' $checked /> $tmp[$i] ";
			$ret .= ($pref['signup_ext_req'.$key] && $i==0 && (!USER))? "<span style='font-size:15px; color:red'> *</span>":"";
			$ret .="<br />";
		};
		$ret .="</div>";
		$ret .="</td></tr>\n\n";
		break;

		case "dropdown":
		$ret .= "\n<select class='tbox' style='width:200px'  name='".$ufield_name."'><option></option>\n";
		for ($i=0; $i<count($tmp); $i++) {
			$selected = ($user_pref[$ufield_name] == "$tmp[$i]" )? " selected='selected'" :  "";
			$ret .="<option value=\"".$tmp[$i]."\" ".$selected." >". $tmp[$i] ."</option>\n";
		};
		$ret .="</select>";
		$ret .= ($pref['signup_ext_req'.$key] && (!USER))? "<span style='font-size:15px; color:red'> *</span>":"";
		$ret .= "</div></td></tr>\n\n";

		break;

		case "text":
		if($u_value == ""){$u_value = "40";};
		$valuehere = ($_POST[$ufield_name])? $_POST[$ufield_name] : ($user_pref[$ufield_name])? $user_pref[$ufield_name] : $v_default;
		if(!USER && $_POST[$ufield_name]){ $valuehere = $_POST[$ufield_name];}
		$ret .="<input class='tbox' type='text' name='".$ufield_name."' size='".$u_value."' value='".$valuehere."' maxlength='200' />";
		$ret .= ($pref['signup_ext_req'.$key] && (!USER))? "<span style='font-size:15px; color:red'> *</span>":"";
		$ret .="</div></td></tr>\n\n";
		break;

		case "table":
		$ret .="
		<select class='tbox' style='width:200px'  name='".$ufield_name."'><option></option>";

		$tmp = explode(",",$u_value);
		$fieldid = $row[$tmp[1]];
		$fieldvalue = $row[$tmp[2]];
		$sql -> db_Select($tmp[0],"*","$tmp[1] !='' ORDER BY $tmp[2]");
		while($row = $sql-> db_Fetch()){
			$fieldid = $row[$tmp[1]];
			$fieldvalue = $row[$tmp[2]];
			$checked = ($fieldid == $user_pref[$ufield_name] || ($fieldid == $v_default && !$user_pref[$ufield_name]))? " selected='selected'" : "";
			if(!USER){ $checked = ($_POST[$ufield_name] == $fieldid)? " selected='selected'" : ($fieldid == $v_default)? " selected='selected'" : "";}
			$ret .="<option value='".$fieldid."' $checked > $fieldvalue </option>";
		}
		$ret .="</select>";
		$ret .= ($pref['signup_ext_req'.$key] && e_PAGE =="customsignup.php")? "<span style='font-size:15px; color:red'> *</span>":"";
		$ret .="</div></td></tr>";
		break;

		default:
		//    $ret = "<tr>
		//    <td style='width:20%' class='".$tdclass."'>".$form_ext_name."</td>
		//    <td style='width:80%; text-align:".$alignit."' class='".$tdclass."' nowrap>";
		$valuehere = ($_POST[$ufield_name])? $_POST[$ufield_name] : ($user_pref[$ufield_name])? $user_pref[$ufield_name] : $v_defualt;
		if(!USER){ $valuehere = $_POST[$ufield_name];}
		$ret .="<input class='tbox' type='text' name='".$ufield_name."' size='40' value='".$valuehere."' maxlength='200' />";
		$ret .= ($pref['signup_ext_req'.$key])? "<span style='font-size:15px; color:red'> *</span>":"";
		$ret .= "</div></td></tr>";
		break;
	}

	return $ret;
}

?>