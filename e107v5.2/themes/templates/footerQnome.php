
<?

        echo "
            </td><td class=\"spacer\"></td>
            <td style=\"vertical-align: top; width:15%; text-align:right\">
              <div class=\"defaulttext\" style=\"text-align:justify\">
";
$sql5 = new dbFunc;
$sql5 -> dbQuery("SELECT * FROM ".MUSER."menus WHERE menu_location='2' ORDER BY menu_order");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql5-> dbFetch()){
	if(!eregi("menu", $menu_name)){
		$menu_name();
	}else{
		require_once("menus/".$menu_name.".php");
	}
}
require_once("menus/log_menu.php");
              echo "</div>
            </td>
            <td class=\"spacer\"></td>
            <td style=\"vertical-align: top; width:15%; text-align:right\">
               
                   <div class=\"defaulttext\" style=\"text-align:justify\">";
      


$sql5 = new dbFunc;
$sql5 -> dbQuery("SELECT * FROM ".MUSER."menus WHERE menu_location='4' ORDER BY menu_order");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql5-> dbFetch()){
	if(!eregi("menu", $menu_name)){
		$menu_name();
	}else{
		require_once("menus/".$menu_name.".php");
	}
}

/*
 require_once(THEME."farrightmenu.php");
*/

echo "<br />";

             echo "</div>
                  </td><td class=\"spacer\"></td></tr></table>";

    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\"> 
            <tr>
              <td>
                </td>
                  <td>
                    <div style=\"text-align:center\">"; 
 
                                              
                       echo "</div>
                      </td>
                    <td>
                  </td>
                </tr>
              </table>

    <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
       <tr><td class=\"spacer\"></td>
        <td class=\"tbottom\" style=\"width:20%; vertical-align: bottom;\"><br /><br />";



require_once("menus/sitebutton_menu.php");
echo "</td><td class=\"spacer\"></td><td class=\"spacer\"></td>
        <td class=\"tbottom\" style=\"width:60%; vertical-align: bottom;\">";
         
   
         

$ns -> tablerender("DISCLAIMER",  "<div style=\"text-align:center\">".SITEDISCLAIMER.
"</div>");


     
               

        echo "</td><td class=\"spacer\"></td><td class=\"spacer\"></td>   
        <td class=\"tbottom\" style=\"width:20%; vertical-align: bottom;\">"; 
       require_once("menus/compliance_menu.php");
        echo "</td><td class=\"spacer\"></td>
       </tr>
      </table>";
 
    echo "</td>
        <td class=\"rightside\"><img src=\"".THEME."images/blank.gif\" height=\"1\" width=\"3\" alt=\"\" /></td>
      </tr>
      <tr>
        <td class=\"bottom\" colspan= \"3\"><img src=\"".THEME."images/blank.gif\" height=\"1\" width=\"1\" alt=\"\" />
        </td>
      </tr>
    </table>
  </body>
</html>";

 $sql -> db_Close();

?>

