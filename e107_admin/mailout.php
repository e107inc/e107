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
|     $Source: /cvs_backup/e107_0.7/e107_admin/mailout.php,v $
|     $Revision: 1.4 $
|     $Date: 2004-12-26 21:11:40 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

    require_once("../class2.php");
    require_once(e_ADMIN."auth.php");
    if(!getperms("4")){ header("location:".e_BASE."index.php"); exit;}
    require_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_users.php");

    require_once(e_HANDLER."userclass_class.php");
    if($pref['htmlarea']){
    require_once(e_HANDLER."htmlarea/htmlarea.inc.php");
    htmlarea('email_body');
    }

if(IsSet($_POST['submit'])){

    if($_POST['email_to'] == "all"){   // send to all.
       $sql -> db_Select("user", "user_name, user_email, user_class", "ORDER BY user_name", "no-where");
       $c=0;
           while($row = $sql -> db_Fetch()){
               extract($row);
               $recipient_name[$c] = $user_name;
               $recipient[$c]= $user_email;
               $c++;
           }
    }else{       // send to a user-class.

        $sql -> db_Select("user", "user_name, user_email, user_class", "ORDER BY user_name", "no-where");
        $c=0;
        while($row = $sql -> db_Fetch()){
            extract($row);
                if(check_class($_POST['email_to'], $user_class)){
                    $recipient_name[$c] = $user_name;
                    $recipient[$c]= $user_email;
                    $c++;
                }

        }  // end while

    }

    // ===== phpmailer version.

    require(e_HANDLER."phpmailer/class.phpmailer.php");

    $mail = new PHPMailer();

    $mail->From     = ($_POST['email_from_email'])? $_POST['email_from_email']:$pref['siteadminemail'];
    $mail->FromName = ($_POST['email_from_name'])? $_POST['email_from_name']:$pref['siteadmin'];
  //  $mail->Host     = "smtp1.site.com;smtp2.site.com";
    $mail->Mailer   = "mail";
    $mail->AddCC = ($_POST['email_cc']);
    $mail->WordWrap = 50;
    $mail->Charset  = CHARSET;
    $mail->Subject = $_POST['email_subject'];
    $mail->IsHTML(true);
    $attach = chop($_POST['email_attachment']);

    $root = (preg_match("#^/#",$DOWNLOADS_DIRECTORY) || preg_match("#.:#",$DOWNLOADS_DIRECTORY))? "" : e_BASE;

    if(!$mail->AddAttachment($root.$DOWNLOADS_DIRECTORY."/".$_POST['email_attachment'],$attach) && $attach !=""){
    $mss = "There is a problem with the attachment";
    $ns -> tablerender("Error", $mss);
    require_once(e_ADMIN."footer.php");
    exit;
    }
    // ============================  Render Results and Mailit =========

    $text = "<div style='overflow:auto;height:300px'>";
    $text .= "<table class='fborder' style='width:100%'>";
    $text .= "<tr><td class='fcaption'>Username</td><td class='fcaption'>Email</td><td class='fcaption'>Status</td></tr>";
    $message_subject = stripslashes($_POST['email_subject']);
    $message_body = stripslashes($_POST['email_body']);
    $message_body = eregi_replace('src="','src="'.SITEURL,$message_body);


    $sent_no = 0;

    for ($i=0; $i<count($recipient); $i++) {

    // --- start loop ----

    $text .="<tr>";
    $text .="<td class='forumheader3' style='width:40%'>".$recipient_name[$i]."</td>";
    $text .="<td class='forumheader3' style='width:40%'>".$recipient[$i]."</td>";

    $mes_body = str_replace("{USERNAME}",$recipient_name[$i],$message_body);

    $mail->Body    = str_replace("\n","<br>",$mes_body);
    $mail->AltBody = strip_tags(str_replace("<br>","\n",$mes_body));
    $mail->AddAddress($recipient[$i], $recipient_name[$i]);



       if($mail->Send()){
       $stat = "<span style='color:green'>Sent</span>";
       $sent_no ++;
       }else{
       $stat = "<span style='color:red'>Error</span>";
       }
       $text .="<td class='forumheader3'>&nbsp;&nbsp; $stat </td></tr>";



    $mail->ClearAddresses();



     // ---- end loop. ---

    };

     $mail->ClearAttachments();


    $text .="</table></div>";
    $rec_text = $c > 1 ? "recipients":"recipient";

    if($c == 0 ){ $text = "<div style='text-align:center'>No Recipients Found</div>"; }

    $ns -> tablerender("Emailing $c $rec_text", $text);
    require_once(e_ADMIN."footer.php");
    exit;
}





// Display Form.



   $text = "";
   $text .= ($pref['smtp_enable']==0)? "<div style='text-align:center'>It is recommended that you enable <a href='prefs.php'>SMTP</a> for sending large numbers of emails.<br /><br /></div>":"";
   $text .= "<div style='text-align:center'>
   <form method='post' action='".e_SELF."' id='linkform'>
   <table style='width:96%' class='fborder'>
   <tr>
   <td style='width:30%' class='forumheader3'>From Name: </td>
   <td style='width:70%' class='forumheader3'>
   <input type='text' name='email_from_name' class='tbox' style='width:80%' value='$email_from_name' />
   </td>
   </tr>

   <tr>
   <td style='width:30%' class='forumheader3'>From Email: </td>
   <td style='width:70%' class='forumheader3'>
   <input type='text' name='email_from_email' class='tbox' style='width:80%' value='$email_from_email' />
   </td>
   </tr>

   <tr>
   <td style='width:30%' class='forumheader3'>To: </td>
   <td style='width:70%' class='forumheader3'>
   ".userclasses("email_to")."$email_to</td>
   </tr>";

   $text .="

   <tr>
   <td style='width:30%' class='forumheader3'>Cc: </td>
   <td style='width:70%' class='forumheader3'>
   <input type='text' name='email_cc' class='tbox' style='width:80%' value='' />
   $email_cc
   </td>
   </tr>


   <tr>
   <td style='width:30%' class='forumheader3'>Bcc: </td>
   <td style='width:70%' class='forumheader3'>
   <input type='text' name='email_bcc' class='tbox' style='width:80%' value='' />
   $email_bcc
   </td>
   </tr>

   <tr>
   <td style='width:30%' class='forumheader3'>Subject: </td>
   <td style='width:70%' class='forumheader3'>
   <input type='text' name='email_subject' class='tbox' style='width:80%' value='' />
   $email_subject
   </td>
   </tr>";


   // Attachment.

   $text .= "<tr>
   <td style='width:30%' class='forumheader3'>Attachment: </td>
   <td style='width:70%' class='forumheader3'>";
   $text .= "<select class='tbox' name='email_attachment' >
       <option></option>";
        $sql -> db_Select("download", "download_url,download_name", "download_id !='' ORDER BY download_name");
        while($row = $sql-> db_Fetch()){
        extract($row);
        $selected = ($_POST['email_attachment'] == $download_url) ?  "selected='selected'" : "";
       $text .="<option value=\"$download_url \" $selected>$download_name</option>";
       }
   $text .= " </select>";

   $text .="</td>
   </tr>";


   $text .="   <tr>
   <td colspan='2' style='width:30%' class='forumheader3'>

   <textarea rows='10' cols='20' id='email_body' name='email_body'  class='tbox' style='border:1px solid black;width:100%;height:200px'>
   $email_body
   </textarea>
   </td>
   </tr>

   ";


   $text .="<tr style='vertical-align:top'>
   <td colspan='2' style='text-align:center' class='forumheader'>";
   $text .= "<input class='button' type='submit' name='submit' value='Send Email' />";
   $text .= "</td>
   </tr>
   </table>
   </form>
   </div>";

   $caption = "Mail-Out";
   $ns -> tablerender($caption, $text);


require_once(e_ADMIN."footer.php");

function userclasses($name){
    global $sql;
   $text .= "<select style='width:80%' class='tbox' name='$name' >
       <option value='all'>All Members</option>";
       $sql -> db_Select("userclass_classes");
       while($row = $sql-> db_Fetch()){
           extract($row);

       $text .="<option value=\"$userclass_id\" $selected>Userclass - $userclass_name</option>";
       }
   $text .= " </select>";

   return $text;
}


  function show_options($action){
                // ##### Display options ---------------------------------------------------------------------------------------------------------
                                if($action==""){$action="main";}
                                // ##### Display options ---------------------------------------------------------------------------------------------------------
                                $var['main']['text']=USRLAN_71;
                                $var['main']['link']= "users.php";

                                $var['create']['text']=USRLAN_72;
                                $var['create']['link']="users.php?create";

                                $var['prune']['text']=USRLAN_73;
                                $var['prune']['link']="users.php?prune";

                                $var['extended']['text']=USRLAN_74;
                                $var['extended']['link']="users.php?extended";

                                $var['options']['text']=USRLAN_75;
                                $var['options']['link']="users.php?options";

                                $var['mailing']['text']= USRLAN_121;
                                $var['mailing']['link']="mailout.php";
                                show_admin_menu(USRLAN_76,$action,$var);
                   }

function mailout_adminmenu(){
        global $user;
        global $action;
        $action = "mailing";
        show_options($action);
}


?>