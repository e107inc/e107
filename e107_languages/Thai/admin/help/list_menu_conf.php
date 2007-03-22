<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ฉSteve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Thai/admin/help/list_menu_conf.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-03-22 00:34:29 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
แปลและพัฒนาส่วนระบบภาษาไทยโดย ผศ.ประชิด ทิณบุตร เมื่อวันที่ 18 มีนาคม 2549  แก้ไขล่าสุด 28 พย.2549
อาจารย์ประจำสาขาวิชาศิลปกรรม มหาวิทยาลัยราชภัฏจันทรเกษม ถนนรัชดาภิเษก เขตจตุจักร กทม 10900.โทร.(66) 0 2942 6900  ต่อ 3011,3014
Thai Developer & Translation : Assistant Professor Prachid Tinnabutr : Division of Art ,Chandrakasem Rajabhat University,Jatuchak,Bangkok ,Thailand.10900. Tel :(66) 02 9426900 ext:3011,3014
Last update:28 nov 2006 .
Personal Address : 144/157 Moo 1 ,Changwatana Rd.Pakkret District ,Nonthaburi Province,Thailand,11120 Tel/Fax:(66)0 2962 9505  prachid@prachid.com,prachid@wittycomputer.com ,Mobile Phone : (66) 08 9667 0091
URL : http://www.prachid.com, http://www.wittycomputer.com, http://www.e107thailand.com
*/

if (!defined('e107_INIT')) { exit; }

$text = "In this section you can configure 3 menus<br>
<b> New Articles Menu</b> <br>
Enter a number for example '5' in the first field to show the first 5 articles, leave empty to see all, You configure what the title of the link should be to the rest of the articles in the second field, when you leave this last option empty it won't create a link, for example: 'All articles'<br>
<b> Comments/Forum Menu</b> <br>
The number of comments default to 5, the number of characters default to 10000. The postfix is for if a line is too long it will cut it off and append this postfix to the end, a good choice for this is '...', check original topics if you want to see those in the overview.<br>

";
$ns -> tablerender("Menu Configuration Help", $text);
?>
