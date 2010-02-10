<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - Site Maintenance
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/newsletter/nl_archive.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/
require_once('../../class2.php');
if (!$e107->isInstalled('newsletter') || !ADMIN) 
{
	header('Location: '.e_BASE.'index.php');
	exit(); 
}
include_lan(e_PLUGIN.'newsletter/languages/'.e_LANGUAGE.'.php');
require_once(HEADERF);

$action_parent_id	= 0;
$action_nl_id		= 0;
if(e_QUERY)
{
	$tmp = explode('.', e_QUERY);
	$action				= $tmp[0];
	$action_parent_id	= varset(intval($tmp[1], 0));
	$action_nl_id		= varset(intval($tmp[2], 0));
	unset($tmp);
}

$page_size = 10; // Might become a preference setting later on
$text .= "<div style='text-align: center; margin-left: auto; margin-right: auto; width: 100%;'>";

if (($action <> 'show' && $action <> 'showp') || ($action_parent_id == 0))
{ 	// Action 'show' displays initial page, 'showp' displays following pages
	$text .= NLLAN_68; // Invalid parameter defined
} 
else
{
	if(!isset($_POST['limit_start']))
	{
		$limit_start = 0;
	}
	else
	{
		$limit_start = $_POST['limit_start'];
	}
	$nl_count = $e107->sql->db_Count('newsletter', '(*)', "WHERE newsletter_parent='".$action_parent_id."' AND newsletter_flag='1'");
	if ($nl_count > 0)
	{
		// Retrieve parent info
		$e107->sql->db_Select('newsletter', "*", "newsletter_id='".$action_parent_id."'");
		if ($row = $e107->sql->db_Fetch()) 
		{
			$parent_newsletter_title  = $tp->toHTML($row['newsletter_title'],true);
			$parent_newsletter_text   = $tp->toHTML($row['newsletter_text'],true);
			$parent_newsletter_header = $tp->toHTML($row['newsletter_header'],true);
			$parent_newsletter_footer = $tp->toHTML($row['newsletter_footer'],true);
		}
		if ($action_nl_id == '' || $action_nl_id == 0) //Show list of sent newsletters
		{
			// Display parent name
			$text .= "{$parent_newsletter_title}<br />
					  <div style='text-align: left;'>{$parent_newsletter_text}</div><br /><br />
					  <table>";
					  
			// Display list of sent newsletters titles
			if ($action == 'showp')
			{	// This should only be done when action is 'showp'
				$limit_start = $limit_start + $page_size;
			}
			$e107->sql->db_Select('newsletter', '*', "newsletter_parent='".$action_parent_id."' AND newsletter_flag='1' ORDER BY newsletter_datestamp DESC LIMIT ".$limit_start.",".$page_size);
			while ($row = $e107->sql->db_Fetch()) 
			{
				$ga = new convert();
				$newsletter_datestamp = $ga->convert_date($row['newsletter_datestamp'], 'long');
				$text .= "<tr>
							<td>
								".$row['newsletter_issue']."
							</td>
							<td>
								<a href='".e_PLUGIN."newsletter/nl_archive.php?show.".$action_parent_id.".".$row['newsletter_id']."'>".$tp->toHTML($row['newsletter_title'],true)."</a>
							</td>
							<td>
								".$newsletter_datestamp."
							</td>
						  </tr>";
			}
			$text .= "</table>";
			if($limit_start + $page_size < $nl_count)
			{
				$text .= "<form id='nl' method='post' action='".e_PLUGIN."newsletter/nl_archive.php?showp.".$action_parent_id."'>
				<br /><input class='button' name='submit' type='submit' value='View older newsletters in archive'/>
				<input type='hidden' name='limit_start' value='".$limit_start."'/></form>";
			}
		}
		else // Show requested newsletter
		{
			$e107->sql->db_Select('newsletter', '*', "newsletter_parent='".$action_parent_id."' AND newsletter_id='".$action_nl_id."' AND newsletter_flag='1'");
			if ($row = $e107->sql->db_Fetch()) 
			{
				// Display parent header
				$text .= "$parent_newsletter_title<br />
						  <div style='text-align: left;'>$parent_newsletter_text</div><br /><br />
						  $parent_newsletter_header<br /><br />";
				// Display newsletter text
				$ga = new convert();
				$newsletter_datestamp = $ga->convert_date($row['newsletter_datestamp'], "long");		
				$text .= $newsletter_datestamp."<br />". 
						 $tp->toHTML($row['newsletter_title'],true)."<br />
						 <div style='text-align: left;'>".$tp->toHTML($row['newsletter_text'],true)."</div><br /><br />"; 
				// Display parent footer
				$text .= "$parent_newsletter_footer<br />";
				// Display back to newsletter overview button
				$text .= "<br /><a href='javascript:history.go(-1);'><input class='button' type='submit' value='".NLLAN_71."'</a>";
			}
			else
			{
				$text .= NLLAN_70; //Selected newsletter does not exist
			}
		}
	} 
	else
	{
		$text .= NLLAN_69; // No send newsletters available for selected parent
	}
}

$text .= "</div>";

$ns -> tablerender(NLLAN_67, $text);
require_once(FOOTERF);
?>