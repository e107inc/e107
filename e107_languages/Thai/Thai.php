<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Thai/Thai.php,v $
|     $Revision: 1.2 $
|     $Date: 2007-03-22 00:34:28 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
แปลและพัฒนาส่วนระบบภาษาไทยโดย ผศ.ประชิด ทิณบุตร เมื่อวันที่ 18 มีนาคม 2549  แก้ไขล่าสุด 28 พย.2549
อาจารย์ประจำสาขาวิชาศิลปกรรม มหาวิทยาลัยราชภัฏจันทรเกษม ถนนรัชดาภิเษก เขตจตุจักร กทม 10900.โทร.(66) 0 2942 6900  ต่อ 3011,3014
Thai Developer & Translation : Assistant Professor Prachid Tinnabutr : Division of Art ,Chandrakasem Rajabhat University,Jatuchak,Bangkok ,Thailand.10900. Tel :(66) 02 9426900 ext:3011,3014
Last update:28 nov 2006 .
Personal Address : 144/157 Moo 1 ,Changwatana Rd.Pakkret District ,Nonthaburi Province,Thailand,11120 Tel/Fax:(66)0 2962 9505  prachid@prachid.com,prachid@wittycomputer.com ,Mobile Phone : (66) 08 9667 0091
URL : http://www.prachid.com, http://www.wittycomputer.com, http://www.e107thailand.com
*/
setlocale(LC_ALL, 'th_TH');
define("CORE_LC", 'th');
define("CORE_LC2", 'th');
// define("TEXTDIRECTION","rtl");
define("CHARSET", "UTF-8");  // for a true multi-language site. :)
define("CORE_LAN1","ผิดพลาด : ชุดรูปแบบกราฟิกหายไป.\\n\\nให้เปลี่ยนไปใช้ชุดรูปแบบอื่นโดยให้ไปเปลี่ยนที่ศูนย์กลางการจัดการระบบ(admin area) หรือส่งไฟล์เข้าไปเก็บไว้ในแฟ้ม e107_theme.ในเครื่องแม่ข่าย");

//v.616
define("CORE_LAN2"," \\1 wrote:");// "\\1" represents the username.
define("CORE_LAN3","ไม่สามารถแนบไฟล์ได้");

//v0.7+
define("CORE_LAN4", "กรุณาลบไฟล์ install.php ออกจากระบบก่อน");
define("CORE_LAN5", "ทั้งนี้เพื่อความปลอดภัยของระบบ ซึ่งอาจจะทำให้มีการติดตั้งซ้อนทับระบบได้อีก");

// v0.7.6
define("CORE_LAN6", "เปิดใช้ระบบป้องกันการ flood(การเรียกเข้าใช้ระบบหรือกระทำซ้ำๆคำสั่งเกินกำหนด ในลักษณะกลั่นแกล้ง)ของเว็ปไซท์ให้เรียบร้อยแล้ว ขอให้จำไว้ว่าหากมีการทำFloodเข้าหน้าใดๆ ระบบจะระงับการเข้าใช้ขากหมายเลขไอพีนั้นๆโดยอัตโนมัติ");
define("CORE_LAN7", "ไฟล์ระบบหลักจะทำกู้คืนไฟล์ที่ได้จากการสำรองข้อมูลอัตโนมัติจากการที่ตั้งค่าไว้.");
define("CORE_LAN8", "การสำรองไฟล์ระบบหลักที่ตั้งค่าอัตโนมัติไว้ เกิดความผิดพลาด");
define("CORE_LAN9", "ไฟล์ระบบหลักที่สำรองข้อมูลไว้อัตโนมัติ ไม่สามารถกู้คืนกลับได้ ยกเลิกปฏิบัติการ");
define("CORE_LAN10", "ตรวจพบว่า เกิดขัดข้องที่การรับค่า - ออกจากระบบ.");


define("LAN_WARNING", "คำเตือน!");
define("LAN_ERROR", "ผิดพลาด");
define("LAN_ANONYMOUS", "บุคคลทั่วไป");



?>