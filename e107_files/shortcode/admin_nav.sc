if(ADMIN){
	global $ns, $pref;
	$adminfpage = (!$pref['adminstyle'] || $pref['adminstyle'] == "default" ? "admin.php" : $pref['adminstyle'].".php");
	if(!strstr(e_SELF, "/".$adminfpage) || strstr(e_SELF, "/".$adminfpage."?")){
		$e107_var['x']['text']=ADLAN_52;
		$e107_var['x']['link']=e_ADMIN.$adminfpage;

		$e107_var['y']['text']=ADLAN_53;
		$e107_var['y']['link']=e_BASE."index.php";

		$text .= show_admin_menu("",time(),$e107_var)."<br />";

		require_once(e_ADMIN."header_links.php");
		$text .= get_admin_treemenu(ADLAN_93,time(),$e107_var,TRUE);
		unset($e107_var);

		// Plugin links menu

		$sql2 = new db;
		if($sql2 -> db_Select("plugin", "*", "plugin_installflag=1"))
		{
			while($row = $sql2 -> db_Fetch())
			{
				extract($row);
				include(e_PLUGIN.$plugin_path."/plugin.php");

				//Link Plugin Manager
				$e107_var['x']['text'] = "<b>".ADLAN_98."</b>";
				$e107_var['x']['link'] = e_ADMIN."plugin.php";
				$e107_var['x']['perm'] = "P";

				// Links Plugins
				if($eplug_conffile)
				{
					$e107_var['x'.$plugin_id]['text'] = $eplug_caption;
					$e107_var['x'.$plugin_id]['link'] = e_PLUGIN.$plugin_path."/".$eplug_conffile;
					$e107_var['x'.$plugin_id]['perm'] = "P".$plugin_id;
				}
				unset($eplug_conffile, $eplug_name, $eplug_caption);
			}
			$text .= get_admin_treemenu(ADLAN_95,time(),$e107_var,TRUE);
			unset($e107_var);
		}
		unset($e107_var);
		$e107_var['x']['text']=ADLAN_46;
		$e107_var['x']['link']=e_ADMIN."admin.php?logout";
		$text .= "<br />".show_admin_menu("",$act,$e107_var);
		$ns -> tablerender(LAN_head_1, $text);

	}
	else
	{
		$text = "<div style='text-align:center'>";
		unset($e107_var);
		$e107_var['x']['text']=ADLAN_53;
		$e107_var['x']['link']=e_ADMIN."../index.php";
		$text .= show_admin_menu("",$act,$e107_var);
		$text  .="</div>";
		$ns -> tablerender(LAN_head_1, $text);
		unset($text);
	}
}
