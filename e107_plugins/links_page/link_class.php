<?php
/*
+ ----------------------------------------------------------------------------+
|    e107 website system
|
|    ©Steve Dunstan 2001-2002
|    http://e107.org
|    jalist@e107.org
|
|    Released   under the   terms and   conditions of the
|    GNU    General Public  License (http://gnu.org).
|
|    $Source: /cvs_backup/e107_0.7/e107_plugins/links_page/link_class.php,v $
|    $Revision: 1.3 $
|    $Date: 2005-06-26 20:16:56 $
|    $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

class linkclass {

	function LinkPageDefaultPrefs(){

		$linkspage_pref['link_page_categories'] = "0";
		$linkspage_pref['link_submit'] = "0";
		$linkspage_pref['link_submit_class'] = "0";
		$linkspage_pref['link_open_all'] = "5";			//use individual link open type setting
		$linkspage_pref["link_nextprev"] = "1";
		$linkspage_pref["link_nextprev_number"] = "20";

		$linkspage_pref['link_cat_icon'] = "1";
		$linkspage_pref['link_cat_desc'] = "1";
		$linkspage_pref['link_cat_amount'] = "1";
		$linkspage_pref['link_cat_total'] = "1";
		$linkspage_pref['link_cat_icon_empty'] = "0";
		$linkspage_pref['link_cat_sort'] = "link_category_name";
		$linkspage_pref['link_cat_order'] = "ASC";
		$linkspage_pref['link_cat_resize_value'] = "50";

		$linkspage_pref['link_icon'] = "1";
		$linkspage_pref['link_referal'] = "1";
		$linkspage_pref['link_url'] = "0";
		$linkspage_pref['link_desc'] = "1";
		$linkspage_pref['link_rating'] = "0";
		$linkspage_pref['link_icon_empty'] = "0";
		$linkspage_pref['link_sortorder'] = "0";
		$linkspage_pref['link_sort'] = "link_order";
		$linkspage_pref['link_order'] = "ASC";
		$linkspage_pref['link_resize_value'] = "100";

		$linkspage_pref['link_pagenumber'] = "20";

		return $linkspage_pref;
	}

	function getLinksPagePref(){
		global $sql, $eArrayStorage;

		$num_rows = $sql -> db_Select("core", "*", "e107_name='links_page' ");
		if ($num_rows == 0) {
			$linkspage_pref = $this->LinkPageDefaultPrefs();
			$tmp = $eArrayStorage->WriteArray($linkspage_pref);
			$sql -> db_Insert("core", "'links_page', '{$tmp}' ");
			$sql -> db_Select("core", "*", "e107_name='links_page' ");
		}
		$row = $sql -> db_Fetch();
		$linkspage_pref = $eArrayStorage->ReadArray($row['e107_value']);

		return $linkspage_pref;
	}

	function UpdateLinksPagePref($_POST){
		global $sql, $eArrayStorage, $tp;

		$num_rows = $sql -> db_Select("core", "*", "e107_name='links_page' ");
		if ($num_rows == 0) {
			$sql -> db_Insert("core", "'links_page', '{$tmp}' ");
		}else{
			$row = $sql -> db_Fetch();

			//assign new preferences
			foreach($_POST as $k => $v){
				if(preg_match("#^link_#",$k)){
					$linkspage_pref[$k] = $tp->toDB($v, true);
				}
			}

			//create new array of preferences
			$tmp = $eArrayStorage->WriteArray($linkspage_pref);

			$sql -> db_Update("core", "e107_value = '{$tmp}' WHERE e107_name = 'links_page' ");
		}
		return $linkspage_pref;
	}


	function showLinkSort(){
		global $rs, $ns, $link_sort, $link_order;
	
		$sotext = "
		".$rs -> form_open("post", e_SELF."?".e_QUERY, "linksort")."
			".LAN_LINKS_15." 
			".$rs -> form_select_open("link_sort")."
			".$rs -> form_option(LAN_LINKS_4, ($link_sort == "link_name" ? "1" : "0"), "link_name", "")."
			".$rs -> form_option(LAN_LINKS_5, ($link_sort == "link_url" ? "1" : "0"), "link_url", "")."
			".$rs -> form_option(LAN_LINKS_6, ($link_sort == "link_order" ? "1" : "0"), "link_order", "")."
			".$rs -> form_option(LAN_LINKS_7, ($link_sort == "link_refer" ? "1" : "0"), "link_refer", "")."
			".$rs -> form_select_close()."
			".LAN_LINKS_6." 
			".$rs -> form_select_open("link_order")."
			".$rs -> form_option(LAN_LINKS_8, ($link_order == "ASC" ? "1" : "0"), "ASC", "")."
			".$rs -> form_option(LAN_LINKS_9, ($link_order == "DESC" ? "1" : "0"), "DESC", "")."
			".$rs -> form_select_close()."
			<input class='button' style='width:25px' type='submit' name='submit' value='go'>
		".$rs -> form_close();

		return $sotext;
	}

}

?>