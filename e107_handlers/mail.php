<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /classes/mail.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

function sendemail($send_to, $subject, $message,$format="plain"){
        global $pref;
        $send_to = "<".$send_to.">";
        $headers = "From: \"".$pref['siteadmin']."\" <".$pref['siteadminemail']."> \n";
        $headers .= "Reply-To: ".$pref['siteadmin']." <".$pref['siteadminemail'].">\n";
        $headers .= "X-Sender: ".$pref['siteadminemail']."\n";
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



        if($pref['smtp_enable']){
                require_once(e_HANDLER."smtp.php");
                if(smtpmail($send_to, $subject, $message, $headers)){
                        return TRUE;
                }else{
                        return FALSE;
                }
        }else{
                $headers .= "Return-Path: <".$pref['siteadminemail'].">\n";
                if(@mail($send_to, $subject, $message, $headers)){
                        return TRUE;
                }else{
                        return FALSE;
                }
        }

}


function validatemail($Email) {
    global $HTTP_HOST;
    $result = array(); ;

    if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $Email)) {
        $result[0]=false;
        $result[1]="$Email is not properly formatted";
        return $result;
    }

    list ( $Username, $Domain ) = split ("@",$Email);

    if (getmxrr($Domain, $MXHost)){
        $ConnectAddress = $MXHost[0];
    } else {
        $ConnectAddress = $Domain;
    }

    $Connect = fsockopen ( $ConnectAddress, 25 );

    if ($Connect){

        if (ereg("^220", $Out = fgets($Connect, 1024))) {

           fputs ($Connect, "HELO $HTTP_HOST\r\n");
           $Out = fgets ( $Connect, 1024 );
           fputs ($Connect, "MAIL FROM: <{$Email}>\r\n");
           $From = fgets ( $Connect, 1024 );
           fputs ($Connect, "RCPT TO: <{$Email}>\r\n");
           $To = fgets ($Connect, 1024);
           fputs ($Connect, "QUIT\r\n");
           fclose($Connect);
            if (!ereg ("^250", $From) ||
            !ereg ( "^250", $To )) {
               $result[0]=false;
               $result[1]="Server rejected address";
               $result[2] = $From;
               return $result;
            }
            } else {
            $result[0] = false;
            $result[1] = "No response from server";
            $result[2] = $From;
            return $result;
          }

    }  else {

        $result[0]=false;
        $result[1]="Can not connect E-Mail server.";
        $result[2] = $From;
        return $result;
    }
  $result[0]=true;
    $result[1]="$Email appears to be valid.";
    $result[2] = $From;
    return $result;
} // end of function


?>