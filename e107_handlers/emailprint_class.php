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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/emailprint_class.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-07 10:51:56 $
|     $Author: pholzmann $
+----------------------------------------------------------------------------+
*/

class emailprint{
        function render_emailprint($mode, $id, $look=0){
                // $look = 0  --->  display all icons
                // $look = 1  --->  display email icon only
                // $look = 2  --->  display print icon only
                $text_emailprint="";
                switch($mode){
                        case "article":
                                $email = "article";
                                $print = "article";
                        break;
                        case "news":
                                $email = "news";
                                $print = "news";
                        break;
                        case "content":
                                $email = "content";
                                $print = "content";
                        break;
                }
                if($look==0 || $look==1){
                        if(defined("ICONMAIL")&&file_exists(THEME."images/".ICONMAIL)){
                                $ico_mail = THEME."images/".ICONMAIL;
                        }else{
                                $ico_mail = e_IMAGE."generic/friend.gif";
                        }
                        $text_emailprint .= "<a href='email.php?".$email.".".$id."'><img src='".$ico_mail."' style='border:0' alt='email to someone' title='email to someone' /></a> ";
                }
                if($look==0 || $look==2){
                        if(defined("ICONPRINT")&&file_exists(THEME."images/".ICONPRINT)){
                                $ico_print = THEME."images/".ICONPRINT;
                        }else{
                                $ico_print = e_IMAGE."generic/printer.gif";
                        }
                        $text_emailprint .= "<a href='print.php?".$print.".".$id."'><img src='".$ico_print."' style='border:0' alt='printer friendly' title='printer friendly'/></a>";
                }
                return $text_emailprint;
        }
}

?>