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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/headlines_menu/headlines_menu.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-12-13 13:20:44 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$headline_update = 7200;        // (2 hours)

// is script being called as menu item, or standalone page ...

if(strstr(($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_FILENAME']), "headlines_menu.php")){
        require_once("../../class2.php");
        require_once(HEADERF);

        include(e_PLUGIN."headlines_menu/languages/".(file_exists(e_PLUGIN."headlines_menu/languages/".e_LANGUAGE.".php") ? e_LANGUAGE.".php" : "English.php"));

        if($feeds = $sql -> db_Select("headlines", "*", "headline_active='1' ")){
                $column = ($feeds/2);
                $rss = new parse_xml;
                $text = "<div style='text-align:center'>\n<table style='width:95%'>\n<tr>\n";
                $text .= "<td style='width:50%; vertical-align:top'>";
                while($row = $sql -> db_Fetch()){
                        extract($row);

                        $rss = new parse_xml;
                        $text .= str_replace("e107_themes/", e_THEME, $headline_description);

                        $c++;

                        if($c >= ($column-1) && !$flag){
                                $text .= "</td>\n<td style='width:50%; vertical-align:top'>\n";
                                $flag = TRUE;
                        }
                }

                $gen = new convert; $datestamp = $gen->convert_date(($headline_timestamp ? $headline_timestamp : time()), "long");
                $text .= "</td></tr>
                <tr><td colspan='2' class='forumheader3' style='text-align:center'>".NFMENU_162." ".$datestamp."</td></tr>
                </table></div>";
                $ns -> tablerender(NFMENU_161, stripslashes($text));
                require_once(FOOTERF);
        }

}

$text = "<br />";
if($sql -> db_Select("headlines", "*", "headline_active='1' ")){
        while($row = $sql -> db_Fetch()){
                extract($row);
                if(!$headline_url){ break; }
                if($headline_timestamp+$headline_update < time() && !strstr(THEME, "../")){
                        $tmp = parse_url($headline_url);
                        $dead = FALSE;
                        if(ini_get("allow_url_fopen")){
                                if(!$remote = @fopen ($headline_url, "r")){
                                        $dead = TRUE;
                                        $text .= "<div style='text-align:center'>\n<table style='width:95%' class='forumheader3'>\n<tr>\n<td style='text-align:center' class='forumheader2'>\n<div class='smalltext'><a href='".$headline_url."' rel='external'>[".NFMENU_163." ".$tmp['host']."]</a></div></td></tr></table></div><br />";
                                }
                        } else {
                                if(!$remote = @fsockopen ($tmp['host'], 80 ,$errno, $errstr, 10)){
                                        $dead = TRUE;
                                        $text .= "<div style='text-align:center'>\n<table style='width:95%' class='forumheader3'>\n<tr>\n<td style='text-align:center' class='forumheader2'>\n<div class='smalltext'><a href='".$headline_url."' rel='external'>[".NFMENU_163." ".$tmp['host']."]</a></div></td></tr></table></div><br />";
                                } else {
                                        stream_set_timeout($remote, 10);
                                        fputs($remote, "GET ".$headline_url." HTTP/1.0\r\n\r\n");
                                }
                        }
                        unset($data);
                        if (!$dead) {
                                while(!feof($remote)){
                                        $data .= fgets ($remote);
                                }
                                fclose ($remote);
                                $data = eregi_replace("^.*\<\?xml", "<?xml", $data);
                                if(strstr($data, "Your Headline Reader Has Been Banned")){
                                        $tmp = parse_url($headline_url);
                                        $text .= "<div style='text-align:center'>\n<table style='width:95%' class='forumheader3'>\n<tr>\n<td style='text-align:center' class='forumheader2'>\n<div class='smalltext'><a href='".$headline_url."' rel='external'>[".NFMENU_163." ".$tmp['host']."]</a></div></td></tr></table></div><br />";
                                }
                                $rss = new parse_xml;
                                $rss -> parse_xml_($data);
                                $text .= $rss -> cache_results($headline_id);
                        }
                } else {
                        $text .= str_replace(ereg_replace("\.\.\/", "", THEME), THEME, $headline_data);
                }
        }

        $gen = new convert; $datestamp = $gen->convert_date(($headline_timestamp ? $headline_timestamp : time()), "short");
        $text .= "<div class='smalltext' style='text-align:right'>".NFMENU_162.": ".$datestamp."</div><a href='".e_PLUGIN."headlines_menu/headlines_menu.php'>".NFMENU_166."</a>";
        $ns -> tablerender(NFMENU_161, stripslashes($text), 'headlines_menu');
}

class parse_xml {

        var $parser;
        var $current_tag;
        var $channel = array();
        var $rssdata = array();
        var $channel_flag = FALSE;
        var $counter1 = 0;
        var $counter2 = 0;
        var $counter3 = 0;

        function parse_xml_ ( $rss_source ){


                if(!function_exists('xml_parser_create')){
                        return "ERROR";
                }

                $this->parser = xml_parser_create('');
                xml_set_object($this->parser, $this);
                xml_set_element_handler($this->parser, 'startElement', 'endElement');
                xml_set_character_data_handler( $this->parser, 'characterData' );
                xml_parse( $this->parser, $rss_source );
                xml_parser_free( $this->parser );

        }

        function startElement ($p, $element, &$attrs) {
                $this->current_tag = strtolower($element);
                if($this->current_tag == "item" || $this->current_tag == "images"){
                        $this->channel_flag = TRUE;
                }
        }

        function endElement ($p, $element) {
                $this->current_tag = strtolower($this->current_tag);
                if($this->current_tag == "description" && $this->channel_flag){
                        $this->counter1 ++;
                }else if($this->current_tag == "title" && $this->channel_flag){
                        $this->counter2 ++;
                }else if($this->current_tag == "link" && $this->channel_flag){
                        $this->counter3 ++;
                }
                $this->current_tag = "";

        }

        function characterData ($p, $data) {
                $this->current_tag = strtolower($this->current_tag);
                if(trim(chop($data))){
                        if(!$this->channel_flag){
                                if(!$this->channel[$this->current_tag]){
                                        $this->channel[$this->current_tag] = $data;
                                }
                        }else{
                                if($this->current_tag == "description"){
                                        $this->rssdata[$this->current_tag][$this->counter1] .= strip_tags($data, "<br><br />");
                                }else if($this->current_tag == "title"){
                                        $this->rssdata[$this->current_tag][$this->counter2] .= strip_tags($data, "<br><br />");
                                }else if($this->current_tag == "link"){
                                        $this->rssdata[$this->current_tag][$this->counter3] .= strip_tags($data, "<br><br />");
                                }
                        }
                }
        }


        function cache_results($headline_id, $description = FALSE){
                $sql = new db;

                $sql -> db_Select("headlines", "headline_image ", "headline_id=$headline_id");
                $row = $sql -> db_Fetch(); extract($row);

                $logoimage = ($headline_image ? $headline_image : $this->channel['url']);

                $text = "<div style='text-align:center'>\n<table style='width:95%' class='forumheader3'>\n<tr>\n<td style='text-align:center' class='forumheader2'>\n<a href='".$this->channel['link']."' rel='external'>
                ".($this->channel['url'] || $logoimage ? "<img src='$logoimage' alt='' style='border:0; vertical-align:center' />" : $this->channel['title'])."</a></td></tr>";

                $text2 = $text;

                for($a=0; $a<=9; $a++){
                        if($this->rssdata['link'][$a]){
                                $text2 .= "<tr><td><img src='".THEME."images/bullet2.gif' alt='' style='vertical-align:middle' /> <b><a href='".$this->rssdata['link'][$a]."' rel='external'>".$this->rssdata['title'][$a]."</a></b><br />".($this->rssdata['description'][$a] && !strstr($this->rssdata['description'][$a], "Search") ? "<span class='smalltext'>" .wordwrap(substr($this->rssdata['description'][$a], 0, 300), 30, "\n", 1)." ...</span>" : "")."</td></tr>";

                                $text .= "<tr><td  class='smalltext'><img src='".THEME."images/bullet2.gif' alt='' style='vertical-align:middle' /> <a href='".$this->rssdata['link'][$a]."' rel='external'>".$this->rssdata['title'][$a]."</a></td></tr>";
                        }
                }
                $text .= "</table></div><br />\n";
                $text2 .= "</table></div><br />\n";
                if(!$description){
                        $sql -> db_Update ("headlines", "headline_data='".addslashes($text)."', headline_timestamp='".time()."', headline_description='".addslashes($text2)."' WHERE headline_id='$headline_id' ");

                        return $text;
                }else{
                        return $text2;
                }
        }

}        // end class //

?>
