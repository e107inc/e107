<?php
/*  User-Mailout for the e107 core release. .
 This code is experimental
 Still to be added:
  - pulldown menu with a list of files in the download area.
  - the {USERNAME} stuff.
  - bulk mailing with BCC option.. ?

*/

require_once("../class2.php");
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."userclass_class.php");
    if($pref['htmlarea']){
    require_once(e_HANDLER."htmlarea/htmlarea.inc.php");
    htmlarea('email_body');
    }

$send_amount = 5; // amount of people in BCC per email.


if(IsSet($_POST['submit'])){

     if($_POST['email_to'] != "all"){
        $_POST['email_to'] .= ".";
        $query = "user_class REGEXP('".$_POST['email_to']."')";
        }else{$query = "";}

        $sql -> db_Select("user", "*", "$query");
        while($row = $sql-> db_Fetch()){
        $recipient[]  = $row['user_email'];
        $recipient_name[] = $row['user_name'];
        // put into an array so sorting by provider can be achieved if needed later.
        }

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

    $message_body = eregi_replace('src="','src="'.SITEURL,$_POST['email_body']);
    $message_body .="<br>";
    echo $message_body;
    for ($i=0; $i<count($recipient); $i++) {

        $text .= $recipient[$i];
        if(sendemail($recipient[$i],$_POST['email_subject'],$message_body,$recipient_name[$i],$_POST['email_from_email'],$_POST['email_from_name'],$attach,$_POST['email_cc'],$_POST['email_bcc'],"","")){
        $stat = "<span style='color:green'>Sent</span>";
        }else{
        $stat = "<span style='color:red'>Error</span>";
        }
        $text .=" - $stat<br />";
    };
        $text .="</div>";

$ns -> tablerender("Email Sent", $text);
require_once(e_ADMIN."footer.php");
exit;
}



   $text = "<div style='text-align:center'>
   <form method='post' action='".e_SELF."' name='linkform'>
   <table style='width:96%' class='fborder'>
   <tr>
   <td style='width:30%' class='forumheader3'>From Name: </td>
   <td style='width:70%' class='forumheader3'>
   <input type='text' id='email_from' name='email_from_name' class='tbox' style='width:80%' value='$email_from_name' />
   </td>
   </tr>

   <tr>
   <td style='width:30%' class='forumheader3'>From Email: </td>
   <td style='width:70%' class='forumheader3'>
   <input type='text' id='email_from' name='email_from_email' class='tbox' style='width:80%' value='$email_from_email' />
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

   <textarea id='email_body' name='email_body'  class='tbox' style='border:1px solid black;width:100%;height:200px'>
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



?>