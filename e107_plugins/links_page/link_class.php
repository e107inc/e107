<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/links_page/link_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

class linkclass 
{

    function LinkPageDefaultPrefs()
	{
        $linkspage_pref['link_page_categories'] = "0";
        $linkspage_pref['link_submit'] = "0";
        $linkspage_pref['link_submit_class'] = "0";
        $linkspage_pref['link_submit_directpost'] = "0";
        $linkspage_pref["link_nextprev"] = "1";
        $linkspage_pref["link_nextprev_number"] = "20";
        $linkspage_pref['link_comment'] = "";
        $linkspage_pref['link_rating'] = "";

        $linkspage_pref['link_navigator_frontpage'] = "";
        $linkspage_pref['link_navigator_submit'] = "";
        $linkspage_pref['link_navigator_manager'] = "";
        $linkspage_pref['link_navigator_refer'] = "";
        $linkspage_pref['link_navigator_rated'] = "";
        $linkspage_pref['link_navigator_allcat'] = "";
        $linkspage_pref['link_navigator_links'] = "";
        $linkspage_pref['link_navigator_category'] = "";

        $linkspage_pref['link_cat_icon'] = "1";
        $linkspage_pref['link_cat_desc'] = "1";
        $linkspage_pref['link_cat_amount'] = "1";
        $linkspage_pref['link_cat_total'] = "1";
        $linkspage_pref['link_cat_empty'] = "1";
        $linkspage_pref['link_cat_icon_empty'] = "0";
        $linkspage_pref['link_cat_sortorder'] = "0";
        $linkspage_pref['link_cat_sort'] = "link_category_name";
        $linkspage_pref['link_cat_order'] = "ASC";
        $linkspage_pref['link_cat_resize_value'] = "50";

        $linkspage_pref['link_icon'] = "1";
        $linkspage_pref['link_referal'] = "1";
        $linkspage_pref['link_url'] = "0";
        $linkspage_pref['link_desc'] = "1";
        $linkspage_pref['link_icon_empty'] = "0";
        $linkspage_pref['link_sortorder'] = "0";
        $linkspage_pref['link_sort'] = "order";
        $linkspage_pref['link_order'] = "ASC";
        $linkspage_pref['link_open_all'] = "5";         //use individual link open type setting
        $linkspage_pref['link_resize_value'] = "100";

        $linkspage_pref['link_manager'] = "0";
        $linkspage_pref['link_manager_class'] = "0";
        $linkspage_pref['link_directpost'] = "0";
        $linkspage_pref['link_directdelete'] = "0";

        $linkspage_pref['link_refer_minimum'] = "";

        $linkspage_pref['link_rating_minimum'] = "";

        $linkspage_pref['link_menu_caption'] = LCLAN_OPT_86;
        $linkspage_pref['link_menu_navigator_frontpage'] = "1";
        $linkspage_pref['link_menu_navigator_submit'] = "1";
        $linkspage_pref['link_menu_navigator_manager'] = "1";
        $linkspage_pref['link_menu_navigator_refer'] = "1";
        $linkspage_pref['link_menu_navigator_rated'] = "1";
        $linkspage_pref['link_menu_navigator_links'] = "1";
        $linkspage_pref['link_menu_navigator_category'] = "1";
        $linkspage_pref['link_menu_navigator_rendertype'] = "";
        $linkspage_pref['link_menu_navigator_caption'] = LCLAN_OPT_82;
        $linkspage_pref['link_menu_category'] = "1";
        $linkspage_pref['link_menu_category_amount'] = "1";
        $linkspage_pref['link_menu_category_rendertype'] = "";
        $linkspage_pref['link_menu_category_caption'] = LCLAN_OPT_83;
        $linkspage_pref['link_menu_recent'] = "1";
        $linkspage_pref['link_menu_recent_category'] = "";
        $linkspage_pref['link_menu_recent_description'] = "";
        $linkspage_pref["link_menu_recent_number"] = "5";
        $linkspage_pref['link_menu_recent_caption'] = LCLAN_OPT_84;

        return $linkspage_pref;
    }

    function getLinksPagePref()
	{
        global $sql, $eArrayStorage;

        $num_rows = $sql -> db_Select("core", "*", "e107_name='links_page' ");
        if ($num_rows == 0) 
		{
            $linkspage_pref = $this->LinkPageDefaultPrefs();
            $tmp = $eArrayStorage->WriteArray($linkspage_pref);
            $sql -> db_Insert("core", "'links_page', '{$tmp}' ");
            $sql -> db_Select("core", "*", "e107_name='links_page' ");
        }
        $row = $sql -> db_Fetch();
        $linkspage_pref = $eArrayStorage->ReadArray($row['e107_value']);

        return $linkspage_pref;
    }


    function UpdateLinksPagePref()
	{
        global $sql, $eArrayStorage, $tp, $admin_log;

        $num_rows = $sql -> db_Select("core", "*", "e107_name='links_page' ");
        if ($num_rows == 0) 
		{
            $sql -> db_Insert("core", "'links_page', '' ");	// Create dummy entry if none present
			$oldPref = array();
        }
		else
		{
            $row = $sql -> db_Fetch(MYSQL_ASSOC);
			$oldPref = $eArrayStorage->ReadArray($row['e107_value']);
			unset($row);
		}
		$linkspage_pref = array();
		//assign new preferences
		foreach($_POST as $k => $v)
		{
			if(strpos($k, "link_") === 0)
			{
				$linkspage_pref[$k] = $tp->toDB($v);
			}
		}

		if ($admin_log->logArrayDiffs($linkspage_pref, $oldPref, 'LINKS_01'))
		{
			//create new array of preferences
			$tmp = $eArrayStorage->WriteArray($linkspage_pref);
			$sql -> db_Update("core", "`e107_value` = '{$tmp}' WHERE `e107_name` = 'links_page' ");
		}
        return $linkspage_pref;
    }


	function ShowNextPrev($from='0', $number, $total)
	{
		global $linkspage_pref, $qs, $tp, $link_shortcodes, $LINK_NEXTPREV, $LINK_NP_TABLE, $pref;

		$number = (e_PAGE == 'admin_linkspage_config.php' ? '20' : $number);
		if($total<=$number)
		{
			return;
		}
		if(e_PAGE == 'admin_linkspage_config.php' || (isset($linkspage_pref["link_nextprev"]) && $linkspage_pref["link_nextprev"]))
		{
			$np_querystring = e_SELF."?[FROM]".(isset($qs[0]) ? ".".$qs[0] : "").(isset($qs[1]) ? ".".$qs[1] : "").(isset($qs[2]) ? ".".$qs[2] : "").(isset($qs[3]) ? ".".$qs[3] : "").(isset($qs[4]) ? ".".$qs[4] : "");
			$parms = $total.",".$number.",".$from.",".$np_querystring."";
			$LINK_NEXTPREV = $tp->parseTemplate("{NEXTPREV={$parms}}");

			if(!isset($LINK_NP_TABLE)){
				$template = (e_PAGE == 'admin_linkspage_config.php' ? e_THEME.$pref['sitetheme']."/" : THEME)."links_template.php";
				if(is_readable($template)){
					require_once($template);
				}else{
					require_once(e_PLUGIN."links_page/links_template.php");
				}
			}
			echo $tp -> parseTemplate($LINK_NP_TABLE, FALSE, $link_shortcodes);
		}
	}


    function setPageTitle()
	{
        global $sql, $qs, $linkspage_pref;

        //show all categories
        if(!isset($qs[0]) && $linkspage_pref['link_page_categories']){
            $page = LCLAN_PAGETITLE_1." / ".LCLAN_PAGETITLE_2;
        }
        //show all categories
        if(isset($qs[0]) && $qs[0] == "cat" && !isset($qs[1]) ){
            $page = LCLAN_PAGETITLE_1." / ".LCLAN_PAGETITLE_2;
        }
        //show all links in all categories
        if( (!isset($qs[0]) && !$linkspage_pref['link_page_categories']) || (isset($qs[0]) && $qs[0] == "all") ){
            $page = LCLAN_PAGETITLE_1." / ".LCLAN_PAGETITLE_3;
        }
        //show all links in one categories
        if(isset($qs[0]) && $qs[0] == "cat" && isset($qs[1]) && is_numeric($qs[1])){
            $sql -> db_Select("links_page_cat", "link_category_name", "link_category_id='".$qs[1]."' ");
            $row2 = $sql -> db_Fetch();
            $page = LCLAN_PAGETITLE_1." / ".LCLAN_PAGETITLE_4." / ".$row2['link_category_name'];
        }
        //view top rated
        if(isset($qs[0]) && $qs[0] == "rated"){
            $page = LCLAN_PAGETITLE_1." / ".LCLAN_PAGETITLE_5;
        }
        //view top refer
        if(isset($qs[0]) && $qs[0] == "top"){
            $page = LCLAN_PAGETITLE_1." / ".LCLAN_PAGETITLE_6;
        }
        //personal link managers
        if (isset($qs[0]) && $qs[0] == "manage"){
            $page = LCLAN_PAGETITLE_1." / ".LCLAN_PAGETITLE_7;
        }
        //comments on links
        if (isset($qs[0]) && $qs[0] == "comment" && isset($qs[1]) && is_numeric($qs[1]) ){
            $page = LCLAN_PAGETITLE_1." / ".LCLAN_PAGETITLE_8;
        }
        //submit link
        if (isset($qs[0]) && $qs[0] == "submit" && check_class($linkspage_pref['link_submit_class'])) {
            $page = LCLAN_PAGETITLE_1." / ".LCLAN_PAGETITLE_9;
        }
        //define("e_PAGETITLE", strtolower($page));
        define("e_PAGETITLE", $page);
    }




    function parse_link_append($rowl)
	{
        global $tp, $linkspage_pref;
        if($linkspage_pref['link_open_all'] && $linkspage_pref['link_open_all'] == "5"){
            $link_open_type = $rowl['link_open'];
        }else{
            $link_open_type = $linkspage_pref['link_open_all'];
        }

        switch ($link_open_type) {
            case 1:
            $lappend = "<a class='linkspage_url' href='".$rowl['link_url']."' onclick=\"open_window('".e_PLUGIN."links_page/links.php?view.".$rowl['link_id']."','full');return false;\" >"; // Googlebot won't see it any other way.
            break;
            case 2:
            $lappend = "<a class='linkspage_url' href='".$rowl['link_url']."' onclick=\"location.href='".e_PLUGIN."links_page/links.php?view.".$rowl['link_id']."';return false\" >";  // Googlebot won't see it any other way.
            break;
            case 3:
            $lappend = "<a class='linkspage_url' href='".$rowl['link_url']."' onclick=\"location.href='".e_PLUGIN."links_page/links.php?view.".$rowl['link_id']."';return false\" >";  // Googlebot won't see it any other way.
            break;
            case 4:
            $lappend = "<a class='linkspage_url' href='".$rowl['link_url']."' onclick=\"open_window('".e_PLUGIN."links_page/links.php?view.".$rowl['link_id']."');return false\">"; // Googlebot won't see it any other way.
            break;
            default:
            $lappend = "<a class='linkspage_url' href='".$rowl['link_url']."' onclick=\"location.href='".e_PLUGIN."links_page/links.php?view.".$rowl['link_id']."';return false\" >";  // Googlebot won't see it any other way.
        }
        return $lappend;
    }





    function showLinkSort($mode='')
	{
        global $rs, $ns, $qs, $linkspage_pref;

        $check = "";
        if($qs){
            for($i=0;$i<count($qs);$i++){
                if($qs[$i] && substr($qs[$i],0,5) == "order"){
                    $check = $qs[$i];
                    break;
                }
            }
        }
        if($check){
            //string is like : order + a + heading
            $checks = substr($check,6);
            $checko = substr($check,5,1);
        }else{
            $checks = "";
            $checko = "";
        }
        $baseurl = e_PLUGIN."links_page/links.php";
        $qry = (isset($qs[0]) && substr($qs[0],0,5) != "order" ? $qs[0] : "").(isset($qs[1]) && substr($qs[1],0,5) != "order" ? ".".$qs[1] : "").(isset($qs[2]) && substr($qs[2],0,5) != "order" ? ".".$qs[2] : "").(isset($qs[3]) && substr($qs[3],0,5) != "order" ? ".".$qs[3] : "");

        $sotext = "
        ".$rs -> form_open("post", e_SELF, "linkorder", "", "enctype='multipart/form-data'")."
            ".LAN_LINKS_15."
            ".$rs -> form_select_open("link_sort");
            if($mode == "cat"){
                $sotext .= "
                ".$rs -> form_option(LAN_LINKS_4, ($checks == "heading" ? "1" : "0"), "heading", "")."
                ".$rs -> form_option(LAN_LINKS_44, ($checks == "id" ? "1" : "0"), "id", "")."
                ".$rs -> form_option(LAN_LINKS_6, ($checks == "order" ? "1" : "0"), "order", "");
            }else{
                $sotext .= "
                ".$rs -> form_option(LAN_LINKS_4, ($checks == "heading" ? "1" : "0"), "heading", "")."
                ".$rs -> form_option(LAN_LINKS_5, ($checks == "url" ? "1" : "0"), "url", "")."
                ".$rs -> form_option(LAN_LINKS_6, ($checks == "order" ? "1" : "0"), "order", "")."
                ".$rs -> form_option(LAN_LINKS_7, ($checks == "refer" ? "1" : "0"), "refer", "")."
                ".$rs -> form_option(LAN_LINKS_38, ($checks == "date" ? "1" : "0"), "date", "");
            }
            $sotext .= "
            ".$rs -> form_select_close()."
            ".LAN_LINKS_6."
            ".$rs -> form_select_open("link_order")."
            ".$rs -> form_option(LAN_LINKS_8, ($checko == "a" ? "1" : "0"), $baseurl."?".($qry ? $qry."." : "")."ordera", "")."
            ".$rs -> form_option(LAN_LINKS_9, ($checko == "d" ? "1" : "0"), $baseurl."?".($qry ? $qry."." : "")."orderd", "")."
            ".$rs -> form_select_close()."
            ".$rs -> form_button("button", "submit", LCLAN_ITEM_36, " onclick=\"document.location=link_order.options[link_order.selectedIndex].value+link_sort.options[link_sort.selectedIndex].value;\"", "", "")."
        ".$rs -> form_close();

        return $sotext;
    }


    function parseOrderCat($orderstring)
	{
        if(substr($orderstring,6) == "heading"){
            $orderby        = "link_category_name";
            $orderby2       = "";
        }elseif(substr($orderstring,6) == "id"){
            $orderby        = "link_category_id";
            $orderby2       = ", link_category_name ASC";
        }elseif(substr($orderstring,6) == "order"){
            $orderby        = "link_category_order";
            $orderby2       = ", link_category_name ASC";
        }else{
            $orderstring    = "orderdheading";
            $orderby        = "link_category_name";
            $orderby2       = ", link_category_name ASC";
        }
        return $orderby." ".(substr($orderstring,5,1) == "a" ? "ASC" : "DESC")." ".$orderby2;
    }

    function parseOrderLink($orderstring)
	{
        if(substr($orderstring,6) == "heading"){
            $orderby        = "link_name";
            $orderby2       = "";
        }elseif(substr($orderstring,6) == "url"){
            $orderby        = "link_url";
            $orderby2       = ", link_name ASC";
        }elseif(substr($orderstring,6) == "refer"){
            $orderby        = "link_refer";
            $orderby2       = ", link_name ASC";
        }elseif(substr($orderstring,6) == "date"){
            $orderby        = "link_datestamp";
            $orderby2       = ", link_name ASC";
        }elseif(substr($orderstring,6) == "order"){
            $orderby        = "link_order";
            $orderby2       = ", link_name ASC";
        }else{
            $orderstring    = "orderaorder";
            $orderby        = "link_order";
            $orderby2       = ", link_name ASC";
        }
        return $orderby." ".(substr($orderstring,5,1) == "a" ? "ASC" : "DESC")." ".$orderby2;
    }

    function getOrder($mode='')
	{
        global $qs, $linkspage_pref;

        if(isset($qs[0]) && substr($qs[0],0,5) == "order"){
            $orderstring    = $qs[0];
        }elseif(isset($qs[1]) && substr($qs[1],0,5) == "order"){
            $orderstring    = $qs[1];
        }elseif(isset($qs[2]) && substr($qs[2],0,5) == "order"){
            $orderstring    = $qs[2];
        }elseif(isset($qs[3]) && substr($qs[3],0,5) == "order"){
            $orderstring    = $qs[3];
        }else{
            if($mode == "cat"){
                $orderstring    = "order".($linkspage_pref["link_cat_order"] == "ASC" ? "a" : "d" ).($linkspage_pref["link_cat_sort"] ? $linkspage_pref["link_cat_sort"] : "date" );
            }else{
                $orderstringcat = "order".($linkspage_pref["link_cat_order"] == "ASC" ? "a" : "d" ).($linkspage_pref["link_cat_sort"] ? $linkspage_pref["link_cat_sort"] : "date" );

                $orderstring    = "order".($linkspage_pref["link_order"] == "ASC" ? "a" : "d" ).($linkspage_pref["link_sort"] ? $linkspage_pref["link_sort"] : "date" );
            }
        }

        if($mode == "cat"){
            $str = $this -> parseOrderCat($orderstring);
        }else{
            if(isset($orderstringcat)){
                $str = $this -> parseOrderCat($orderstringcat);
                $str .= ", ".$this -> parseOrderLink($orderstring);
            }else{
                $str = $this -> parseOrderLink($orderstring);
            }
        }

        $order = " ORDER BY ".$str;
        return $order;
    }

    function show_message($message, $caption='') 
	{
        global $ns;
        $ns->tablerender($caption, "<div style='text-align:center'><b>".$message."</b></div>");
    }

    function uploadLinkIcon()
	{
        global $ns, $pref;
        $pref['upload_storagetype'] = "1";
        require_once(e_HANDLER."upload_handler.php");
        $pathicon = e_PLUGIN."links_page/link_images/";
        $uploaded = file_upload($pathicon);

        $icon = "";
        if($uploaded) 
		{
            $icon = $uploaded[0]['name'];
            if($_POST['link_resize_value'])
			{
                require_once(e_HANDLER."resize_handler.php");
                resize_image($pathicon.$icon, $pathicon.$icon, $_POST['link_resize_value'], "nocopy");
            }
        }
        $msg = ($icon ? LCLAN_ADMIN_7 : LCLAN_ADMIN_8);
        $this -> show_message($msg);
    }

    function uploadCatLinkIcon()
	{
        global $ns, $pref;
        $pref['upload_storagetype'] = "1";
        require_once(e_HANDLER."upload_handler.php");
        $pathicon = e_PLUGIN."links_page/cat_images/";
        $uploaded = file_upload($pathicon);

        $icon = "";
        if($uploaded) 
		{
            $icon = $uploaded[0]['name'];
            if($_POST['link_cat_resize_value'])
			{
                require_once(e_HANDLER."resize_handler.php");
                resize_image($pathicon.$icon, $pathicon.$icon, $_POST['link_cat_resize_value'], "nocopy");
            }
        }
        $msg = ($icon ? LCLAN_ADMIN_7 : LCLAN_ADMIN_8);
        $this -> show_message($msg);
    }


    function dbCategoryCreate() 
	{
        global $sql, $tp, $admin_log;
        $link_t = $sql->db_Count("links_page_cat", "(*)");
        $linkData = array();
		$linkData['link_category_name'] = $tp -> toDB($_POST['link_category_name']);
		$linkData['link_category_description'] = $tp -> toDB($_POST['link_category_description']);
		$linkData['link_category_icon'] = $tp -> toDB($_POST['link_category_icon']);
		$linkData['link_category_order'] = $link_t+1;
		$linkData['link_category_class'] = $tp -> toDB($_POST['link_category_class']);
		$linkData['link_category_datestamp'] = time();
        $sql->db_Insert("links_page_cat", $linkData);
		$admin_log->logArrayAll('LINKS_05',$linkData);
        $this->show_message(LCLAN_ADMIN_4);
    }


    function dbCategoryUpdate() 
	{
        global $sql, $tp, $admin_log;
        global $sql, $tp;
        $time = ($_POST['update_datestamp'] ? time() : ($_POST['link_category_datestamp'] != "0" ? $_POST['link_category_datestamp'] : time()) );
        $linkData = array();
		$linkData['link_category_name'] = $tp -> toDB($_POST['link_category_name']);
		$linkData['link_category_description'] = $tp -> toDB($_POST['link_category_description']);
		$linkData['link_category_icon'] = $tp -> toDB($_POST['link_category_icon']);
		$linkData['link_category_order'] = $link_t+1;
		$linkData['link_category_class'] = $tp -> toDB($_POST['link_category_class']);
		$linkData['link_category_datestamp'] = $time;
        $sql->db_UpdateArray("links_page_cat", $linkData," WHERE link_category_id='".intval($_POST['link_category_id'])."'");
		$admin_log->logArrayAll('LINKS_06',$linkData);
        $this->show_message(LCLAN_ADMIN_5);
    }


    function dbOrderUpdate($order) 
	{
        global $sql, $admin_log;
        foreach ($order as $order_id) 
		{
            $tmp = explode(".", $order_id);
			$sql->db_Update("links_page", "link_order=".intval($tmp[1])." WHERE link_id=".intval($tmp[0]));
        }
		$admin_log->logArrayAll('LINKS_07',$order);
        $this->show_message(LCLAN_ADMIN_9);
    }


    function dbOrderCatUpdate($order) 
	{
        global $sql, $admin_log;
        foreach ($order as $order_id) 
		{
            $tmp = explode(".", $order_id);
            $sql->db_Update("links_page_cat", "link_category_order=".intval($tmp[1])." WHERE link_category_id=".intval($tmp[0]));
        }
		$admin_log->logArrayAll('LINKS_08',$order);
        $this->show_message(LCLAN_ADMIN_9);
    }


    function dbOrderUpdateInc($inc) 
	{
        global $sql, $admin_log;
        $tmp = explode(".", $inc);
        $linkid = intval($tmp[0]);
        $link_order = intval($tmp[1]);
        if(isset($tmp[2]))
		{
            $location = intval($tmp[2]);
            $sql->db_Update("links_page", "link_order=link_order+1 WHERE link_order='".($link_order-1)."' AND link_category=".$location);
            $sql->db_Update("links_page", "link_order=link_order-1 WHERE link_id='{$linkid}' AND link_category=".$location);
			$admin_log->log_event('LINKS_09','ID: '.$location.' -inc- '.$link_order,E_LOG_INFORMATIVE,'');
        }
		else
		{
            $sql->db_Update("links_page_cat", "link_category_order=link_category_order+1 WHERE link_category_order=".($link_order-1));
            $sql->db_Update("links_page_cat", "link_category_order=link_category_order-1 WHERE link_category_id=".$linkid);
			$admin_log->log_event('LINKS_10','ID: '.$linkid.' -inc- '.$link_order,E_LOG_INFORMATIVE,'');
        }
    }

    function dbOrderUpdateDec($dec) 
	{
        global $sql, $admin_log;
        $tmp = explode(".", $dec);
        $linkid = intval($tmp[0]);
        $link_order = intval($tmp[1]);
        if(isset($tmp[2]))
		{
            $location = intval($tmp[2]);
            $sql->db_Update("links_page", "link_order=link_order-1 WHERE link_order='".($link_order+1)."' AND link_category=".$location);
            $sql->db_Update("links_page", "link_order=link_order+1 WHERE link_id='{$linkid}' AND link_category=".$location);
			$admin_log->log_event('LINKS_11','ID: '.$location.' -dec- '.$link_order,E_LOG_INFORMATIVE,'');
        }
		else
		{
            $sql->db_Update("links_page_cat", "link_category_order=link_category_order-1 WHERE link_category_order='".($link_order+1)."' ");
            $sql->db_Update("links_page_cat", "link_category_order=link_category_order+1 WHERE link_category_id=".$linkid);
			$admin_log->log_event('LINKS_12','ID: '.$linkid.' -dec- '.$link_order,E_LOG_INFORMATIVE,'');
        }
    }

	function verify_link_manage($id)
	{
		global $sql;

		if ($sql->db_Select("links_page", "link_author", "link_id='".intval($id)."' "))
		{
			$row = $sql->db_Fetch();
		}
		if(varset($row['link_author']) != USERID)
		{
			js_location(SITEURL);
		}
	}

	// Create a new link. If $mode == 'submit', link has to go through the approval process; else its admin entry
    function dbLinkCreate($mode='') 
	{
        global $ns, $tp, $qs, $sql, $e107cache, $e_event, $linkspage_pref, $admin_log;

		$edata_ls = array(
			'link_category' 	=> intval($_POST['cat_id']),
			'link_name'			=> $tp->toDB($_POST['link_name']), 
			'link_url' 			=> $tp->toDB($_POST['link_url']), 
			'link_description' 	=> $tp->toDB($_POST['link_description']), 
			'link_button' 		=> $tp->toDB($_POST['link_but'])
			);

		if (!$edata_ls['link_name'] || !$edata_ls['link_url'] || !$edata_ls['link_description']) 
		{
			message_handler("ALERT", 5);
			return;
		} 

        if ($edata_ls['link_url'] && !strstr($edata_ls['link_url'], "http")) 
		{
			$edata_ls['link_url'] = "http://".$edata_ls['link_url'];
        }

        //create link, submit area, tmp table
		if(isset($mode) && $mode == "submit")
		{
			$edata_ls['username'] = (defined('USERNAME')) ? USERNAME : LAN_LINKS_3;

			$submitted_link     = implode('^', $edata_ls);
			$sql->db_Insert("tmp", "'submitted_link', '".time()."', '$submitted_link' ");

			$edata_ls['submitted_link'] = $submitted_link;
			$e_event->trigger("linksub", $edata_ls);
		  //header("location:".e_SELF."?s");
			js_location(e_SELF."?s");
        }
		else
		{	// Admin-entered link
            $link_t = $sql->db_Count("links_page", "(*)", "WHERE link_category='".intval($_POST['cat_id'])."'");
            $time   = ($_POST['update_datestamp'] ? time() : ($_POST['link_datestamp'] != "0" ? $_POST['link_datestamp'] : time()) );

			$edata_ls['link_open'] = intval($_POST['linkopentype']);
			$edata_ls['link_class'] =intval(varset($_POST['link_class']));
            $edata_ls['link_author'] = USERID;		// Default
            //update link
			if (is_numeric($qs[2]) && $qs[1] != "sn") 
			{
				if($qs[1]!== "manage")
				{
                    $edata_ls['link_author'] = ($_POST['link_author'] && $_POST['link_author']!='' ? $tp -> toDB($_POST['link_author']) : USERID);
                }

				$edata_ls['link_datestamp'] = $time;
                $sql->db_UpdateArray("links_page",   $edata_ls, " WHERE link_id='".intval($qs[2])."'");
				$msg = LCLAN_ADMIN_3;
				$data = array('method'=>'update', 'table'=>'links_page', 'id'=>$qs[2], 'plugin'=>'links_page', 'function'=>'dbLinkCreate');
				$msg .= $e_event->triggerHook($data);
                $admin_log->logArrayAll('LINKS_14',$edata_ls);
                $e107cache->clear("sitelinks");
				$this->show_message($msg);
            //create link
			} 
			else 
			{
				$edata_ls['link_datestamp'] = time();
				$edata_ls['link_order'] = $link_t+1;
                $sql->db_Insert("links_page", $edata_ls);
				$msg = LCLAN_ADMIN_2;
				$id = mysql_insert_id();
				$data = array('method'=>'create', 'table'=>'links_page', 'id'=>$id, 'plugin'=>'links_page', 'function'=>'dbLinkCreate');
				$msg .= $e_event->triggerHook($data);
				$admin_log->logArrayAll('LINKS_13',$edata_ls);
                $e107cache->clear("sitelinks");
                $this->show_message($msg);
            }
            //delete from tmp table after approval
			if (is_numeric($qs[2]) && $qs[1] == "sn") 
			{
                $sql->db_Delete("tmp", "tmp_time=".intval($qs[2]));
            }
        }
    }

    function show_link_create() 
	{
        global $sql, $rs, $qs, $ns, $fl, $linkspage_pref, $e_event;

        $row['link_category']       = "";
        $row['link_name']           = "";
        $row['link_url']            = "";
        $row['link_description']    = "";
        $row['link_button']         = "";
        $row['link_open']           = "";
        $row['link_class']          = "";
        $link_resize_value          = (isset($linkspage_pref['link_resize_value']) && $linkspage_pref['link_resize_value'] ? $linkspage_pref['link_resize_value'] : "100");

        if (isset($qs[1]) && $qs[1] == 'edit' && !isset($_POST['submit'])) 
		{
            if ($sql->db_Select("links_page", "*", "link_id='".intval($qs[2])."' ")) 
			{
                $row = $sql->db_Fetch();
            }
        }

        if (isset($qs[1]) && $qs[1] == 'sn') 
		{
            if ($sql->db_Select("tmp", "*", "tmp_time='".intval($qs[2])."'")) {
                $row = $sql->db_Fetch();
                $submitted                  = explode("^", $row['tmp_info']);
                $row['link_category']       = $submitted[0];
                $row['link_name']           = $submitted[1];
                $row['link_url']            = $submitted[2];
                $row['link_description']    = $submitted[3]."\n[i]".LCLAN_ITEM_1." ".$submitted[5]."[/i]";
                $row['link_button']         = $submitted[4];

            }
        }

        if(isset($_POST['uploadlinkicon'])){
            $row['link_category']       = $_POST['cat_id'];
            $row['link_name']           = $_POST['link_name'];
            $row['link_url']            = $_POST['link_url'];
            $row['link_description']    = $_POST['link_description'];
            $row['link_button']         = $_POST['link_but'];
            $row['link_open']           = $_POST['linkopentype'];
            $row['link_class']          = $_POST['link_class'];
            $link_resize_value          = (isset($_POST['link_resize_value']) && $_POST['link_resize_value'] ? $_POST['link_resize_value'] : $link_resize_value);
        }
        $width = (e_PAGE == "admin_linkspage_config.php" ? ADMIN_WIDTH : "width:100%;");
        $text = "
        <div style='text-align:center'>
        ".$rs -> form_open("post", e_SELF."?".e_QUERY, "linkform", "", "enctype='multipart/form-data'", "")."
        <table style='".$width."' class='fborder' cellspacing='0' cellpadding='0'>
        <tr>
        <td style='width:30%' class='forumheader3'>".LCLAN_ITEM_2."</td>
        <td style='width:70%' class='forumheader3'>";

        if (!$link_cats = $sql->db_Select("links_page_cat")) {
            $text .= LCLAN_ITEM_3."<br />";
        } else {
            $text .= $rs -> form_select_open("cat_id", "");
            while (list($cat_id, $cat_name, $cat_description) = $sql->db_Fetch()) {
                if ( (isset($row['link_category']) && $row['link_category'] == $cat_id) || (isset($row['linkid']) && $cat_id == $row['linkid'] && $action == "add") ) {
                    $text .= $rs -> form_option($cat_name, "1", $cat_id, "");
                } else {
                    $text .= $rs -> form_option($cat_name, "0", $cat_id, "");
                }
            }
            $text .= $rs -> form_select_close();
        }
        $text .= "
        </td>
        </tr>
        <tr>
        <td style='width:30%' class='forumheader3'>".LCLAN_ITEM_4."</td>
        <td style='width:70%' class='forumheader3'>
            ".$rs -> form_text("link_name", 60, $row['link_name'], 100)."
        </td>
        </tr>
        <tr>
        <td style='width:30%' class='forumheader3'>".LCLAN_ITEM_5."</td>
        <td style='width:70%' class='forumheader3'>
            ".$rs -> form_text("link_url", 60, $row['link_url'], 200)."
        </td>
        </tr>
        <tr>
        <td style='width:30%; vertical-align:top;' class='forumheader3'>".LCLAN_ITEM_6."</td>
        <td style='width:70%' class='forumheader3'>
            ".$rs -> form_textarea("link_description", '59', '3', $row['link_description'], "", "", "", "", "")."
        </td>
        </tr>
        <tr>
        <td style='width:30%; vertical-align:top;' class='forumheader3'>".LCLAN_ITEM_7."</td>
        <td style='width:70%' class='forumheader3'>";
            if(!FILE_UPLOADS){
                $text .= "<b>".LCLAN_ITEM_9."</b>";
            }else{
                if(!is_writable(e_PLUGIN."links_page/link_images/")){
                    $text .= "<b>".LCLAN_ITEM_10." ".e_PLUGIN."links_page/link_images/ ".LCLAN_ITEM_11."</b><br />";
                }
                $text .= "
                <input class='tbox' type='file' name='file_userfile[]'  size='58' /><br />
                ".LCLAN_ITEM_8." ".$rs -> form_text("link_resize_value", 3, $link_resize_value, 3)."&nbsp;".LCLAN_ITEM_12."
                ".$rs -> form_button("submit", "uploadlinkicon", LCLAN_ITEM_13, "", "", "");
            }
        $text .= "
        </td>
        </tr>";

//        $rejectlist = array('$.','$..','/','CVS','thumbs.db','Thumbs.db','*._$', 'index', 'null*', 'blank*');
        $iconpath = e_PLUGIN."links_page/link_images/";
        $iconlist = $fl->get_files($iconpath);
        $iconpath = e_PLUGIN_ABS."links_page/link_images/";			// Absolute paths now we've got the files

        $text .= "
        <tr>
        <td style='width:30%; vertical-align:top;' class='forumheader3'>".LCLAN_ITEM_14."</td>
        <td style='width:70%; vertical-align:top;' class='forumheader3'>
        <input class='tbox' type='text' name='link_but' id='link_but' size='60' value='".$row['link_button']."' maxlength='100' />
            <div id='linkbut' style='display:block; vertical-align:top;'><table style='text-align:left; width:100%;'><tr><td style='width:20%; padding-right:10px;'>";
            $selectjs   = " onchange=\"document.getElementById('link_but').value=this.options[this.selectedIndex].value; if(this.options[this.selectedIndex].value!=''){document.getElementById('iconview').src='".$iconpath."'+this.options[this.selectedIndex].value; document.getElementById('iconview').style.display='block';}else{document.getElementById('iconview').src='';document.getElementById('iconview').style.display='none';}\"";
            $text       .= $rs -> form_select_open("link_button", $selectjs);
            $text       .= $rs -> form_option(LCLAN_ITEM_34, ($row['link_button'] ? "0" : "1"), "");
            foreach($iconlist as $icon){
                $text   .= $rs -> form_option($icon['fname'], ($icon['fname'] == $row['link_button'] ? "1" : "0"), $icon['fname'] );
            }
            $text .= $rs -> form_select_close();
            if(isset($row['link_button']) && $row['link_button']){
                $img = $iconpath.$row['link_button'];
            }else{
                $blank_display = 'display: none';
                $img = e_PLUGIN_ABS."links_page/images/blank.gif";
            }
            $text .= "</td><td><img id='iconview' src='".$img."' style='width:".$link_resize_value."px; ".$blank_display."' /><br /><br /></td></tr></table>";
            $text .= "</div>
        </td>
        </tr>";

        //0=same window, 1=_blank, 2=_parent, 3=_top, 4=miniwindow
        $text .= "
        <tr>
        <td style='width:30%; vertical-align:top;' class='forumheader3'>".LCLAN_ITEM_16."</td>
        <td style='width:70%' class='forumheader3'>
            ".$rs -> form_select_open("linkopentype")."
            ".$rs -> form_option(LCLAN_ITEM_17, ($row['link_open'] == "0" ? "1" : "0"), "0", "")."
            ".$rs -> form_option(LCLAN_ITEM_18, ($row['link_open'] == "1" ? "1" : "0"), "1", "")."
            ".$rs -> form_option(LCLAN_ITEM_19, ($row['link_open'] == "4" ? "1" : "0"), "4", "")."
            ".$rs -> form_select_close()."
        </td>
        </tr>
        <tr>
        <td style='width:30%; vertical-align:top;' class='forumheader3'>".LCLAN_ITEM_20."</td>
        <td style='width:70%' class='forumheader3'>
            ".r_userclass("link_class", $row['link_class'], "off", "public,guest,nobody,member,admin,classes")."
        </td>
        </tr>";

		//triggerHook
		$data = array('method'=>'form', 'table'=>'links_page', 'id'=>$row['link_id'], 'plugin'=>'links_page', 'function'=>'show_link_create');
		$hooks = $e_event->triggerHook($data);
		if(!empty($hooks))
		{
			$text .= "<tr><td class='fcaption' colspan='2' >".LAN_HOOKS." </td></tr>";
			foreach($hooks as $hook)
			{
				if(!empty($hook))
				{
					$text .= "
					<tr>
					<td style='width:30%; vertical-align:top;' class='forumheader3'>".$hook['caption']."</td>
					<td style='width:70%' class='forumheader3'>".$hook['text']."</td>
					</tr>";
				}
			}
		}

		$text .= "
        <tr style='vertical-align:top'>
        <td colspan='2' style='text-align:center' class='forumheader'>";
        if (isset($qs[2]) && $qs[2] && $qs[1] == "edit") {
            $text .= $rs -> form_hidden("link_datestamp", $row['link_datestamp']);
            $text .= $rs -> form_checkbox("update_datestamp", 1, 0)." ".LCLAN_ITEM_21."<br /><br />";
            $text .= $rs -> form_button("submit", "add_link", LCLAN_ITEM_22, "", "", "").$rs -> form_hidden("link_id", $row['link_id']).$rs -> form_hidden("link_author", $row['link_author']);

        } else {
            $text .= $rs -> form_button("submit", "add_link", LCLAN_ITEM_23, "", "", "");
        }
        $text .= "</td>
        </tr>
        </table>
        ".$rs -> form_close()."
        </div>";

        $ns->tablerender(LCLAN_PAGETITLE_1, $text);
    }

    function show_links() 
	{
        global $sql, $qs, $rs, $ns, $tp, $from;
        $number = "20";
		$LINK_CAT_NAME = '';			// May be appropriate to add a shortcode later

        if($qs[2] == "all")
		{	// Show all categories
            $caption = LCLAN_ITEM_38;
            $qry = " link_id != '' ORDER BY link_category ASC, link_order ASC";
        }
		else
		{	// Show single category
            if ($sql->db_Select("links_page_cat", "link_category_name", "link_category_id='".intval($qs[2])."' " )) 
			{
                $row = $sql->db_Fetch();
                $caption = LCLAN_ITEM_2." ".$row['link_category_name'];
            }
            $qry = " link_category=".intval($qs[2])." ORDER BY link_order, link_id ASC";
        }

        $link_total = $sql->db_Select("links_page", "*", " ".$qry." ");
        if (!$sql->db_Select("links_page", "*", " ".$qry." LIMIT ".intval($from).",".intval($number)." ")) 
		{
          js_location(e_SELF."?link");
        }
		else
		{	// Display the individual links
            $text = $rs->form_open("post", e_SELF.(e_QUERY ? "?".e_QUERY : ""), "myform_{$row['link_id']}", "", "");
            $text .= "<div style='text-align:center'>
            <table class='fborder' style='".ADMIN_WIDTH."'>
            <tr>
            <td class='fcaption' style='width:5%'>".LCLAN_ITEM_25."</td>
            <td class='fcaption' style='width:65%'>".LCLAN_ITEM_26."</td>
            <td class='fcaption' style='width:10%'>".LCLAN_ITEM_27."</td>
            <td class='fcaption' style='width:10%'>".LCLAN_ITEM_28."</td>
            <td class='fcaption' style='width:10%'>".LCLAN_ITEM_29."</td>
            </tr>";
            while ($row = $sql->db_Fetch()) 
			{
                $linkid = $row['link_id'];
                $img = "";
                if ($row['link_button']) 
				{
                    if (strpos($row['link_button'], "http://") !== FALSE) 
					{
                        $img = "<img src='".$row['link_button']."' alt='".$LINK_CAT_NAME."' />";
                    } 
					else 
					{
                        if(strstr($row['link_button'], "/"))
						{
                            $img = "<img src='".e_BASE.$row['link_button']."' alt='".$LINK_CAT_NAME."' />";
                        }
						else
						{
                            $img = "<img src='".e_PLUGIN_ABS."links_page/link_images/".$row['link_button']."' alt='".$LINK_CAT_NAME."' />";
                        }
                    }
                }
                if($row['link_order'] == "1"){
                    $up = "&nbsp;&nbsp;&nbsp;";
                }else{
                    $up = "<input type='image' src='".LINK_ICON_ORDER_UP_BASE."' value='".$linkid.".".$row['link_order'].".".$row['link_category']."' name='inc' />";
                }
                if($row['link_order'] == $link_total){
                    $down = "&nbsp;&nbsp;&nbsp;";
                }else{
                    $down = "<input type='image' src='".LINK_ICON_ORDER_DOWN_BASE."' value='".$linkid.".".$row['link_order'].".".$row['link_category']."' name='dec' />";
                }
                $text .= "
                <tr>
                <td class='forumheader3' style='width:5%; text-align: center; vertical-align: middle'>".$img."</td>
                <td style='width:65%' class='forumheader3'>
                    <a href='".e_PLUGIN_ABS."links_page/links.php?".$row['link_id']."' rel='external'>".LINK_ICON_LINK."</a> ".$row['link_name']."
                </td>
                <td style='width:10%; text-align:center; white-space: nowrap' class='forumheader3'>
                    <a href='".e_SELF."?link.edit.".$linkid."' title='".LCLAN_ITEM_31."'>".LINK_ICON_EDIT."</a>
                    <input type='image' title='delete' name='delete[main_{$linkid}]' alt='".LCLAN_ITEM_32."' src='".LINK_ICON_DELETE_BASE."' onclick=\"return jsconfirm('".$tp->toJS(LCLAN_ITEM_33." [ ".$row['link_name']." ]")."')\" />
                </td>
                <td style='width:10%; text-align:center; white-space: nowrap' class='forumheader3'>
                    ".$up."
                    ".$down."
                </td>
                <td style='width:10%; text-align:center' class='forumheader3'>
                    <select name='link_order[]' class='tbox'>";
                    //".$rs -> form_select_open("link_order[]");
                    for($a = 1; $a <= $link_total; $a++) {
                        $text .= $rs -> form_option($a, ($row['link_order'] == $a ? "1" : "0"), $linkid.".".$a, "");
                    }
                    $text .= $rs -> form_select_close()."
                </td>
                </tr>";
            }
            $text .= "
            <tr>
            <td class='forumheader' colspan='4'>&nbsp;</td>
            <td class='forumheader' style='width:5%; text-align:center'>
            ".$rs->form_button("submit", "update_order", LCLAN_ITEM_30)."
            </td>
            </tr>
            </table></div>
            ".$rs->form_close();
        }
        $ns->tablerender($caption, $text);
		$this->ShowNextPrev($from, $number, $link_total);
    }

    function show_cat_create() {
        global $qs, $sql, $rs, $ns, $tp, $fl;

        $row['link_category_name']          = "";
        $row['link_category_description']   = "";
        $row['link_category_icon']          = "";
        $link_cat_resize_value              = (isset($linkspage_pref['link_cat_resize_value']) && $linkspage_pref['link_cat_resize_value'] ? $linkspage_pref['link_cat_resize_value'] : "50");

        if(isset($_POST['uploadcatlinkicon'])){
            $row['link_category_name']          = $_POST['link_category_name'];
            $row['link_category_description']   = $_POST['link_category_description'];
            $row['link_category_icon']          = $_POST['link_category_icon'];
            $link_cat_resize_value              = (isset($_POST['link_cat_resize_value']) && $_POST['link_cat_resize_value'] ? $_POST['link_cat_resize_value'] : $link_cat_resize_value);
        }
        if ($qs[1] == "edit") {
            if ($sql->db_Select("links_page_cat", "*", "link_category_id='".intval($qs[2])."' ")) {
                $row = $sql->db_Fetch();
            }
        }
        if(isset($_POST['category_clear'])){
            $row['link_category_name']          = "";
            $row['link_category_description']   = "";
            $row['link_category_icon']          = "";
        }
//        $rejectlist = array('$.','$..','/','CVS','thumbs.db','Thumbs.db','*._$', 'index', 'null*');
        $iconlist = $fl->get_files(e_PLUGIN."links_page/cat_images/");

        $text = "<div style='text-align:center'>
        ".$rs->form_open("post", e_SELF.(e_QUERY ? "?".e_QUERY : ""), "linkform", "", "enctype='multipart/form-data'", "")."
        <table class='fborder' style='".ADMIN_WIDTH."'>
        <tr>
        <td class='forumheader3' style='width:30%'>".LCLAN_CAT_13."</td>
        <td class='forumheader3' style='width:70%'>".$rs->form_text("link_category_name", 50, $row['link_category_name'], 200)."</td>
        </tr>
        <tr>
        <td class='forumheader3' style='width:30%; vertical-align:top;'>".LCLAN_CAT_14."</td>
        <td class='forumheader3' style='width:70%'>".$rs->form_text("link_category_description", 60, $row['link_category_description'], 200)."</td>
        </tr>
        <tr>
        <td style='width:30%; vertical-align:top;' class='forumheader3'>".LCLAN_CAT_15."</td>
        <td style='width:70%' class='forumheader3'>";
            if(!FILE_UPLOADS){
                $text .= "<b>".LCLAN_CAT_17."</b>";
            }else{
                if(!is_writable(e_PLUGIN."links_page/cat_images/")){
                    $text .= "<b>".LCLAN_CAT_18." ".e_PLUGIN."links_page/cat_images/ ".LCLAN_CAT_19."</b><br />";
                }
                $text .= "
                <input class='tbox' type='file' name='file_userfile[]'  size='58' /><br />
                ".LCLAN_CAT_16." ".$rs -> form_text("link_cat_resize_value", 3, $link_cat_resize_value, 3)."&nbsp;".LCLAN_CAT_20."
                ".$rs -> form_button("submit", "uploadcatlinkicon", LCLAN_CAT_21, "", "", "");
            }
        $text .= "
        </td>
        </tr>
        <tr>
        <td style='width:30%; vertical-align:top;' class='forumheader3'>".LCLAN_CAT_22."</td>
        <td style='width:70%' class='forumheader3'>
            ".$rs -> form_text("link_category_icon", 60, $row['link_category_icon'], 100)."
            ".$rs -> form_button("button", '', LCLAN_CAT_23, "onclick=\"expandit('catico')\"")."
            <div id='catico' style='{head}; display:none'>";
            foreach($iconlist as $icon){
                $text .= "<a href=\"javascript:insertext('".$icon['fname']."','link_category_icon','catico')\"><img src='".$icon['path'].$icon['fname']."' alt='' /></a> ";
            }
            $text .= "</div>
        </td>
        </tr>
        <tr>
        <td style='width:30%; vertical-align:top;' class='forumheader3'>".LCLAN_CAT_24."</td>
        <td style='width:70%' class='forumheader3'>
            ".r_userclass("link_category_class", $row['link_category_class'], "off", "public,guest,nobody,member,admin,classes")."
        </td>
        </tr>
        <tr><td colspan='2' style='text-align:center' class='forumheader'>";
        if (is_numeric($qs[2])) {
            $text .= $rs -> form_hidden("link_category_order", $row['link_category_order']);
            $text .= $rs -> form_hidden("link_category_datestamp", $row['link_category_datestamp']);
            $text .= $rs -> form_checkbox("update_datestamp", 1, 0)." ".LCLAN_CAT_25."<br /><br />";
            $text .= $rs -> form_button("submit", "update_category", LCLAN_CAT_26, "", "", "");
            $text .= $rs -> form_button("submit", "category_clear", LCLAN_CAT_27). $rs->form_hidden("link_category_id", $qs[2]);

        } else {
            $text .= $rs -> form_button("submit", "create_category", LCLAN_CAT_28, "", "", "");
        }
        $text .= "</td></tr></table>
        ".$rs->form_close()."
        </div>";

        $ns->tablerender(LCLAN_CAT_29, $text);
        unset($row['link_category_name'], $row['link_category_description'], $row['link_category_icon']);
    }

    function show_categories($mode) {
        global $sql, $rs, $ns, $tp, $fl;

        if ($category_total = $sql->db_Select("links_page_cat", "*", "ORDER BY link_category_order ASC", "mode=no_where")) {
            $text = "
            <div style='text-align: center'>
            ".$rs->form_open("post", e_SELF.(e_QUERY ? "?".e_QUERY : ""), "", "", "")."
            <table class='fborder' style='".ADMIN_WIDTH."'>
            <tr>
            <td style='width:5%' class='fcaption'>".LCLAN_CAT_1."</td>
            <td class='fcaption'>".LCLAN_CAT_2."</td>
            <td style='width:10%' class='fcaption'>".LCLAN_CAT_3."</td>";
            if($mode == "cat"){
                $text .= "
                <td class='fcaption' style='width:10%'>".LCLAN_CAT_4."</td>
                <td class='fcaption' style='width:10%'>".LCLAN_CAT_5."</td>";
            }
            $text .= "
            </tr>";
            while ($row = $sql->db_Fetch()) {
                $linkcatid = $row['link_category_id'];
                if ($row['link_category_icon']) {
                    $img = (strstr($row['link_category_icon'], "/") ? "<img src='".e_BASE.$row['link_category_icon']."' alt='' style='vertical-align:middle' />" : "<img src='".e_PLUGIN_ABS."links_page/cat_images/".$row['link_category_icon']."' alt='' style='vertical-align:middle' />");
                } else {
                    $img = "&nbsp;";
                }
                $text .= "
                <tr>
                <td style='width:5%; text-align:center' class='forumheader3'>".$img."</td>
                <td class='forumheader3'>
                    <a href='".e_PLUGIN_ABS."links_page/links.php?cat.".$linkcatid."' rel='external'>".LINK_ICON_LINK."</a>
                    ".$row['link_category_name']."<br /><span class='smalltext'>".$row['link_category_description']."</span>
                </td>";
                if($mode == "cat"){
                    if($row['link_category_order'] == "1"){
                        $up = "&nbsp;&nbsp;&nbsp;";
                    }else{
                        $up = "<input type='image' src='".LINK_ICON_ORDER_UP_BASE."' value='".$linkcatid.".".$row['link_category_order']."' name='inc' />";
                    }
                    if($row['link_category_order'] == $category_total){
                        $down = "&nbsp;&nbsp;&nbsp;";
                    }else{
                        $down = "<input type='image' src='".LINK_ICON_ORDER_DOWN_BASE."' value='".$linkcatid.".".$row['link_category_order']."' name='dec' />";
                    }
                    $text .= "
                    <td style='width:10%; text-align:center; white-space: nowrap' class='forumheader3'>
                        <a href='".e_SELF."?cat.edit.".$linkcatid."' title='".LCLAN_CAT_6."'>".LINK_ICON_EDIT."</a>
                        <input type='image' title='delete' name='delete[category_{$linkcatid}]' alt='".LCLAN_CAT_7."' src='".LINK_ICON_DELETE_BASE."' onclick=\"return jsconfirm('".$tp->toJS(LCLAN_CAT_8." [ ".$row['link_category_name']." ]")."')\"/>
                    </td>
                    <td style='width:10%; text-align:center; white-space: nowrap' class='forumheader3'>
                        ".$up."
                        ".$down."
                    </td>
                    <td style='width:10%; text-align:center' class='forumheader3'>
                        <select name='link_category_order[]' class='tbox'>";
                        for($a = 1; $a <= $category_total; $a++) {
                            $text .= $rs -> form_option($a, ($row['link_category_order'] == $a ? "1" : "0"), $linkcatid.".".$a, "");
                        }
                        $text .= $rs -> form_select_close()."
                    </td>";
                }else{
                    $text .= "<td style='width:10%; text-align:center; white-space: nowrap' class='forumheader3'>
                    <a href='".e_SELF."?link.view.".$linkcatid."' title='".LCLAN_CAT_9."'>".LINK_ICON_EDIT."</a></td>";
                }
                $text .= "
                </tr>\n";
            }
            if($mode == "cat"){
                $text .= "
                <tr>
                <td class='forumheader' colspan='4'>&nbsp;</td>
                <td class='forumheader' style='width:5%; text-align:center'>
                ".$rs->form_button("submit", "update_category_order", LCLAN_CAT_10)."
                </td>
                </tr>";
            }else{
                $text .= "
                <tr>
                <td class='forumheader' colspan='2'>&nbsp;</td>
                <td class='forumheader' style='width:5%; text-align:center'>".$rs->form_button("button", "viewalllinks", LCLAN_ITEM_37, "onclick=\"document.location='".e_SELF."?link.view.all';\"")."
                </td>
                </tr>";
            }
            $text .= "
            </table>
            ".$rs->form_close()."
            </div>";
        } else {
            $text = "<div style='text-align:center'>".LCLAN_CAT_11."</div>";
        }
        $ns->tablerender(LCLAN_CAT_12, $text);
        unset($row['link_category_name'], $row['link_category_description'], $row['link_category_icon']);
    }

    function show_submitted() {
        global $sql, $rs, $qs, $ns, $tp;

        if (!$submitted_total = $sql->db_Select("tmp", "*", "tmp_ip='submitted_link' ")) {
            $text = "<div style='text-align:center'>".LCLAN_SL_2."</div>";
        }else{
            $text = "
            ".$rs->form_open("post", e_SELF."?sn", "submitted_links")."
            <table class='fborder' style='".ADMIN_WIDTH."'>
            <tr>
            <td style='width:60%' class='fcaption'>".LCLAN_SL_3."</td>
            <td style='width:30%' class='fcaption'>".LCLAN_SL_4."</td>
            <td style='width:10%; white-space:nowrap; text-align:center' class='fcaption'>".LCLAN_SL_5."</td>
            </tr>";
            while ($row = $sql->db_Fetch()) {
                $tmp_time = $row['tmp_time'];
                $submitted = explode("^", $row['tmp_info']);
                if (!strstr($submitted[2], "http")) {
                    $submitted[2] = "http://".$submitted[2];
                }
                $text .= "<tr>
                <td style='width:60%' class='forumheader3'><a href='".$submitted[2]."' rel='external'>".$submitted[2]."</a></td>
                <td style='width:30%' class='forumheader3'>".$submitted[5]."</td>
                <td style='width:10%; white-space:nowrap; text-align:center; vertical-align:top' class='forumheader3'>
                    <a href='".e_SELF."?link.sn.".$tmp_time."' title='".LCLAN_SL_6."'>".LINK_ICON_EDIT."</a>
                    <input type='image' title='delete' name='delete[sn_{$tmp_time}]' alt='".LCLAN_SL_7."' src='".LINK_ICON_DELETE_BASE."' onclick=\"return jsconfirm('".$tp->toJS(LCLAN_SL_8." [ ".$tmp_time." ]")."')\" />
                </td>
                </tr>\n";
            }
            $text .= "</table>".$rs->form_close();
        }
        $ns->tablerender(LCLAN_SL_1, $text);
    }

    function show_pref_options() {
        global $linkspage_pref, $ns, $rs, $pref;

        $text = "
        <script type=\"text/javascript\">
        <!--
        var hideid=\"optgeneral\";
        function showhideit(showid){
            if (hideid!=showid){
                show=document.getElementById(showid).style;
                hide=document.getElementById(hideid).style;
                show.display=\"\";
                hide.display=\"none\";

                //showh=document.getElementById(showid+'help').style;
                //hideh=document.getElementById(hideid+'help').style;
                //showh.display=\"\";
                //hideh.display=\"none\";

                hideid = showid;
            }
        }
        //-->
        </script>";

        $TOPIC_ROW = "
        <tr>
            <td class='forumheader3' style='width:25%; white-space:nowrap; vertical-align:top;'>{TOPIC_TOPIC}</td>
            <td class='forumheader3' style='vertical-align:top;'>{TOPIC_FIELD}</td>
        </tr>";

        $TOPIC_TITLE_ROW = "<tr><td colspan='2' class='fcaption'>{TOPIC_CAPTION}</td></tr>";
        $TOPIC_ROW_SPACER = "<tr><td style='height:20px;' colspan='2'></td></tr>";
        $TOPIC_TABLE_END = $this->pref_submit()."</table></div>";

        $text .= "
        <div style='text-align:center'>
        ".$rs -> form_open("post", e_SELF."?".e_QUERY, "optform", "", "", "")."

        <div id='optgeneral' style='text-align:center'>
        <table style='".ADMIN_WIDTH."' class='fborder'>";

        $TOPIC_CAPTION = LCLAN_OPT_MENU_1;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_7;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_page_categories", "1", ($linkspage_pref['link_page_categories'] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_page_categories", "0", ($linkspage_pref['link_page_categories'] ? "0" : "1"), "", "").LCLAN_OPT_4;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_8;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_submit", "1", ($linkspage_pref['link_submit'] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_submit", "0", ($linkspage_pref['link_submit'] ? "0" : "1"), "", "").LCLAN_OPT_4;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_9;
        $TOPIC_FIELD = r_userclass("link_submit_class", $linkspage_pref['link_submit_class']);
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_48;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_submit_directpost", "1", ($linkspage_pref['link_submit_directpost'] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_submit_directpost", "0", ($linkspage_pref['link_submit_directpost'] ? "0" : "1"), "", "").LCLAN_OPT_4."<br />".LCLAN_OPT_49;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        //link_nextprev
        $TOPIC_TOPIC = LCLAN_OPT_10;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_nextprev", "1", ($linkspage_pref["link_nextprev"] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_nextprev", "0", ($linkspage_pref["link_nextprev"] ? "0" : "1"), "", "").LCLAN_OPT_4;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        //link_nextprev_number
        $TOPIC_TOPIC = LCLAN_OPT_11;
        $TOPIC_FIELD = $rs -> form_select_open("link_nextprev_number");
        for($i=2;$i<52;$i++){
            $TOPIC_FIELD .= $rs -> form_option($i, ($linkspage_pref["link_nextprev_number"] == $i ? "1" : "0"), $i);
            $i++;
        }
        $TOPIC_FIELD .= $rs -> form_select_close();
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        //link_comment
        $TOPIC_TOPIC = LCLAN_OPT_55;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_comment", "1", ($linkspage_pref["link_comment"] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_comment", "0", ($linkspage_pref["link_comment"] ? "0" : "1"), "", "").LCLAN_OPT_4;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_27;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_rating", "1", ($linkspage_pref['link_rating'] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_rating", "0", ($linkspage_pref['link_rating'] ? "0" : "1"), "", "").LCLAN_OPT_4;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_62;
        $TOPIC_FIELD = "<table style='width:100%;' cellpadding='0' cellspacing='0'><tr><td style='white-space:nowrap; width:20%;'>
        ".$rs -> form_checkbox("link_navigator_frontpage", 1, ($linkspage_pref['link_navigator_frontpage'] ? "1" : "0"))." ".LCLAN_OPT_60."<br />
        ".$rs -> form_checkbox("link_navigator_submit", 1, ($linkspage_pref['link_navigator_submit'] ? "1" : "0"))." ".LCLAN_OPT_58."<br />
        ".$rs -> form_checkbox("link_navigator_manager", 1, ($linkspage_pref['link_navigator_manager'] ? "1" : "0"))." ".LCLAN_OPT_59."<br />
        ".$rs -> form_checkbox("link_navigator_refer", 1, ($linkspage_pref['link_navigator_refer'] ? "1" : "0"))." ".LCLAN_OPT_20."<br />
        </td><td style='white-space:nowrap;'>
        ".$rs -> form_checkbox("link_navigator_rated", 1, ($linkspage_pref['link_navigator_rated'] ? "1" : "0"))." ".LCLAN_OPT_21."<br />
        ".$rs -> form_checkbox("link_navigator_allcat", 1, ($linkspage_pref['link_navigator_allcat'] ? "1" : "0"))." ".LCLAN_OPT_66."<br />
        ".$rs -> form_checkbox("link_navigator_links", 1, ($linkspage_pref['link_navigator_links'] ? "1" : "0"))." ".LCLAN_OPT_67."<br />
        ".$rs -> form_checkbox("link_navigator_category", 1, ($linkspage_pref['link_navigator_category'] ? "1" : "0"))." ".LCLAN_OPT_61."<br />
        </td></tr></table>";
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $text .= $TOPIC_TABLE_END;

        $text .= "
        <div id='optmanager' style='display:none; text-align:center'>
        <table style='".ADMIN_WIDTH."' class='fborder'>";

        $TOPIC_CAPTION = LCLAN_OPT_MENU_2;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_54;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_manager", "1", ($linkspage_pref['link_manager'] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_manager", "0", ($linkspage_pref['link_manager'] ? "0" : "1"), "", "").LCLAN_OPT_4;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_46;
        $TOPIC_FIELD = r_userclass("link_manager_class", $linkspage_pref['link_manager_class'])."<br />".LCLAN_OPT_47;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_48;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_directpost", "1", ($linkspage_pref['link_directpost'] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_directpost", "0", ($linkspage_pref['link_directpost'] ? "0" : "1"), "", "").LCLAN_OPT_4."<br />".LCLAN_OPT_49;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_50;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_directdelete", "1", ($linkspage_pref['link_directdelete'] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_directdelete", "0", ($linkspage_pref['link_directdelete'] ? "0" : "1"), "", "").LCLAN_OPT_4."<br />".LCLAN_OPT_51;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $text .= $TOPIC_TABLE_END;

        $text .= "
        <div id='optcategory' style='display:none; text-align:center'>
        <table style='".ADMIN_WIDTH."' class='fborder'>";

        $TOPIC_CAPTION = LCLAN_OPT_MENU_3;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_13;
        $TOPIC_FIELD = "<table style='width:100%;' cellpadding='0' cellspacing='0'><tr><td style='white-space:nowrap; width:20%;'>
        ".$rs -> form_checkbox("link_cat_icon", 1, ($linkspage_pref['link_cat_icon'] ? "1" : "0"))." ".LCLAN_OPT_14."<br />
        ".$rs -> form_checkbox("link_cat_desc", 1, ($linkspage_pref['link_cat_desc'] ? "1" : "0"))." ".LCLAN_OPT_15."<br />
        </td><td style='white-space:nowrap;'>
        ".$rs -> form_checkbox("link_cat_amount", 1, ($linkspage_pref['link_cat_amount'] ? "1" : "0"))." ".LCLAN_OPT_16."<br />
        ".$rs -> form_checkbox("link_cat_total", 1, ($linkspage_pref['link_cat_total'] ? "1" : "0"))." ".LCLAN_OPT_19."<br />
        </td></tr></table>";
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_65;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_cat_empty", "1", ($linkspage_pref['link_cat_empty'] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_cat_empty", "0", ($linkspage_pref['link_cat_empty'] ? "0" : "1"), "", "").LCLAN_OPT_4;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_22;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_cat_icon_empty", "1", ($linkspage_pref['link_cat_icon_empty'] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_cat_icon_empty", "0", ($linkspage_pref['link_cat_icon_empty'] ? "0" : "1"), "", "").LCLAN_OPT_4;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_29;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_cat_sortorder", "1", ($linkspage_pref['link_cat_sortorder'] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_cat_sortorder", "0", ($linkspage_pref['link_cat_sortorder'] ? "0" : "1"), "", "").LCLAN_OPT_4;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_23;
        $TOPIC_FIELD = "
        ".$rs -> form_select_open("link_cat_sort")."
        ".$rs -> form_option(LCLAN_OPT_40, ($linkspage_pref['link_cat_sort'] == "heading" ? "1" : "0"), "heading", "")."
        ".$rs -> form_option(LCLAN_OPT_41, ($linkspage_pref['link_cat_sort'] == "id" ? "1" : "0"), "id", "")."
        ".$rs -> form_option(LCLAN_OPT_36, ($linkspage_pref['link_cat_sort'] == "order" ? "1" : "0"), "order", "")."
        ".$rs -> form_select_close();
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_24;
        $TOPIC_FIELD = "
        ".$rs -> form_select_open("link_cat_order")."
        ".$rs -> form_option(LCLAN_OPT_30, ($linkspage_pref['link_cat_order'] == "ASC" ? "1" : "0"), "ASC", "")."
        ".$rs -> form_option(LCLAN_OPT_31, ($linkspage_pref['link_cat_order'] == "DESC" ? "1" : "0"), "DESC", "")."
        ".$rs -> form_select_close();
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_25;
        $TOPIC_FIELD = $rs -> form_text("link_cat_resize_value", "3", $linkspage_pref['link_cat_resize_value'], "3", "tbox", "", "", "")." ".LCLAN_OPT_5;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $text .= $TOPIC_TABLE_END;

        $text .= "
        <div id='optlinks' style='display:none; text-align:center'>
        <table style='".ADMIN_WIDTH."' class='fborder'>";

        $TOPIC_CAPTION = LCLAN_OPT_MENU_4;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_13;
        $TOPIC_FIELD = "<table style='width:100%;' cellpadding='0' cellspacing='0'><tr><td style='white-space:nowrap; width:20%;'>
        ".$rs -> form_checkbox("link_icon", 1, ($linkspage_pref['link_icon'] ? "1" : "0"))." ".LCLAN_OPT_14."<br />
        ".$rs -> form_checkbox("link_referal", 1, ($linkspage_pref['link_referal'] ? "1" : "0"))." ".LCLAN_OPT_17."<br />
        </td><td style='white-space:nowrap;'>
        ".$rs -> form_checkbox("link_url", 1, ($linkspage_pref['link_url'] ? "1" : "0"))." ".LCLAN_OPT_18."<br />
        ".$rs -> form_checkbox("link_desc", 1, ($linkspage_pref['link_desc'] ? "1" : "0"))." ".LCLAN_OPT_15."<br />
        </td></tr></table>";
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_28;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_icon_empty", "1", ($linkspage_pref['link_icon_empty'] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_icon_empty", "0", ($linkspage_pref['link_icon_empty'] ? "0" : "1"), "", "").LCLAN_OPT_4;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_29;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_sortorder", "1", ($linkspage_pref['link_sortorder'] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_sortorder", "0", ($linkspage_pref['link_sortorder'] ? "0" : "1"), "", "").LCLAN_OPT_4;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_23;
        $TOPIC_FIELD = "
        ".$rs -> form_select_open("link_sort")."
        ".$rs -> form_option(LCLAN_OPT_34, ($linkspage_pref['link_sort'] == "heading" ? "1" : "0"), "heading", "")."
        ".$rs -> form_option(LCLAN_OPT_35, ($linkspage_pref['link_sort'] == "url" ? "1" : "0"), "url", "")."
        ".$rs -> form_option(LCLAN_OPT_36, ($linkspage_pref['link_sort'] == "order" ? "1" : "0"), "order", "")."
        ".$rs -> form_option(LCLAN_OPT_37, ($linkspage_pref['link_sort'] == "refer" ? "1" : "0"), "refer", "")."
        ".$rs -> form_option(LCLAN_OPT_53, ($linkspage_pref['link_sort'] == "date" ? "1" : "0"), "date", "")."
        ".$rs -> form_select_close();
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_24;
        $TOPIC_FIELD = "
        ".$rs -> form_select_open("link_order")."
        ".$rs -> form_option(LCLAN_OPT_30, ($linkspage_pref['link_order'] == "ASC" ? "1" : "0"), "ASC", "")."
        ".$rs -> form_option(LCLAN_OPT_31, ($linkspage_pref['link_order'] == "DESC" ? "1" : "0"), "DESC", "")."
        ".$rs -> form_select_close();
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        //0=same window, 1=_blank, 2=_parent, 3=_top, 4=miniwindow
        $TOPIC_TOPIC = LCLAN_OPT_32;
        $TOPIC_FIELD = "
        ".$rs -> form_select_open("link_open_all")."
        ".$rs -> form_option(LCLAN_OPT_42, ($linkspage_pref['link_open_all'] == "5" ? "1" : "0"), "5", "")."
        ".$rs -> form_option(LCLAN_OPT_43, ($linkspage_pref['link_open_all'] == "0" ? "1" : "0"), "0", "")."
        ".$rs -> form_option(LCLAN_OPT_44, ($linkspage_pref['link_open_all'] == "1" ? "1" : "0"), "1", "")."
        ".$rs -> form_option(LCLAN_OPT_45, ($linkspage_pref['link_open_all'] == "4" ? "1" : "0"), "4", "")."
        ".$rs -> form_select_close();
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_33;
        $TOPIC_FIELD = $rs -> form_text("link_resize_value", "3", $linkspage_pref['link_resize_value'], "3", "tbox", "", "", "")." ".LCLAN_OPT_5;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $text .= $TOPIC_TABLE_END;

        $text .= "
        <div id='optrefer' style='display:none; text-align:center'>
        <table style='".ADMIN_WIDTH."' class='fborder'>";

        $TOPIC_CAPTION = LCLAN_OPT_MENU_5;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_56;
        $TOPIC_FIELD = $rs -> form_text("link_refer_minimum", "3", $linkspage_pref['link_refer_minimum'], "3", "tbox", "", "", "")." ".LCLAN_OPT_57;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $text .= $TOPIC_TABLE_END;

        $text .= "
        <div id='optrating' style='display:none; text-align:center'>
        <table style='".ADMIN_WIDTH."' class='fborder'>";

        $TOPIC_CAPTION = LCLAN_OPT_MENU_6;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_63;
        $TOPIC_FIELD = "";
        $TOPIC_FIELD = $rs -> form_text("link_rating_minimum", "3", $linkspage_pref['link_rating_minimum'], "3", "tbox", "", "", "")." ".LCLAN_OPT_64;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $text .= $TOPIC_TABLE_END;






        $text .= "
        <div id='optmenu' style='display:none; text-align:center'>
        <table style='".ADMIN_WIDTH."' class='fborder'>";

        $TOPIC_CAPTION = LCLAN_OPT_MENU_7;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_85;
        $TOPIC_FIELD = $rs -> form_text("link_menu_caption", "15", $linkspage_pref['link_menu_caption'], "100", "tbox", "", "", "");
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);


        $TOPIC_TOPIC = LCLAN_OPT_62;
        $TOPIC_FIELD = "<table style='width:100%;' cellpadding='0' cellspacing='0'><tr><td style='white-space:nowrap; width:20%;'>
        ".$rs -> form_checkbox("link_menu_navigator_frontpage", 1, ($linkspage_pref['link_menu_navigator_frontpage'] ? "1" : "0"))." ".LCLAN_OPT_60."<br />
        ".$rs -> form_checkbox("link_menu_navigator_submit", 1, ($linkspage_pref['link_menu_navigator_submit'] ? "1" : "0"))." ".LCLAN_OPT_58."<br />
        ".$rs -> form_checkbox("link_menu_navigator_manager", 1, ($linkspage_pref['link_menu_navigator_manager'] ? "1" : "0"))." ".LCLAN_OPT_59."<br />
        ".$rs -> form_checkbox("link_menu_navigator_refer", 1, ($linkspage_pref['link_menu_navigator_refer'] ? "1" : "0"))." ".LCLAN_OPT_20."<br />
        </td><td style='white-space:nowrap;'>
        ".$rs -> form_checkbox("link_menu_navigator_rated", 1, ($linkspage_pref['link_menu_navigator_rated'] ? "1" : "0"))." ".LCLAN_OPT_21."<br />
        ".$rs -> form_checkbox("link_menu_navigator_links", 1, ($linkspage_pref['link_menu_navigator_links'] ? "1" : "0"))." ".LCLAN_OPT_67."<br />
        ".$rs -> form_checkbox("link_menu_navigator_category", 1, ($linkspage_pref['link_menu_navigator_category'] ? "1" : "0"))." ".LCLAN_OPT_61."<br />
        </td></tr></table>";
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_79;
        $TOPIC_FIELD = $rs -> form_text("link_menu_navigator_caption", "15", $linkspage_pref['link_menu_navigator_caption'], "100", "tbox", "", "", "");
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_69;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_menu_navigator_rendertype", "1", ($linkspage_pref['link_menu_navigator_rendertype'] ? "1" : "0"), "", "").LCLAN_OPT_76."
        ".$rs -> form_radio("link_menu_navigator_rendertype", "0", ($linkspage_pref['link_menu_navigator_rendertype'] ? "0" : "1"), "", "").LCLAN_OPT_75;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);



        $TOPIC_TOPIC = LCLAN_OPT_70;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_menu_category", "1", ($linkspage_pref['link_menu_category'] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_menu_category", "0", ($linkspage_pref['link_menu_category'] ? "0" : "1"), "", "").LCLAN_OPT_4;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_80;
        $TOPIC_FIELD = $rs -> form_text("link_menu_category_caption", "15", $linkspage_pref['link_menu_category_caption'], "100", "tbox", "", "", "");
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_87;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_menu_category_amount", "1", ($linkspage_pref['link_menu_category_amount'] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_menu_category_amount", "0", ($linkspage_pref['link_menu_category_amount'] ? "0" : "1"), "", "").LCLAN_OPT_4;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_71;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_menu_category_rendertype", "1", ($linkspage_pref['link_menu_category_rendertype'] ? "1" : "0"), "", "").LCLAN_OPT_76."
        ".$rs -> form_radio("link_menu_category_rendertype", "0", ($linkspage_pref['link_menu_category_rendertype'] ? "0" : "1"), "", "").LCLAN_OPT_75;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);



        $TOPIC_TOPIC = LCLAN_OPT_72;
        $TOPIC_FIELD = "
        ".$rs -> form_radio("link_menu_recent", "1", ($linkspage_pref['link_menu_recent'] ? "1" : "0"), "", "").LCLAN_OPT_3."
        ".$rs -> form_radio("link_menu_recent", "0", ($linkspage_pref['link_menu_recent'] ? "0" : "1"), "", "").LCLAN_OPT_4;
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_73;
        $TOPIC_FIELD = "<table style='width:100%;' cellpadding='0' cellspacing='0'><tr><td style='white-space:nowrap; width:20%;'>
        ".$rs -> form_checkbox("link_menu_recent_category", 1, ($linkspage_pref['link_menu_recent_category'] ? "1" : "0"))." ".LCLAN_OPT_77."<br />
        ".$rs -> form_checkbox("link_menu_recent_description", 1, ($linkspage_pref['link_menu_recent_description'] ? "1" : "0"))." ".LCLAN_OPT_78."<br />
        </td></tr></table>";
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_81;
        $TOPIC_FIELD = $rs -> form_text("link_menu_recent_caption", "15", $linkspage_pref['link_menu_recent_caption'], "100", "tbox", "", "", "");
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

        $TOPIC_TOPIC = LCLAN_OPT_74;
        $TOPIC_FIELD = $rs -> form_select_open("link_menu_recent_number");
        for($i=1;$i<15;$i++){
            $TOPIC_FIELD .= $rs -> form_option($i, ($linkspage_pref["link_menu_recent_number"] == $i ? "1" : "0"), $i);
        }
        $TOPIC_FIELD .= $rs -> form_select_close();
        $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);



        $text .= $TOPIC_TABLE_END;


        $text .= "
        ".$rs->form_close()."
        </div>";
        $ns->tablerender(LCLAN_OPT_2, $text);
    }

    function pref_submit() 
	{
        global $rs;
        $text = "
        <tr>
        <td colspan='2' style='text-align:center' class='forumheader'>
        <input class='button' type='submit' name='updateoptions' value='".LCLAN_ADMIN_1."' />
        </td>
        </tr>";

        return $text;
    }



}

?>