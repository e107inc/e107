/* $Id: menu.sc,v 1.4 2008-05-30 20:45:53 e107steved Exp $ */
global $sql;
global $ns;
global $eMenuList;
global $error_handler;
$tmp = explode(":",$parm);

$buffer_output = TRUE;				// Default - return all output.
if (isset($tmp[1]) && $tmp[1] == 'echo') $buffer_output = FALSE;
if (isset($tmp[1]) && $tmp[1] == 'ret') $buffer_output = TRUE;			// 0.7 compatible parameter

if (!array_key_exists($tmp[0],$eMenuList)) {
	return;
}

if ($buffer_output) 
{
  ob_start();
}

foreach($eMenuList[$tmp[0]] as $row)
{
	$show_menu = TRUE;
	if($row['menu_pages']) {
		list($listtype,$listpages) = explode("-",$row['menu_pages']);
		$pagelist = explode("|",$listpages);
		$check_url = e_SELF.(e_QUERY ? "?".e_QUERY : '');

		if($listtype == '1')  //show menu
		{
			$show_menu = FALSE;
			foreach($pagelist as $p)
			{
				if(substr($p, -1) == '!')
				{
					$p = substr($p, 0, -1);
					if(substr($check_url, strlen($p)*-1) == $p)
					{
						$show_menu = TRUE;
					}
				}
				else 
				{
					if(strpos($check_url,$p) !== FALSE)
					{
						$show_menu = TRUE;
					}
				}
			}
		}
		elseif($listtype == '2') //hide menu
		{
			$show_menu = TRUE;
			foreach($pagelist as $p) {
				if(substr($p, -1) == '!')
				{
					$p = substr($p, 0, -1);
					if(substr($check_url, strlen($p)*-1) == $p)
					{
						$show_menu = FALSE;
					}
				}
				else 
				{
					if(strpos($check_url, $p) !== FALSE)
					{
						$show_menu = FALSE;
					}
				}
			}
		}
	}

	if($show_menu) 
	{
		$mname = $row['menu_name'];
		if($error_handler->debug == true) 
		{
			echo "\n<!-- Menu Start: ".$mname." -->\n";
		}
		$sql->db_Mark_Time($row['menu_name']);
		if(is_numeric($row['menu_path']))
		{
			global $tp;
			$sql -> db_Select("page", "*", "page_id='".$row['menu_path']."' ");
			$page  = $sql -> db_Fetch();
			$caption = $tp -> toHTML($page['page_title'], TRUE, 'parse_sc, constants');
			$text = $tp -> toHTML($page['page_text'], TRUE, 'parse_sc, constants');
			$ns -> tablerender($caption, $text);
		}
		else
		{
			if (is_readable(e_PLUGIN.$row['menu_path']."/languages/".e_LANGUAGE.".php")) 
			{
				include_once(e_PLUGIN.$row['menu_path']."/languages/".e_LANGUAGE.".php");	
			} 
			elseif (is_readable(e_PLUGIN.$row['menu_path']."/languages/".e_LANGUAGE."/".e_LANGUAGE.".php")) 
			{
				include_once(e_PLUGIN.$row['menu_path']."/languages/".e_LANGUAGE."/".e_LANGUAGE.".php");	
			} 
			elseif (is_readable(e_PLUGIN.$row['menu_path']."/languages/English.php")) 
			{
				include_once(e_PLUGIN.$row['menu_path']."/languages/English.php");
			} 
			elseif (is_readable(e_PLUGIN.$row['menu_path']."/languages/English/English.php")) 
			{
				include_once(e_PLUGIN.$row['menu_path']."/languages/English/English.php");
			}
			
			if(file_exists(e_PLUGIN.$row['menu_path']."/".$mname.".php"))
			{
				include_once(e_PLUGIN.$row['menu_path']."/".$mname.".php");
			}
		}
		$sql->db_Mark_Time("(After ".$mname.")");
		if ($error_handler->debug == true) 
		{
		  echo "\n<!-- Menu End: ".$mname." -->\n";			
		}
	}
}

if ($buffer_output) 
{
  $ret = ob_get_contents();
  ob_end_clean();
  return $ret;
}