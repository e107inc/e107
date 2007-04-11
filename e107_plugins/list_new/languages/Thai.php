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
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/list_new/languages/Thai.php,v $
|     $Revision: 1.2 $
|     $Date: 2007-04-11 05:41:12 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
/*แปลและพัฒนาส่วนระบบภาษาไทยโดย ผศ.ประชิด ทิณบุตร เมื่อวันที่ 18 มีนาคม 2549  แก้ไขล่าสุด 28 มีนาคม.2550
อาจารย์ประจำสาขาวิชาศิลปกรรม มหาวิทยาลัยราชภัฏจันทรเกษม ถนนรัชดาภิเษก เขตจตุจักร กทม 10900.โทร.(66) 0 2942 6900  ต่อ 3011,3014
Thai Developer & Translation : Assistant Professor Prachid Tinnabutr : Division of Art ,Chandrakasem Rajabhat University,Jatuchak,Bangkok ,Thailand.10900. Tel :(66) 02 9426900 ext:3011,3014
Last update:28 March 2007 .
Personal Address : 144/157 Moo 1 ,Changwatana Rd.Pakkret District ,Nonthaburi Province,Thailand,11120 Tel/Fax:(66)0 2962 9505  prachid@prachid.com,prachid@wittycomputer.com ,Mobile Phone : (66) 08 9667 0091
URL : http://www.prachid.com, http://www.wittycomputer.com, http://www.e107thailand.com(Official International Sites)
*/

if (!defined("PAGE_NAME")) { define("PAGE_NAME", "รายการใหม่"); }
define("LIST_PLUGIN_1", "รายการใหม่");
define("LIST_PLUGIN_2", "โปรแกรมเสริมนี้ เป็นคำสั่งแสดงรายการส่วนต่างๆของระบบที่มีใหม่และในปัจจุบัน เป็น การรายงานสารบบของเว็ปไซท์ แสดงและรวบรวมกิจกรรมที่มีเข้ามาทั้งหมดภายในเว็ปไซท์ของคุณ สามารถจำแนกประเภทให้เห็นเป็นภาพรวมของกิจกรรมในหมวดหมูต่างๆ ที่เพิ่มเข้ามาใหม่และที่มีอยู่ปัจจุบัน เหมือนเป็นรายงานกิจกรรมทั้งหมดให้เห็น หรือเชื่อมโยงไปดู/ตรวจสอบ/แก้ไขตามที่ต้องการได้.");
define("LIST_PLUGIN_3", "การตั้งค่าเมนูหลัก");
define("LIST_PLUGIN_4", "โปรแกรมเสริม รายการใหม่ของเว็ปไซท์ พร้อมใช้งานแล้ว.");
define("LIST_PLUGIN_5", "รายการใหม่");
define("LIST_PLUGIN_6", "ยังไม่ได้ติดตั้งโปรแกรม.");
define("LIST_ADMIN_1", "ปัจจุบัน");
define("LIST_ADMIN_2", "ปรับปรุงการคั้งค่า");
define("LIST_ADMIN_3", "ปรับปรุงการคั้งค่าแล้ว");
define("LIST_ADMIN_4", "ส่วนของ");
define("LIST_ADMIN_5", "เมนู");
define("LIST_ADMIN_6", "หน้า");
define("LIST_ADMIN_7", "ให้ใช้งานได้");
define("LIST_ADMIN_8", "ยังไม่ใช้งาน");
define("LIST_ADMIN_9", "เปิด");
define("LIST_ADMIN_10", "ปิดแล้ว");
define("LIST_ADMIN_11", "ปรับปรุง");
define("LIST_ADMIN_12", "เลือก");
define("LIST_ADMIN_13", " ขอต้อนรับสู่หน้าแสดงลำดับรายการปัจจุบันของเว็ปไซท์ ".SITENAME." หน้านี้เป็นการรายงานกิจกรรมโดยภาพรวมของแต่ละส่วน ที่เปิดให้เข้าใช้งานภายในเว็ป โดยแสดงข้อมูลรายการที่มีอยู่และเพิ่มเข้ามาในแต่ละส่วน.");
define("LIST_ADMIN_14", "รายการใหม่ ที่เพิ่มเข้าระบบในปัจจุบัน");
define("LIST_ADMIN_15", "รายการที่มี นับแต่ที่คุณเคยเข้ามาครั้งล่าสุด");
define("LIST_ADMIN_16", "ขอต้อนรับสู่หน้าแสดงลำดับรายการปัจจุบันของเว็ปไซท์".SITENAME." ! หน้านี้เป็นการแสดงรายงานกิจกรรมโดยภาพรวมของแต่ละส่วน ที่เปิดให้เข้าใช้งานภายในเว็ป โดยแสดงข้อมูลรายการที่มีอยู่และเพิ่มเข้ามาในแต่ละส่วน นับแต่ที่คุณเคยเข้ามาครั้งล่าสุด");
define("LIST_ADMIN_SECT_1", "ส่วนรายการที่มีอยู่");
define("LIST_ADMIN_SECT_2", "คลิกเลือกส่วนรายการใดบ้าง ที่จะให้แสดงรายการที่มีอยู่ปัจจุบันและที่เข้ามาใหม่");
define("LIST_ADMIN_SECT_3", "");
define("LIST_ADMIN_SECT_4", "ส่วนรายการที่จะให้แสดงเป็นค่าเริ่มต้น");
define("LIST_ADMIN_SECT_5", "คลิกเลือกว่าจะให้ส่วนรายการใด เป็นส่วนที่จะแสดงเป็นค่าเริ่มต้นบ้าง");
define("LIST_ADMIN_SECT_6", "");
define("LIST_ADMIN_SECT_7", "ส่วนรายการที่จะให้แสดงชื่อผู้เขียน");
define("LIST_ADMIN_SECT_8", "คลิกเลือกส่วนรายการใดบ้าง ที่จะให้แสดงชื่อผู้เขียน");
define("LIST_ADMIN_SECT_9", "");
define("LIST_ADMIN_SECT_10", "หมวดหมู่");
define("LIST_ADMIN_SECT_11", "คลิกเลือกประเภทรายการใด ที่จะให้แสดงที่เพิ่มเข้ามาใหม่และที่มีอยู่ปัจจุบัน");
define("LIST_ADMIN_SECT_12", "");
define("LIST_ADMIN_SECT_13", "การใช้วันเวลา");
define("LIST_ADMIN_SECT_14", "คลิกเลือกส่วนของรายการที่จะให้แสดงวันเวลา");
define("LIST_ADMIN_SECT_15", "");
define("LIST_ADMIN_SECT_16", "จำนวนรายการแสดงต่อหน้า");
define("LIST_ADMIN_SECT_17", "คลิกเลือกจำนวน ที่จะให้แสดงรายการในแต่ละส่วนรายการ ต่อหน้า ");
define("LIST_ADMIN_SECT_18", "");
define("LIST_ADMIN_SECT_19", "จัดเรียงลำดับรายการ");
define("LIST_ADMIN_SECT_20", "คลิกเลือกจัดเรียงลำดับรายการที่จะแสดง");
define("LIST_ADMIN_SECT_21", "");
define("LIST_ADMIN_SECT_22", "สัญรูป");
define("LIST_ADMIN_SECT_23", "คลิกเลือกส่วนรายการที่จะให้แสดงสัญรูป");
define("LIST_ADMIN_SECT_24", "");
define("LIST_ADMIN_SECT_25", "คำที่ใช้เป็นหัวข้อรายการ");
define("LIST_ADMIN_SECT_26", "คำแปลหรือนิยามคำเป็นภาษาไทยในแต่ละส่วน");
define("LIST_ADMIN_SECT_27", "");
define("LIST_ADMIN_OPT_1", "ทั่วไป");
define("LIST_ADMIN_OPT_2", "หน้าปัจจุบัน");
define("LIST_ADMIN_OPT_3", "เมนูปัจจุบัน");
define("LIST_ADMIN_OPT_4", "หน้าใหม่");
define("LIST_ADMIN_OPT_5", "เมนูใหม่");
define("LIST_ADMIN_OPT_6", "เลือกค่า");
define("LIST_ADMIN_MENU_2", "การใช้สัญลักษณ์ข้อย่อย");
define("LIST_ADMIN_MENU_3", "คลิกเลือกเปิด-ปิดการใช้งานสัญลักษณ์นำหน้าหัวข้อย่อย");
define("LIST_ADMIN_MENU_4", "");
define("LIST_ADMIN_LAN_2", "คำอธิบาย");
define("LIST_ADMIN_LAN_3", "นิยามคำอธิบาย");
define("LIST_ADMIN_LAN_4", "");
define("LIST_ADMIN_LAN_5", "การใช้สัญรูป");
define("LIST_ADMIN_LAN_6", "คลิกเลือกเปิด-ปิดการใช้สัญรูป");
define("LIST_ADMIN_LAN_7", "");
define("LIST_ADMIN_LAN_8", "จำนวนตัวอักษรพาดหัวเรื่อง");
define("LIST_ADMIN_LAN_9", "เลือกจำนวนตัวอักษรที่จะแสดงเป็นพาดหัวเรื่อง");
define("LIST_ADMIN_LAN_10", "ปล่อยว่างไว้หากจะใช้คำเต็ม");
define("LIST_ADMIN_LAN_11", "จำกัดคำ");
define("LIST_ADMIN_LAN_12", "เลือกจำกัดคำที่จะกำหนดให้เป็นจำนวนตัวอักษรที่จะพาดเป็นหัวเรื่องนั้นต้องมีไม่เกินเท่าใด");
define("LIST_ADMIN_LAN_13", "ปล่อยว่างไว้หากไม่ต้องการแสดง");
define("LIST_ADMIN_LAN_14", "วันที่");
define("LIST_ADMIN_LAN_15", "เลือกการแสดงรูปแบบวันเวลา");
define("LIST_ADMIN_LAN_16", "ต้องการทราบรูปแบบคำสั่งอื่นๆให้ดูได้ที่  <a href='http://www.php.net/manual/en/function.strftime.php' rel='external'>strftime function page at php.net</a>");
define("LIST_ADMIN_LAN_17", "วันนี้วันที่");
define("LIST_ADMIN_LAN_18", "เลือกรูปแบบของวัน หากแสดงเป็นคำนำหน้าของวันนี้");
define("LIST_ADMIN_LAN_19", "ต้องการทราบรูปแบบคำสั่งอื่นๆให้ดูได้ที่ <a href='http://www.php.net/manual/en/function.strftime.php' rel='external'>strftime function page at php.net</a>");
define("LIST_ADMIN_LAN_20", "คอลัมน์");
define("LIST_ADMIN_LAN_21", "เลือกจำนวนคอลัมน์");
define("LIST_ADMIN_LAN_22", "เลือกว่าจะให้แสดงแถวข้อความเป็นกี่คอลัมน์ ในหนึ่งหน้าจอ");
define("LIST_ADMIN_LAN_23", "ข้อความต้อนรับ");
define("LIST_ADMIN_LAN_24", "พิมพ์คำต้อนรับที่จะแสดงในส่วนบนของหน้าเอกสาร");
define("LIST_ADMIN_LAN_25", "");
define("LIST_ADMIN_LAN_26", "ข้อความที่จะแสดงในส่วนรายการที่ไม่มีรายการเข้า");
define("LIST_ADMIN_LAN_27", "คำที่จะบอกกล่าวว่า ในส่วนรายการนี้ยังไม่มีรายการเข้ามาใหม่หรือเป็นหน้าว่าง");
define("LIST_ADMIN_LAN_28", "");
define("LIST_ADMIN_LAN_29", "สัญลักษณ์แทน");
define("LIST_ADMIN_LAN_30", "คลิกเลือกเปิด-ปิดการใช้งานชุดสัญลักษณ์นำหัวข้อย่อย ในกรณีที่ยังไม่เปิดใช้งานหรือไม่มีสัญลักษณ์แทนใช้");
define("LIST_ADMIN_LAN_31", "");
define("LIST_ADMIN_LAN_32", "ดูย้อนหลังได้สูงสุด");
define("LIST_ADMIN_LAN_33", "สามารถดูเนื้อหาย้อนหลังได้จำนวนมากที่สุดกี่วัน");
define("LIST_ADMIN_LAN_34", "");
define("LIST_ADMIN_LAN_35", "วัน");
define("LIST_ADMIN_LAN_36", "จำนวนวันย้อนหลัง");
define("LIST_ADMIN_LAN_37", "แสดงกล่องรายการเลขวันที่จะดูย้อนหลัง?");
define("LIST_ADMIN_LAN_38", "");
define("LIST_ADMIN_LAN_39", "เปิดหากมีการบันทึก ");
define("LIST_ADMIN_LAN_40", "ให้ส่วนที่บันทึก เปิดใช้งานได้เป็นค่าเริ่มต้นหรือไม่?");
define("LIST_ADMIN_LAN_41", "");
define("LIST_MENU_1", "รายการที่มีอยู่ปัจจุบัน");
define("LIST_MENU_2", "โดย");
define("LIST_MENU_3", "เมื่อ");
define("LIST_MENU_4", "ใน");
define("LIST_MENU_5", "วัน");
define("LIST_MENU_6", "ดูรายการเนื้อหาภายในกี่วัน?");
define("LIST_MENU_7", "");
define("LIST_MENU_8", "");
define("LIST_MENU_9", "");
define("LIST_MENU_10", "");
define("LIST_MENU_11", "");
define("LIST_MENU_12", "");
define("LIST_MENU_13", "");
define("LIST_MENU_14", "");
define("LIST_MENU_15", "");
define("LIST_MENU_16", "");
define("LIST_MENU_17", "");
define("LIST_MENU_18", "");
define("LIST_MENU_19", "");
define("LIST_NEWS_1", "ข่าว:เนื้อหา");
define("LIST_NEWS_2", "ยังไม่มีรายการข่าว");
define("LIST_COMMENT_1", "ความเห็น");
define("LIST_COMMENT_2", "ยังไม่มีความเห็น");
define("LIST_COMMENT_3", "ข่าว:เนื้อหา");
define("LIST_COMMENT_4", "faq");
define("LIST_COMMENT_5", "แบบสอบถาม");
define("LIST_COMMENT_6", "docs");
define("LIST_COMMENT_7", "bugtrack");
define("LIST_COMMENT_8", "เนื้อหา");
define("LIST_COMMENT_9", "การโอนไฟล์ลง");
define("LIST_COMMENT_10", "ความคิด");
define("LIST_DOWNLOAD_1", "การโอนไฟล์ลง");
define("LIST_DOWNLOAD_2", "ไม่มีการโอนไฟล์ลง");
define("LIST_MEMBER_1", "สมาชิก");
define("LIST_MEMBER_2", "ยังไม่มีสมาชิก");
define("LIST_CONTENT_1", "เนื้อหา");
define("LIST_CONTENT_2", "ยังไม่มีเนื้อหาใน");
define("LIST_CONTENT_3", "ยังไม่มีการจัดประเภทเนื้อหา");
define("LIST_CHATBOX_1", "กล่องสนทนา:ฝากข้อความ");
define("LIST_CHATBOX_2", "ไม่มีข้อความเข้า");
define("LIST_CALENDAR_1", "ปฏิทินกิจกรรม");
define("LIST_CALENDAR_2", "ยังไม่มีปฏิทินกิจกรรม");
define("LIST_LINKS_1", "การเชื่อมโยง");
define("LIST_LINKS_2", "ยังไม่มีรายการเชื่อมโยง");
define("LIST_FORUM_1", "การอภิปราย");
define("LIST_FORUM_2", "ยังไม่มีกระทู้อภิปราย");
define("LIST_FORUM_3", "ดู:");
define("LIST_FORUM_4", "ตอบ:");
define("LIST_FORUM_5", "กระทู้ล่าสุด:");
define("LIST_FORUM_6", "เมื่อ:");


?>