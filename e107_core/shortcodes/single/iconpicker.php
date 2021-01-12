<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 *
 * Image picker shortcode
 *
*/

function iconpicker_shortcode($parm)
{
		if(empty($parm))
		{
			return null;
		}

		$parms = array();

		parse_str($parm, $parms);
		$name = varset($parms['id']);

	
		$sql = e107::getDb();
		$frm = e107::getForm();
		$tp = e107::getParser();
		
	
		// $sc_parameters is currently being used to select the media-category.

		$qry = "SELECT * FROM `#core_media` WHERE media_userclass IN (".USERCLASS_LIST.") ";
		$qry .= vartrue($sc_parameters) ? " AND media_category = '".$sc_parameters."' " : " AND `media_category` REGEXP '_icon_16|_icon_32|_icon_48|_icon_64' ";
		$qry .= "ORDER BY media_category,media_name";

		$str = "";
		$size_section = array();
		$lastsize = "16";
		
		if($sql->gen($qry))
		{
			while($row = $sql->fetch())
			{
				list($tmp,$tmp2,$size) = explode("_",$row['media_category']);
				
								
				if($str !='' && ($size != $lastsize))
				{
					$size_section[] = $str;
					$str = "";						
				}
				
				$str .= "<a href='#".$row['media_url']."'  onclick=\"e107Helper.insertText('{$row['media_url']}','{$name}','{$name}-iconpicker'); return false; \"><img class='icon picker list%%size%%' src='".$tp->replaceConstants($row['media_url'],'abs')."' alt='{$row['media_name']}' /></a>";
								
				$lastsize = $size;
			
			}

			return '<div id="'.$name.'-iconpicker-ajax"><div class="field-spacer iconpicker">'.str_replace('%%size%%', '', implode('</div><div class="field-spacer iconpicker">', $size_section)).'</div></div>';
		}
	
	
}
