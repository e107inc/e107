global $sql, $link_class, $page,$tp;

$sql -> db_Select('links', '*', "link_category = 1 and link_parent =0 and link_class IN (".USERCLASS_LIST.") ORDER BY link_order ASC");
//$ulmenu = PRELINK."<ul>";					// Leaf
$ulmenu = "<ul id='navigation'>";			// Kubrick
$r='1';		// Needs to be a character - used for access key
while (($row = $sql -> db_Fetch()) && ($r <= "8"))
{
  extract($row);
  $link_url = $tp->replaceConstants($link_url,TRUE);
// Check if current page is one of the links - Test from kubrick is better
  $ltest = (e_QUERY ? e_PAGE."?".e_QUERY : e_PAGE);
  $rtest=substr(strrchr($link_url, "/"), 1);
  if (strpos($link_url, '://') === FALSE) { $link_url = e_BASE.$link_url; }
  if($ltest == $link_url || $rtest == e_PAGE){ $ulclass = '_onpage'; } else { $ulclass = ''; }

  $link_append = '';
  switch ($link_open) 
  {
    case 0:		// Open in same window
	  break;
	case 1:		// Simple open in new window
	  $link_append = " rel='external'";
	  break;
	case 4:		// 600 x 400 window
	  $link_append = " onclick=\"javascript:open_window('{$link_url}',600,400); return false;\"";
	  break;
	case 5:		// 800 x 600 window
	  $link_append = " onclick=\"javascript:open_window('{$link_url}',800,600); return false;\"";
	  break;
  }
   $lname = (defined(trim($link_name))) ? constant(trim($link_name)) : $link_name;
  $ulmenu .= "<li class='nav".$r."$ulclass'><a title='".varsettrue($link_description,'add a text description to this link')."' ";
//  if ($ulclass) $ulmenu .= " class='$ulclass' ";		// Not for kubrick
  $ulmenu .= " href='{$link_url}'".$link_append." accesskey='".$r."' >".LINKSTART.$lname."</a></li>";		// For Kubrick
//  $ulmenu .= " href='{$link_url}'".$link_append." accesskey='".$r."' >".LINKSTART."$lname".LINKEND."</a></li>";		// For leaf


  $r++;
}
//$ulmenu .= "</ul>\n".POSTLINK;		// Leaf
$ulmenu .= "</ul>";						// Kubrick
return $ulmenu;