<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /classes/mail.php
|        updated by Cameron.
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
/*
Please note that mailed attachments have been found to be corrupted using php 4.3.3
php 4.3.6 does NOT have this problem.
*/
// Comment out the line below if you have trouble with some people not receiving emails.
// ini_set(sendmail_path, "/usr/sbin/sendmail -t -f ".$pref['siteadminemail']);


function sendemail($send_to, $subject, $message,$to_name,$send_from,$from_name,$attachments,$Cc,$Bcc,$returnpath,$returnreceipt){
        global $pref;
        $lb = "\n";
        // Clean up the HTML. ==

        if(preg_match('/<(html|font|br|a|img)/i', $message)){
        $Html = $message;
        }else{
        $Html = preg_replace("/\n/","<br />",$message);
        $Html = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)',    '<a href="\\1">\\1</a>', $Html);
        $Html = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)',    '\\1<a href="http://\\2">\\2</a>', $Html);
        $Html = eregi_replace('([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})',    '<a href="mailto:\\1">\\1</a>', $Html);
        }

        $text = strip_tags(preg_replace("<br>","/\n/",$message));
        $OB="----=_OuterBoundary_000". md5(uniqid(mt_rand(), 1));
        $IB="----=_InnerBoundery_001" . md5(uniqid(mt_rand(), 1));

        $send_from = ($send_from)?$send_from:$pref['siteadminemail'];
        $from_name = ($from_name)?$from_name:$pref['siteadmin'];
        $to_name = ($to_name)?$to_name:$send_to;
        $send_to = $to_name." <".$send_to.">\n";

        $headers = "Date: ".date("r")."\n";
        $headers.= "MIME-Version: 1.0\n";
        $headers.= "From: ".$from_name." <".$send_from.">\n";
        $headers.= "Reply-To: ".$from_name." <".$send_from.">\n";
        $headers.= ($returnreceipt !="")? "Return-Receipt: $returnreceipt\n":"Return-Receipt: ".$pref['siteadminemail']."\n";
        $headers.= "X-Sender: ".$send_from."\n";
        $headers.= "X-Mailer: PHP Mailer\n";
        $headers.= "X-MimeOLE: Produced By e107 website system\n";
        $headers.= "X-Priority: 3\n";
        if ($Cc) {$headers .= "Cc: $Cc\n";}
        if ($Bcc) {$headers .= "Bcc: $Bcc\n";}
        $headers.="Content-Type: multipart/mixed;\n\tboundary=\"".$OB."\"\n";

        // Insert Body with text and HTML.
        $body ="This is a multi-part message in MIME format.\n";
        $body.="\n--".$OB."\n";
        $body.="Content-Type:multipart/alternative;\n\tboundary=\"".$IB."\"\n\n";

        //plaintext section
        $body.="\n--".$IB."\n";
        $body.="Content-Type: text/plain;\n\tcharset=".CHARSET."\n";
        $body.="Content-Transfer-Encoding: quoted-printable\n\n";
        // plaintext goes here
        $body.=$text."\n\n";

        // html section
        $body.="\n--".$IB."\n";
        $body.="Content-Type: text/html;\n\tcharset=".CHARSET."\n";
        $body.="Content-Transfer-Encoding: base64\n\n";
        $body.= chunk_split(base64_encode($Html))."\n\n";
        $body.="\n--".$IB."--\n";

// attachments ================
        if($attachments){
        if(!is_array($attachments)){
        $AttmFiles[] = $attachments;
        }else{
        $AttmFiles = $attachments;
        }

         foreach($AttmFiles as $AttmFile){
           if(is_file($AttmFile)){
                $patharray = explode ("/", $AttmFile);
                $mime = is_callable("mime_content_type")? mime_content_type($AttmFile):"application/octetstream";
                $FileName=$patharray[count($patharray)-1];
                $body.= "\n--".$OB."\n";
                $body.="Content-Type: $mime;\n\tname=\"".$FileName."\"\n";
                $body.="Content-Transfer-Encoding: base64\n";
                $body.="Content-length:\"".filesize($AttmFile)."\"\n";
                $body.="Content-Disposition: attachment;\n\tfilename=\"".$FileName."\"\n\n";
                $fd=fopen($AttmFile, "r");
                $FileContent=fread($fd,filesize($AttmFile));
                fclose($fd);
                $FileContent=chunk_split(base64_encode($FileContent));
                $body.=$FileContent; $body.= "\n";
                }
           }
        }
        $body.= "\n--".$OB."--\n";



        if($pref['smtp_enable']){
                require_once(e_HANDLER."smtp.php");
                if(smtpmail($send_to, $subject, $body, $headers)){
                        return TRUE;
                }else{
                        return FALSE;
                }
        }else{
                $headers.= ($returnpath !="")? "Return-Path: <".$returnpath.">\n":"Return-Path: <".$pref['siteadminemail'].">\n";
                if(@mail($send_to, $subject, $body, $headers)){
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
        $result[1]="Cannot find E-Mail server.";
        $result[2] = $From;
        return $result;
    }
  $result[0]=true;
    $result[1]="$Email appears to be valid.";
    $result[2] = $From;
    return $result;
} // end of function


?>