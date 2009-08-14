<?php
/* $Id: menu.php,v 1.3 2009-08-14 15:57:45 e107coders Exp $ */

function menu_shortcode($parm)
{
	global $sql, $ns, $tp, $sc_style;
	global $eMenuList, $error_handler;
	$e107 = e107::getInstance();

	$tmp = explode(':',$parm);

	$buffer_output = true;				// Default - return all output.
	if (isset($tmp[1]) && $tmp[1] == 'echo') { $buffer_output = false; }

	if (!array_key_exists($tmp[0], $eMenuList)) { return; }

	if ($buffer_output)
	{
		ob_start();
	}

    e107::getRender()->eMenuArea = $tmp[0];

	foreach($eMenuList[$tmp[0]] as $row)
	{
		$show_menu = TRUE;
		if($row['menu_pages'])
		{
			list($listtype, $listpages) = explode('-', $row['menu_pages'], 2);
			$pagelist = explode('|',$listpages);
			$check_url = e_SELF.(e_QUERY ? '?'.e_QUERY : '');

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
				$sql -> db_Select("page", "*", "page_id='".$row['menu_path']."' ");
				$page  = $sql -> db_Fetch();
				$caption = $e107->tp->toHTML($page['page_title'], TRUE, 'parse_sc, constants');
				$text = $e107->tp->toHTML($page['page_text'], TRUE, 'parse_sc, constants');
				$e107->ns->tablerender($caption, $text);
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

         e107::getRender()->eMenuCount = 0;
		 e107::getRender()->eMenuArea = null;

	if ($buffer_output)
	{
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}
}