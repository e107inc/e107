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
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:20 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
/*  User-Mailout for the e107 core release. .
 This code is experimental
 Still to be added:
  - pulldown menu with a list of files in the download area.
  - the {USERNAME} stuff.
  - bulk mailing with BCC option.. ?
  - confirm number of recipients, before sending.

*/

require_once("../class2.php");
require_once(e_ADMIN."auth.php");
require_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_users.php");

require_once(e_HANDLER."userclass_class.php");
    if($pref['htmlarea']){
    require_once(e_HANDLER."htmlarea/htmlarea.inc.php");
    htmlarea('email_body');
    }

$send_amount = 5; // amount of people in BCC per email.








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
// ===================

   /* $text = "<div style='text-align:center'>
   <form method='post' action='".e_SELF."' id='confirmform'>
   You have chosen to send an email to <b>$c</b> users<br />
   Are you sure you want to continue ?<br />
   <input type='submit' class='button' value='submit' name='Yes'>
   <input type='hidden' name='";*/


      /*
            $chunk=0;
            for ($j=0; $i<count($recipients); $j++) {
            $bcc_bulk = "";
            for ($i=($chunk); $i<($chunk+$amount); $i++) {
            $bcc_bulk .= $recipients[$i];
            };
            $j = $j +amount;
            $chunk = $chunk+$amount;
      */
    require_once(e_HANDLER."mail.php");
    $text = "<div style='overflow:auto;height:300px'>";
    $text .= "<table class='fborder' style='width:100%'>";
    $text .= "<tr><td class='fcaption'>Username</td><td class='fcaption'>Email</td><td class='fcaption'>Status</td></tr>";
    $message_body = eregi_replace('src="','src="'.SITEURL,$_POST['email_body']);
    $message_body .="<br />";
    $
  //  echo $message_body;
     $sent_no = 0;
    for ($i=0; $i<count($recipient); $i++) {
    $text .="<tr>";
    $text .="<td class='forumheader3' style='width:40%'>".$recipient_name[$i]."</td>";
    $text .="<td class='forumheader3' style='width:40%'>".$recipient[$i]."</td>";
        if(sendemail($recipient[$i],$_POST['email_subject'],$message_body,$recipient_name[$i],$_POST['email_from_email'],$_POST['email_from_name'],$attach,$_POST['email_cc'],$_POST['email_bcc'],"","")){
        $stat = "<span style='color:green'>Sent</span>";
        $sent_no ++;
        }else{
        $stat = "<span style='color:red'>Error</span>";
        }
        $text .="<td class='forumheader3'>&nbsp;&nbsp; $stat </td></tr>";
    };
        $text .="</table></div>";

    $rec_text = $c > 1 ? "recipients":"recipient";

    if($c == 0 ){ $text = "<div style='text-align:center'>No Recipients Found</div>"; }

    $ns -> tablerender("Emailing $c $rec_text", $text);
    require_once(e_ADMIN."footer.php");
exit;
}

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
   </tr>

   <tr>
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


//=============================
/*
function bulk_email($from,$group, $cc, $bcc, $subject, $message, $format="plain", $amount=2){

 global $pref,$sql;




       return "To: ".$send_to."<br>Subject: ".$subject."<br>Message: ".$message."<br>Headers:<br>".str_replace("\n","<br>",$headers);


       // ==========================================================

            $bcc_bulk = substr($bcc_bulk,0,-1);
           /*
            $headers = "From: \"$from\" <$from> \n";
            $headers .= ($cc)? "cc: ".$cc."\n":"";
            $headers .= "bcc: ".$bcc_bulk."\n";
            $headers .= "Reply-To: ".$from." <".$from.">\n";
            $headers .= "X-Sender: ".$from."\n";
            $headers .= "X-Mailer: Microsoft Outlook Express 6.00.2720.3000\n";
            $headers .= "X-MimeOLE: Produced By e107 website system\n";
            $headers .= "X-Priority: 3\n";
            $headers .= "Content-transfer-encoding: 8bit\nDate: " . date('r', time()) . "\n";
            $headers .= "MIME-Version: 1.0\n";
            if($format == "html"){
            $headers .= "Content-Type: text/html; charset=".CHARSET."\n";
            }else{
            $headers .= "Content-Type: text/plain; charset=".CHARSET."\n";
            }
            $send_to = $from; // send to self.


            if($pref['smtp_enable']){
                    require_once(e_HANDLER."smtp.php");
                    if(smtpmail($send_to, $subject, $message, $headers)){
                            return TRUE;
                    }else{
                            return FALSE;
                    }
            }else{
                    $headers .= "Return-Path: <".$from.">\n";
                    if(@mail($send_to, $subject, $message, $headers)){
                      //      return TRUE;
                            $batch[] = "Batch# $j - Successful";
                    }else{
                     //       return FALSE;
                            $batch[] = "Batch# $j - Failed";
                    }
            }
          sleep(2);
     }
 return $bcc_bulk ;

}
*/
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