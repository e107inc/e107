<?php
// $form_ext_name = extended-user-field string
// Usage of $form_ext_name - "Name|type|values"
//
// eg.      myfield|radio|$value1,$value2,$value3
// eg.      myfield|dropdown|$value1,$value2,$value3
// eg.      myfield|text|$size
// eg.      myfield|table|$db_table,$dbfield_value,$dbfield_displayname.
//          eg. User|table|db_user,user_id,user_name
// $tdclass = table style class  eg. forumheader3.
// $alignit = alignment of form element. left,right,center.


function user_extended_name($extended){
    // strips out the other information to just reveal the extended user-field name.
                        $ut = explode("|",$extended);
                        $ret = ($ut[0] != "") ? $ut[0] : trim($extended);
                        return $ret;
                        }


function user_extended_edit($form_ext_name,$tdclass="",$alignit="left"){

                        global $pref,$key,$sql,$user_pref;$_POST;
                        $ut = explode("|",$form_ext_name);
                        $u_name = ($ut[0] != "") ? $ut[0] : trim($form_ext_name);
                        $u_type = trim($ut[1]);
                        $u_value = $ut[2];

                        switch ($u_type) {
                          case "radio":
                            $ret ="<tr><td class='".$tdclass."' style='vertical-align:top'>".$u_name."</td>
                                   <td class='".$tdclass."' style='text-align:".$alignit."'><div style='text-align:left' style='width:10%;white-space:nowrap'>";

                            $tmp = explode(",",$u_value);
                            for ($i=0; $i<count($tmp); $i++) {
                            $checked = ($tmp[$i] == $user_pref[$form_ext_name])? " checked" : "";
                            if(!USER){ $checked = ($_POST[$form_ext_name] == $tmp[$i])? " checked" : "";}
                            $ret .="<input  type='radio' name='".$form_ext_name."'  value='".$tmp[$i]."' $checked /> $tmp[$i] ";
                            $ret .= ($pref['signup_ext_req'.$key] && $i==0 && (!USER))? "<span style='font-size:15px; color:red'> *</span>":"";
                            $ret .="<br/>";
                            };
                            $ret .="</div>";
                              $ret .="</td></tr>";
                          break;

                          case "dropdown":
                            $ret ="<tr><td class='".$tdclass."' style='vertical-align:top'>".$u_name."</td>
                                   <td class='".$tdclass."' style='text-align:".$alignit."'>
                                   <select class='tbox' style='width:200px'  name='".$form_ext_name."'><option></option>";

                            $tmp = explode(",",$u_value);
                            for ($i=0; $i<count($tmp); $i++) {
                            $checked = ($tmp[$i] == $user_pref[$form_ext_name])? " selected" : "";
                            if(!USER){ $checked = ($_POST[$form_ext_name]) == ($tmp[$i])? " selected" : "";}
                            $ret .="<option value='$tmp[$i]' $checked />". $tmp[$i] ."</option />\n";
                            };
                            $ret .="</select>";
                            $ret .= ($pref['signup_ext_req'.$key] && (!USER))? "<span style='font-size:15px; color:red'> *</span>":"";
                            $ret .="</td></tr>";

                          break;

                          case "text":
                          if($u_value == ""){$u_value = "40";};
                             $ret ="<tr><td class='".$tdclass."' style='vertical-align:top'>".$u_name."</td>
                            <td class='".$tdclass."' style='text-align:".$alignit."'>";
                              $valuehere = ($_POST[$form_ext_name])? $_POST[$form_ext_name] : $user_pref[$form_ext_name];
                            $ret .="<input class='tbox' type='text' name='".$form_ext_name."' size='".$u_value."' value='".$valuehere."' maxlength='200' />";
                            $ret .= ($pref['signup_ext_req'.$key] && (!USER))? "<span style='font-size:15px; color:red'> *</span>":"";
                            $ret .="</td></tr>";
                            break;

                         case "table":
                            $ret ="<tr><td class='".$tdclass."' style='vertical-align:top'>".$u_name."</td>
                                   <td class='".$tdclass."' style='text-align:".$alignit."'>
                                   <select class='tbox' style='width:200px'  name='".$form_ext_name."'><option></option>";

                            $tmp = explode(",",$u_value);
                            $fieldid = $row[$tmp[1]];
                            $fieldvalue = $row[$tmp[2]];
                            $sql -> db_Select($tmp[0],"*","$tmp[1] !='' ORDER BY $tmp[2]");
                                while($row = $sql-> db_Fetch()){
                                $fieldid = $row[$tmp[1]];
                                $fieldvalue = $row[$tmp[2]];
                            $checked = ($fieldid == $user_pref[$form_ext_name])? " selected" : "";
                            if(!USER){ $checked = ($_POST[$form_ext_name] == $fieldid)? " selected" : "";}
                            $ret .="<option value='".$fieldid."' $checked /> $fieldvalue </option>";
                            }
                            $ret .="</select>";
                            $ret .= ($pref['signup_ext_req'.$key] && e_PAGE =="customsignup.php")? "<span style='font-size:15px; color:red'> *</span>":"";
                            $ret .="</td></tr>";
                          break;

                        default:
                        $ret = "<tr>
                        <td style='width:20%' class='".$tdclass."'>".$form_ext_name."</td>
                        <td style='width:80%; text-align:".$alignit."' class='".$tdclass."' nowrap>";
                        $valuehere = ($_POST[$form_ext_name])? $_POST[$form_ext_name] : $user_pref[$form_ext_name];
                        $ret .="<input class='tbox' type='text' name='".$form_ext_name."' size='40' value='".$valuehere."' maxlength='200' />";
                        $ret .= ($pref['signup_ext_req'.$key])? "<span style='font-size:15px; color:red'> *</span>":"";
                        $ret .= "</td></tr>";
                        break;
                        }

                        return $ret;


}

?>