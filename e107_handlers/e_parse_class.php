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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/e_parse_class.php,v $
|     $Revision: 1.6 $
|     $Date: 2005-01-23 21:35:34 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

class e_parse {
        var $e_sc;
        var $e_bb;
        var $e_pf;
        var $e_emote;

        function toDB($text,$no_encode = FALSE) {
                if(MAGIC_QUOTES_GPC == TRUE) {
                        $text = stripslashes($text);
                }
                if (ADMIN || $no_encode) {
                        $search = array('$','"',"'",'\\');
                        $replace = array('&#036;','&quot;','&#039;','&#092;');
                        $text = str_replace($search,$replace,$text);
                } else {
                        $text = htmlentities($text,ENT_QUOTES,CHARSET);
                }
                return $text;
        }

        function toForm($text,$single_quotes = FALSE) {
                $mode = ($single_quotes) ? ENT_QUOTES : ENT_COMPAT;
                if(MAGIC_QUOTES_GPC == TRUE) {
                        $text = stripslashes($text);
                }
                $search = array('&#036;','&quot;');
                $replace = array('$','"');
                $text = str_replace($search,$replace,$text);
                return html_entity_decode($text,$mode,CHARSET);
        }

        function post_toHTML($text) {
                if (ADMIN === TRUE || $no_encode === TRUE) {
                        $search = array('$','"',"'",'\\');
                        $replace = array('&#036;','&quot;','&#039;','&#092;');
                        $text = str_replace($search,$replace,$text);
                } else {
                        $text = htmlentities($text,ENT_QUOTES,CHARSET);
                }
                return $this->toHTML($text,TRUE);
        }

        function post_toForm($text) {
                if (MAGIC_QUOTES_GPC == TRUE) {
                        return stripslashes($text);
                }
                return $text;
        }

        function parseTemplate($text, $parseSCFiles=TRUE, $extraCodes="") {
                // Start parse {XXX} codes
                if(!is_object($this->e_sc)) {
                        require_once(e_HANDLER."shortcode_handler.php");
                        $this->e_sc = new e_shortcode;
                }
                return $this -> e_sc -> parseCodes($text,$parseSCFiles,$extraCodes);
                // End parse {XXX} codes
        }

        function toHTML($text, $parseBB=FALSE, $modifiers="",$postID="") {
                if($text==''){return $text;}
                global $pref;
                if(MAGIC_QUOTES_GPC == TRUE) {
                        $text = stripslashes($text);
                }

                $search = array('&#039;','&#036;','&quot;', 'onerror');
                $replace = array("'",'$','"', 'one<i></i>rror');
                $text = str_replace($search,$replace,$text);
                if(strpos($modifiers,'nobreak') == FALSE) {
                        $text = preg_replace("#[\r]*\n[\r]*#","[E_NL]",$text);
                }

                if($pref['smiley_activate']) {
                        if(!is_object($this -> e_emote)) {
                                require_once(e_HANDLER.'emote_filter.php');
                                $this->e_emote = new e_emoteFilter;
                        }
                        $text = $this->e_emote -> filterEmotes($text);
                }

                // Start parse [bb][/bb] codes
                if($parseBB === TRUE) {
                        if(!is_object($this -> e_bb)) {
                                require_once(e_HANDLER.'bbcode_handler.php');
                                $this->e_bb = new e_bbcode;
                        }
                        $text = $this->e_bb -> parseBBCodes($text,$postID);
                }
                // End parse [bb][/bb] codes

                if($pref['profanity_filter']) {
                        if(!is_object($this -> e_pf)) {
                                require_once(e_HANDLER."profanity_filter.php");
                                $this->e_pf = new e_profanityFilter;
                        }
                        $text = $this->e_pf -> filterProfanities($text);
                }

                $nl_replace = (strpos($modifiers,'nobreak') === FALSE) ? "<br />" : "";
                $text = str_replace('[E_NL]',$nl_replace,$text);
                return $text;
        }


        function toJS($stringarray){
            $trans_tbl = get_html_translation_table (HTML_ENTITIES);
            $trans_tbl = array_flip ($trans_tbl);
            return strtr ($stringarray, $trans_tbl);
        }



}
?>