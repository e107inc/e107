<?php 
// **************************************************************************
// *
// *  Calendar Menu for e107 v6xx
// *
// **************************************************************************
require_once("../../class2.php");

if (!getperms("P"))
{
    header("location:" . e_BASE . "index.php");
    exit;
}

if (e_LANGUAGE !="English" && file_exists(e_PLUGIN . "calendar_menu/languages/" . e_LANGUAGE . ".php"))
{
    include_once(e_PLUGIN . "calendar_menu/languages/" . e_LANGUAGE . ".php");
} 
else
{
    include_once(e_PLUGIN . "calendar_menu/languages/admin/English.php");
} 
require_once(e_HANDLER . "userclass_class.php");
require_once(e_ADMIN . "auth.php");

// require_once(e_HANDLER . "ren_help.php");
$calendarmenu_db=new DB;
$calendarmenu_action=$_POST['calendarmenu_action'];
$calendarmenu_edit=false;
// * If we are updating then update or insert the record
if ($calendarmenu_action=='update')
{
    $calendarmenu_id=$_POST['calendarmenu_id'];
    if ($calendarmenu_id==0)
    { 
        // New record so add it
        $calendarmenu_args="
		'0',
		'" . $_POST['event_cat_name'] . "',
		'" . $_POST['ne_new_category_icon'] . "',
		'" . $_POST['event_cat_class'] . "'";
        if ($calendarmenu_db->db_Insert("event_cat", $calendarmenu_args))
        {
            $calendarmenu_msg .="<tr><td class='forumheader3' colspan='2'><strong>" . EC_ADLAN_A26 . "</strong></td></tr>";
        } 
        else
        {
            $calendarmenu_msg .="<tr><td class='forumheader3' colspan='2'><strong>" . EC_ADLAN_A27 . "</strong></td></tr>";
        } 
    } 
    else
    { 
        // Update existing
        $calendarmenu_args="
		event_cat_name='" . $_POST['event_cat_name'] . "',
		event_cat_class='" . $_POST['event_cat_class'] . "',
		event_cat_icon='" . $_POST['ne_new_category_icon'] . "'
		where event_cat_id='$calendarmenu_id'";
        if ($calendarmenu_db->db_Update("event_cat", $calendarmenu_args))
        { 
            // Changes saved
            $calendarmenu_msg .="<tr><td class='forumheader3' colspan='2'><b>" . EC_ADLAN_A28 . "</b></td></tr>";
        } 
        else
        {
            $calendarmenu_msg .="<tr><td class='forumheader3' colspan='2'><b>" . EC_ADLAN_A29 . "</b></td></tr>";
        } 
    } 
} 
// We are creating, editing or deleting a record
if ($calendarmenu_action=='dothings')
{
    $calendarmenu_id=$_POST['calendarmenu_selcat'];
    $calendarmenu_do=$_POST['calendarmenu_recdel'];
    $calendarmenu_dodel=false;

    switch ($calendarmenu_do)
    {
        case '1': // Edit existing record
            {
                // We edit the record
                $calendarmenu_db->db_Select("event_cat", "*", "event_cat_id='$calendarmenu_id'");
                $calendarmenu_row=$calendarmenu_db->db_Fetch() ;
                extract($calendarmenu_row);
                $calendarmenu_cap1=EC_ADLAN_A24;
                $calendarmenu_edit=true;
                break;
            } 
        case '2': // New category
            {
                // Create new record
                $calendarmenu_id=0; 
                // set all fields to zero/blank
                $calendar_category_name="";
                $calendar_category_description="";
                $calendarmenu_cap1=EC_ADLAN_A23;
                $calendarmenu_edit=true;
                break;
            } 
        case '3':
            { 
                // delete the record
                if ($_POST['calendarmenu_okdel']=='1')
                {
                    if ($calendarmenu_db->db_Select("event", "event_id", " where event_category='$calendarmenu_id'","nowhere"))
                    {
                        $calendarmenu_msg .="<tr><td class='forumheader3' colspan='2'><strong>" . EC_ADLAN_A59 . "</strong></td></tr>";
                    } 
                    else
                    {
                        if ($calendarmenu_db->db_Delete("event_cat", " event_cat_id='$calendarmenu_id'"))
                        {
                            $calendarmenu_msg .="<tr><td class='forumheader3' colspan='2'><strong>" . EC_ADLAN_A30 . "</strong></td></tr>";
                        } 
                        else
                        {
                            $calendarmenu_msg .="<tr><td class='forumheader3' colspan='2'><strong>" . EC_ADLAN_A32 . "</strong></td></tr>";
                        } 
                    } 
                } 
                else
                {
                    $calendarmenu_msg .="<tr><td class='forumheader3' colspan='2'><strong>" . EC_ADLAN_A31 . "</strong></td></tr>";
                } 

                $calendarmenu_dodel=true;
                $calendarmenu_edit=false;
            } 
    } 

    if (!$calendarmenu_dodel)
    {
	            require_once(e_HANDLER . "file_class.php");
            $fi = new e_file;
            $imagelist = $fi->get_files(e_PLUGIN . "calendar_menu/images", "\.\w{3}$");
        $calendarmenu_text .="
		<form id='calformupdate' method='post' action='" . e_SELF . "?cat'>
				
		<table style='width:97%;' class='fborder'>
		<tr><td colspan='2' class='fcaption'>$calendarmenu_cap1
		<input type='hidden' value='$calendarmenu_id' name='calendarmenu_id' />
		<input type='hidden' value='update' name='calendarmenu_action' /></td></tr>
		$calendarmenu_msg
		<tr><td style='width:20%;vertical-align:top;' class='forumheader3'>" . EC_ADLAN_A21 . "</td>
		<td  class='forumheader3'><input type='text' class='tbox' name='event_cat_name' value='$event_cat_name' /></td></tr>
<tr><td style='width:20%' class='forumheader3'>" . EC_ADLAN_A80 . "</td>
<td style='width:80%' class='forumheader3'>" . r_userclass("event_cat_class", $event_cat_class) . "
</td></tr>			
<tr><td class='forumheader3' style='width:20%'>" . EC_LAN_55."</td><td class='forumheader3' >";
            $calendarmenu_text .= " <input class='tbox' style='width:150px' type='text' name='ne_new_category_icon' />";
            $calendarmenu_text .= " <input class='button' type='button' style='width: 45px; cursor:hand;' value='".EC_LAN_90."' onclick='expandit(\"cat_icons\")' />";
            $calendarmenu_text .= "<div style='display:none' id='cat_icons'>";

            foreach($imagelist as $img)
            {
                if ($img['fname'])
                {
                    $calendarmenu_text .= "<a href='javascript:addtext(\"" . $img['fname'] . "\")'><img src='" . e_PLUGIN . "calendar_menu/images/" . $img['fname'] . "' style='border:0px' alt='' /></a> ";
                } 
            } 

            $calendarmenu_text .= "</div>
		<tr><td colspan='2' class='fcaption'><input type='submit' name='submits' value='" . EC_ADLAN_A25 . "' class='tbox' /></td></tr>
		</table></form>";
    } 
} 
if (!$calendarmenu_edit)
{ 
    // Get the category names to display in combo box
    // then display actions available
    $calendarmenu2_db=new DB;
    if ($calendarmenu2_db->db_Select("event_cat", "event_cat_id,event_cat_name", " order by event_cat_name", "nowhere"))
    {
        while ($calendarmenu_row=$calendarmenu2_db->db_Fetch())
        {
            extract($calendarmenu_row);
            $calendarmenu_catopt .="<option value='$event_cat_id'" .
            ($calendarmenu_id==$event_cat_id?" selected='selected'":"") . ">$event_cat_name</option>";
        } 
    } 
    else
    {
        $calendarmenu_catopt .="<option value=0'>" . EC_ADLAN_A33 . "</option>";
    } 

    $calendarmenu_text .="
	<form id='calform' method='post' action='".e_SELF."?cat'>
	
	<table width='97%' class='fborder'>
	<tr><td colspan='2' class='fcaption'>" . EC_ADLAN_A11 . "<input type='hidden' value='dothings' name='calendarmenu_action' /></td></tr>
	$calendarmenu_msg
	<tr><td style='width:20%' class='forumheader3'>" . EC_ADLAN_A11 . "</td><td  class='forumheader3'><select name='calendarmenu_selcat' class='tbox'>$calendarmenu_catopt</select></td></tr>

	<tr><td style='width:20%' class='forumheader3'>" . EC_ADLAN_A18 . "</td><td  class='forumheader3'>
	<input type='radio' name='calendarmenu_recdel' value='1' checked='checked' /> " . EC_ADLAN_A13 . "<br />
	<input type='radio' name='calendarmenu_recdel' value='2' /> " . EC_ADLAN_A14 . "<br />
	<input type='radio' name='calendarmenu_recdel' value='3' /> " . EC_ADLAN_A15 . "
	<input type='checkbox' name='calendarmenu_okdel' value='1' />" . EC_ADLAN_A16 . "</td></tr>
	<tr><td colspan='2' class='fcaption'>
	<input type='submit' name='submits' value='" . EC_ADLAN_A17 . "' class='tbox' /></td></tr>

	
	</table></form>";
} 

$ns->tablerender(EC_ADLAN_A19, $calendarmenu_text);

require_once(e_ADMIN . "footer.php");

?>