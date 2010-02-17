<?php
/*
+---------------------------------------------------------------+
|     e107 website system
|     /sitelinks_class.php
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/sitelinks_class.php,v $
|     $Revision$
|     $Date$
|     $Author$
+---------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_sitelinks.php");

/**
* @return void
* @desc Outputs sitelinks
*/

class sitelinks
{

    var $eLinkList;

    function getlinks($cat=1)
    {

        global $sql;
        if ($sql->db_Select('links', '*', "link_category = ".intval($cat)." and link_class IN (".USERCLASS_LIST.") ORDER BY link_order ASC")){
            while ($row = $sql->db_Fetch())
            {
            //  if (substr($row['link_name'], 0, 8) == 'submenu.'){
            //      $tmp=explode('.', $row['link_name'], 3);
             //     $this->eLinkList[$tmp[1]][]=$row;
                if (isset($row['link_parent']) && $row['link_parent'] != 0){
                    $this->eLinkList['sub_'.$row['link_parent']][]=$row;
                }else{
                    $this->eLinkList['head_menu'][] = $row;
                }
            }
        }

    }

    function get($cat=1, $style='', $css_class = false)
    {
        global $pref, $ns, $e107cache, $linkstyle;
        $usecache = ((trim(defset('LINKSTART_HILITE')) != "" || trim(defset('LINKCLASS_HILITE')) != "") ? false : true);

        if ($usecache && !strpos(e_SELF, e_ADMIN) && ($data = $e107cache->retrieve('sitelinks_'.$cat.md5($linkstyle.e_PAGE.e_QUERY)))) 
		{
          return $data;
        }

        if (LINKDISPLAY == 4) {
            require_once(e_PLUGIN.'ypslide_menu/ypslide_menu.php');
            return;
        }

        $this->getlinks($cat);

        // are these defines used at all ?

        if(!defined('PRELINKTITLE')){
            define('PRELINKTITLE', '');
        }
        if(!defined('PRELINKTITLE')){
            define('POSTLINKTITLE', '');
        }
        // -----------------------------

        // where did link alignment go?
        if (!defined('LINKALIGN')) { define(LINKALIGN, ''); }

        if(!$style){
            $style['prelink'] = defined('PRELINK') ? PRELINK : '';
            $style['postlink'] = defined('POSTLINK') ? POSTLINK : '';
            $style['linkclass'] = defined('LINKCLASS') ? LINKCLASS : "";
            $style['linkclass_hilite'] = defined('LINKCLASS_HILITE') ? LINKCLASS_HILITE : "";
            $style['linkstart_hilite'] = defined('LINKSTART_HILITE') ? LINKSTART_HILITE : "";
            $style['linkstart'] = defined('LINKSTART') ? LINKSTART : '';
            $style['linkdisplay'] = defined('LINKDISPLAY') ? LINKDISPLAY : '';
            $style['linkend'] = defined('LINKEND') ? LINKEND : '';
            $style['linkseparator'] = defined('LINKSEPARATOR') ? LINKSEPARATOR : '';
        }

    // Sublink styles.- replacing the tree-menu.
        if(isset($style['sublinkdisplay']) || isset($style['subindent']) || isset($style['sublinkclass']) || isset($style['sublinkstart']) || isset($style['sublinkend']) || isset($style['subpostlink'])){
            foreach($style as $key=>$val){
                $aSubStyle[$key] = (isset($style["sub".$key])) ? $style["sub".$key] : $style[$key];
            }
        }else{
                $style['subindent'] = "&nbsp;&nbsp;";
                $aSubStyle = $style;
        }

        $text = "\n\n\n<!-- Sitelinks ($cat) -->\n\n\n".$style['prelink'];

        if ($style['linkdisplay'] != 3) {
            foreach ($this->eLinkList['head_menu'] as $key => $link){
                $main_linkid = "sub_".$link['link_id'];

                $link['link_expand'] = ((isset($pref['sitelinks_expandsub']) && $pref['sitelinks_expandsub']) && !$style['linkmainonly'] && !defined("LINKSRENDERONLYMAIN") && isset($this->eLinkList[$main_linkid]) && is_array($this->eLinkList[$main_linkid])) ?  TRUE : FALSE;

                $render_link[$key] = $this->makeLink($link,'', $style, $css_class);

                if(!defined("LINKSRENDERONLYMAIN") && !varset($style['linkmainonly']))  /* if this is defined in theme.php only main links will be rendered */
                {

                    // if there's a submenu. :
                    if (isset($this->eLinkList[$main_linkid]) && is_array($this->eLinkList[$main_linkid])){
                        foreach($this->eLinkList[$main_linkid] as $val) // check that something in the submenu is actually selected.
                        {
                            if($this->hilite($val['link_url'],TRUE)== TRUE || $link['link_expand'] == FALSE)
                            {
                                $substyle = "compact";
                                break;
                            }
                            else
                            {
                                $substyle = "none";
                            }
                        }
                        $render_link[$key] .= "\n\n<div id='{$main_linkid}' style='display:$substyle' class='d_sublink'>\n";
                        foreach ($this->eLinkList[$main_linkid] as $sub){
                            $render_link[$key] .= $this->makeLink($sub, TRUE, $aSubStyle, $css_class);
                        }
                        $render_link[$key] .= "\n</div>\n\n";
                    }
                }
            }
            $text .= implode($style['linkseparator'], $render_link);
            $text .= $style['postlink'];
            if ($style['linkdisplay'] == 2) {
                $text = $ns->tablerender(LAN_SITELINKS_183, $text, 'sitelinks', TRUE);
            }
        }
        else
        {
            foreach($this->eLinkList['head_menu'] as $link)
            {
                if (!count($this->eLinkList['sub_'.$link['link_id']]))
                {
                    $text .= $this->makeLink($link,'', $style, $css_class);
                }
                $text .= $style['postlink'];
            }
            $text = $ns->tablerender(LAN_SITELINKS_183, $text, 'sitelinks_main', TRUE);
            foreach(array_keys($this->eLinkList) as $k)
            {
                $mnu = $style['prelink'];
                foreach($this->eLinkList[$k] as $link)
                {
                    if ($k != 'head_menu')
                    {
                        $mnu .= $this->makeLink($link, TRUE, $style, $css_class);
                    }
                }
                $mnu .= $style['postlink'];
                $text .= $ns->tablerender($k, $mnu, 'sitelinks_sub', TRUE);
            }
        }
        $text .= "\n\n\n<!-- end Site Links -->\n\n\n";
        if($usecache)
        {
            $e107cache->set('sitelinks_'.$cat.md5($linkstyle.e_PAGE.e_QUERY), $text);
        }
        return $text;
    }

    function makeLink($linkInfo, $submenu = FALSE, $style='', $css_class = false)
    {
        global $pref,$tp;

        // Start with an empty link
        $linkstart = $indent = $linkadd = $screentip = $href = $link_append = '';
        $highlighted = FALSE;

        // If submenu: Fix Name, Add Indentation.
        if ($submenu == TRUE) {
            if(substr($linkInfo['link_name'],0,8) == "submenu."){
                $tmp = explode('.', $linkInfo['link_name'], 3);
                $linkInfo['link_name'] = $tmp[2];
            }
            $indent = ($style['linkdisplay'] != 3) ? $style['subindent'] : "";
        }

        // Convert any {e_XXX} to absolute URLs (relative ones sometimes get broken by adding e_HTTP at the front)
        $linkInfo['link_url'] = $tp -> replaceConstants($linkInfo['link_url'], TRUE, TRUE); // replace {e_xxxx}

        if(strpos($linkInfo['link_url'],"{") !== FALSE){
            $linkInfo['link_url'] = $tp->parseTemplate($linkInfo['link_url'], TRUE); // shortcode in URL support - dynamic urls for multilanguage.
        }
        // By default links are not highlighted.
        $linkstart = $style['linkstart'];
        $linkadd = ($style['linkclass']) ? " class='".$style['linkclass']."'" : "";
        $linkadd = ($css_class) ? " class='".$css_class."'" : $linkadd;

        // Check for screentip regardless of URL.
        if (isset($pref['linkpage_screentip']) && $pref['linkpage_screentip'] && $linkInfo['link_description']){
            $screentip = " title = \"".$tp->toHTML($linkInfo['link_description'],"","value, emotes_off, defs, no_hook")."\"";
        }

        // Check if its expandable first. It should override its URL.
        if (isset($linkInfo['link_expand']) && $linkInfo['link_expand']){
            $href = " href=\"javascript:expandit('sub_".$linkInfo['link_id']."')\"";
        } elseif ($linkInfo['link_url']){

            // Only add the e_BASE if it actually has an URL.
            $linkInfo['link_url'] = (strpos($linkInfo['link_url'], '://') === FALSE && strpos($linkInfo['link_url'], 'mailto:') !== 0 ? e_HTTP.$linkInfo['link_url'] : $linkInfo['link_url']);

            // Only check if its highlighted if it has an URL
            if ($this->hilite($linkInfo['link_url'], $style['linkstart_hilite'])== TRUE) {
                $linkstart = (isset($style['linkstart_hilite'])) ? $style['linkstart_hilite'] : "";
                $highlighted = TRUE;
            }
            if ($this->hilite($linkInfo['link_url'], $style['linkclass_hilite'])== TRUE) {
                $linkadd = (isset($style['linkclass_hilite'])) ? " class='".$style['linkclass_hilite']."'" : "";
                $highlighted = TRUE;
            }

            if ($linkInfo['link_open'] == 4 || $linkInfo['link_open'] == 5){
                $dimen = ($linkInfo['link_open'] == 4) ? "600,400" : "800,600";
                $href = " href=\"javascript:open_window('".$linkInfo['link_url']."',{$dimen})\"";
            } else {
                $href = " href='".$linkInfo['link_url']."'";
            }

            // Open link in a new window.  (equivalent of target='_blank' )
            $link_append = ($linkInfo['link_open'] == 1) ? " rel='external'" : "";
        }

        // Remove default images if its a button and add new image at the start.
        if ($linkInfo['link_button']){
            $linkstart = preg_replace('/\<img.*\>/si', '', $linkstart);
            $linkstart .= "<img src='".e_IMAGE_ABS."icons/".$linkInfo['link_button']."' alt='' style='vertical-align:middle' />";
        }

        // mobile phone support.
        $accesskey = (isset($style['accesskey']) && $style['accesskey']==TRUE) ? " accesskey='".$linkInfo['link_order']."' " : "";
        $accessdigit = (isset($style['accessdigit'],$style['accesskey']) && $style['accessdigit']==TRUE && $style['accesskey']==TRUE) ? $linkInfo['link_order'].". " : "";

        // If its a link.. make a link
        $_link = "";
        $_link .= $accessdigit;
        if (!empty($href) && ((varset($style['hilite_nolink']) && $highlighted)!=TRUE)){
            $_link .= "<a".$linkadd.$screentip.$href.$link_append.$accesskey.">".$tp->toHTML($linkInfo['link_name'],"","emotes_off, defs, no_hook")."</a>";
        // If its not a link, but has a class or screentip do span:
        }elseif (!empty($linkadd) || !empty($screentip)){
            $_link .= "<span".$linkadd.$screentip.">".$tp->toHTML($linkInfo['link_name'],"","emotes_off, defs, no_hook")."</span>";
            // Else just the name:
        }else {
            $_link .= $tp->toHTML($linkInfo['link_name'],"","emotes_off, defs, no_hook");
        }

        $_link = $linkstart.$indent.$_link;

        return $_link.$style['linkend']."\n";
    }





function hilite($link,$enabled=''){
    global $PLUGINS_DIRECTORY,$tp,$pref;
    if(!$enabled){ return FALSE; }

    $link = $tp->replaceConstants($link, '', TRUE);
    $tmp = explode("?",$link);
    $link_qry = (isset($tmp[1])) ? $tmp[1] : "";
    $link_slf = (isset($tmp[0])) ? $tmp[0] : "";
    $link_pge = basename($link_slf);
	$link_match = (empty($tmp[0])) ? "": strpos(e_SELF,$tmp[0]);	// e_SELF is the actual displayed page

    if(e_MENU == "debug" && getperms('0'))
    {
        echo "<br />link= ".$link;
        echo "<br />link_q= ".$link_qry;
        echo "<br />url= ".e_PAGE;
        echo "<br />url_query= ".e_QUERY."<br />";

    }

// ----------- highlight overriding - set the link matching in the page itself.

    if(defined("HILITE")){
        if(strpos($link,HILITE)){
            return TRUE;
        }
    }


// --------------- highlighting for 'HOME'. ----------------
    global $pref;
    if (isset($pref['frontpage']['all']))
    {
      list($fp,$fp_q) = explode("?",$pref['frontpage']['all']."?");
      if (strpos(e_SELF,"/".$pref['frontpage']['all'])!== FALSE && $fp_q == varset($tmp[1],'') && $link == e_HTTP."index.php")
      {
        return TRUE;
      }
    }

// --------------- highlighting for plugins. ----------------
        if(stristr($link, $PLUGINS_DIRECTORY) !== FALSE && stristr($link, "custompages") === FALSE){

            if($link_qry)
            {  // plugin links with queries
                $subq = explode("?",$link);
                if(strpos(e_SELF,$subq[0]) && e_QUERY == $subq[1]){
                    return TRUE;
                }else{
                    return FALSE;
                }
            }
            else
            {  // plugin links without queries
                $link = str_replace("../", "", $link);
                if(stristr(dirname(e_SELF), dirname($link)) !== FALSE){
                    return TRUE;
                }
            }
            return FALSE;
        }

// --------------- highlight for news items.----------------
// eg. news.php, news.php?list.1 or news.php?cat.2 etc
    if(substr(basename($link),0,8) == "news.php")
    {

        if (strpos($link, "news.php?") !== FALSE && strpos(e_SELF,"/news.php")!==FALSE) {

            $lnk = explode(".",$link_qry); // link queries.
            $qry = explode(".",e_QUERY); // current page queries.

            if($qry[0] == "item")
            {
                return ($qry[2] == $lnk[1]) ? TRUE : FALSE;
            }

            if($qry[0] == "all" && $lnk[0] == "all")
            {
                return TRUE;
            }

            if($lnk[0] == $qry[0] && $lnk[1] == $qry[1])
            {
                return TRUE;
            }

            if($qry[1] == "list" && $lnk[0] == "list" && $lnk[1] == $qry[2])
            {
                return TRUE;
            }

        }
        elseif (!e_QUERY && e_PAGE == "news.php")
        {

            return TRUE;
        }
            return FALSE;

    }
// --------------- highlight for Custom Pages.----------------
// eg. page.php?1

        if (strpos($link, "page.php?") !== FALSE && strpos(e_SELF,"/page.php")) {
            list($custom,$page) = explode(".",$link_qry);
            list($q_custom,$q_page) = explode(".",e_QUERY);
            if($custom == $q_custom){
                return TRUE;
            }else{
                return FALSE;
            }
        }

// --------------- highlight default ----------------
        if(strpos($link, "?") !== FALSE){

            $thelink = str_replace("../", "", $link);
            if((strpos(e_SELF,$thelink) !== false) && (strpos(e_QUERY,$link_qry) !== false)){
                return true;
            }
        }
		if(!preg_match("/all|item|cat|list/", e_QUERY) && (empty($link) == false) && (strpos(e_SELF, str_replace("../", "",$link)) !== false)){
            return true;
        }

		if((!$link_qry && !e_QUERY) && (empty($link) == FALSE) && (strpos(e_SELF,$link) !== FALSE)){
            return TRUE;
        }

		if(($link_slf == e_SELF && !link_qry) || (e_QUERY && empty($link) == FALSE && strpos(e_SELF."?".e_QUERY,$link)!== FALSE) ){
            return TRUE;
        }

        return FALSE;
    }
}
?>
