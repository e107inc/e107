<?
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title><? echo SITENAME; ?></title>
    <link rel="stylesheet" href="<? echo THEME; ?>style.css" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="content-style-type" content="text/css" />
  </head>
<body>
<?

$ns = new table;    
 echo"<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
   <tr>
    <td class=\"leftside\"><img src=\"".THEME."images/blank.gif\" width=\"1\" height=\"1\" alt=\"\" /></td>
    <td style=\"width:100%; vertical-align: top;\">
       <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">
        <tr>
          <td class=\"top\" style=\"width:50%; vertical-align: center;\">
          <div style=\"text-align:left; vertical-align:bottom\">
          <img src=\"".THEME."images/blank.gif\" width=\"25\" height=\"1\" alt=\"\" /><img src=\"".THEME."images/divider.gif\" alt=\"\" /><img src=\"".THEME."images/divider.gif\" alt=\"\" />
           <b>".SITENAME." </b>
           <img src=\"".THEME."images/divider.gif\" alt=\"\" />
           ".SITETAG."
           <img src=\"".THEME."images/divider.gif\" alt=\"\" /></div>
           </td>
           <td class=\"top\" style=\"width:50%; vertical-align: center;\">";

          echo "</td>
        </tr>
        <tr>
          <td class=\"content1\" colspan=\"3\"><img src=\"".THEME."images/blank.gif\" width=\"1\" height=\"1\" alt=\"\" style=\"display: block;\"/></td>
        </tr>
        <tr>
          <td colspan=\"3\">
            <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
              <tr>
                <td class=\"logoline\"><img src=\"".THEME."images/logo.png\" alt=\"Qnome.2y.net\" style=\"display: block;\"/>
                </td>
                <td class=\"logoline\" style=\"width:100%; vertical-align: center;\">
                <img src=\"".THEME."images/blank.gif\" width=\"1\" height=\"1\" alt=\"\" style=\"display: block;\"/>
<!--
mid section -->
                </td>
                <td class=\"logoline\" style=\"width:70%; vertical-align: center;\">
                 <img src=\"".THEME."images/logor.png\" width=\"97\" height=\"97\" alt=\"\" />
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan=\"4\" class=\"content2\" style=\"width:70%;\"><span class=\"mytext3\"><img src=\"".THEME."images/blank.gif\" width=\"25\" height=\"1\" alt=\"\" /></span>";
                  
                  $quotes = file(THEME."quote.txt");
                  $quote = $quotes[rand(0, count($quotes))];
                  echo "<span class=\"mytext2\"><b>Random Quote <img src=\"".THEME."images/divider.gif\" alt=\"\" /></b></span><span class=\"mytext3\"> $quote</span></td>
       </tr>
       <tr>
          <td colspan=\"3\" class=\"content3\"></td>
       </tr>
       <tr>
          <td colspan=\"3\" class=\"content4\">
           <img src=\"".THEME."images/blank.gif\" width=\"1\" height=\"14\" alt=\"\" /></td>
       </tr>
       </table>";
       echo "
           <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">
           <tr>
            <td class=\"spacer\"></td> 
            <td style=\"vertical-align: top; width:15%; text-align:left\">
                       

<div class=\"defaulttext\">";          
if(LINKDISPLAY == 2){ sitelinks(); }
$sql9 = new db;
$sql9 -> db_Select("menus", "*",  "menu_location='1' ORDER BY menu_order");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql9-> db_Fetch()){
	require_once("menus/".$menu_name.".php");
}
require_once("menus/log_menu.php");
echo "</div>
           </td><td class=\"spacer\"></td><td class=\"line-left\"></td><td class=\"spacer\"></td>
         <td style=\"vertical-align: top; width:50%;\">";
?>                   
