<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /classes/mail.php
|        updated by the Dev Team (Cameron, Lolo Irie)
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


if(file_exists(e_LANGUAGEDIR.e_LANGUAGE."/lan_mail_handler.php")){@include(e_LANGUAGEDIR.e_LANGUAGE."/lan_mail_handler.php");}
else{@include(e_LANGUAGEDIR."English/lan_mail_handler.php");}

function sendemail($e107_send_to, $e107_subject, $e107_message, $e107_to_name, $e107_send_from="", $e107_from_name="", $e107_attachments="", $e107_Cc="", $e107_Bcc="", $e107_returnpath="", $e107_returnreceipt="", $e107_mode=3){
        // $e107_mode -> 	0 to use short version of email header + send_to format : email
		//					1 to use short version of email header + send_to format : name <email>
		//					2 to use  long version of email header + send_to format : email
		//					3 to use  long version of email header + send_to format : name <email>
		
		
		// NO anonymous email, subject required, message required
		if(!isset($e107_send_to)||!isset($e107_subject)||!isset($e107_message)){
			return false;
		}
		
		global $pref;
        //$lb = "\n";
        // Clean up the HTML. ==

        if(preg_match('/<(html|font|br|a|img)/i', $e107_message)){
        $Html = $e107_message;
        }else{
        $Html = preg_replace("/\n/","<br />",$e107_message);
        $Html = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)',    '<a href="\\1">\\1</a>', $Html);
        $Html = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)',    '\\1<a href="http://\\2">\\2</a>', $Html);
        $Html = eregi_replace('([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})',    '<a href="mailto:\\1">\\1</a>', $Html);
        }

        $text = strip_tags(preg_replace("<br>","/\n/",$e107_message));
        $OB="----=_OuterBoundary_000". md5(uniqid(mt_rand(), 1));
        $IB="----=_InnerBoundery_001" . md5(uniqid(mt_rand(), 1));

        $e107_send_from = ($e107_send_from!="" ? $e107_send_from : $pref['siteadminemail'] );
        $e107_from_name = ($e107_from_name!="" ? $e107_from_name : $pref['siteadmin'] );
        $e107_to_name = ($e107_to_name!="" ? $e107_to_name : $e107_send_to );
		if($e107_mode==1 || $e107_mode==3){
			$e107_send_to = $e107_to_name." <".$e107_send_to.">\n";
		}
		
        
		
		//$e107_send_to = $e107_to_name." <".$e107_send_to.">\n";

        if($e107_mode>1){
			$headers = "Date: ".date("r")."\r\n";
	        $headers.= "MIME-Version: 1.0\r\n";
	        if($e107_from_name!=""){
				$headers.= "From: ".$e107_from_name.( $e107_send_from != "" ? " <".$e107_send_from.">"  : "" )."\r\n";
	    	    $headers.= "Reply-To: ".$e107_from_name.( $e107_send_from != "" ? " <".$e107_send_from.">"  : "" )."\r\n";
			}
	        $headers.= ($e107_returnreceipt !=""? "Return-Receipt: ".$e107_returnreceipt."\n" : "Return-Receipt: ".$pref['siteadminemail']."\r\n" );
	        $headers.= "X-Sender: ".$e107_send_from."\r\n";
	        $headers.= "X-Mailer: PHP Mailer\r\n";
	        $headers.= "X-MimeOLE: ".LANMAILH_1."\r\n";
	        $headers.= "X-Priority: 3\r\n";
	        if ($e107_Cc!="") {$headers .= "Cc: ".$e107_Cc."\r\n";}
	        if ($e107_Bcc!="") {$headers .= "Bcc: ".$e107_Bcc."\r\n";}
	        $headers.="Content-Type: multipart/mixed;\n\tboundary=\"".$OB."\"\r\n";
		}
		else{
			$headers= "From: ".$e107_from_name."\r\n";
	    	$headers.= "Reply-To: ".$e107_from_name."\r\n";
		}

        // Insert Body with text and HTML.
        $body =LANMAILH_2."\n";
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
        if($e107_attachments!=""){
        if(!is_array($e107_attachments)){
        $AttmFiles[] = $e107_attachments;
        }else{
        $AttmFiles = $e107_attachments;
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
                if(smtpmail($e107_send_to, $e107_subject, $body, $headers)){
                        return TRUE;
                }else{
                        return FALSE;
                }
        }else{
                $headers.= ($e107_returnpath !="")? "Return-Path: <".$e107_returnpath.">\n":"Return-Path: <".$pref['siteadminemail'].">\n";
                if(@mail($e107_send_to, $e107_subject, $body, $headers)){
                        return TRUE;
                }else{
                        return FALSE;
                }
        }

}


function validatemail($Email) {
    global $HTTP_HOST;
    $result = array(); ;
	
	// Email too short, too long or wrong formatted
	if (strlen($Email) < 6 || strlen($Email) > 255 || !eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $Email)){
        $result[0]=false;
        $result[1]=$Email.LANMAILH_3;
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
               $result[1]= LANMAILH_4;
               $result[2] = $From;
               return $result;
            }
            } else {
            $result[0] = false;
            $result[1] = LANMAILH_5;
            $result[2] = $From;
            return $result;
          }

    }  else {

        $result[0]=false;
        $result[1]= LANMAILH_6;
        $result[2] = $From;
        return $result;
    }
  $result[0]=true;
    $result[1]=$Email.LANMAILH_7;
    $result[2] = $From;
    return $result;
} // end of function


?>