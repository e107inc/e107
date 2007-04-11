<?php
/*แปลและพัฒนาส่วนระบบภาษาไทยโดย ผศ.ประชิด ทิณบุตร เมื่อวันที่ 18 มีนาคม 2549  แก้ไขล่าสุด 28 มีนาคม.2550
อาจารย์ประจำสาขาวิชาศิลปกรรม มหาวิทยาลัยราชภัฏจันทรเกษม ถนนรัชดาภิเษก เขตจตุจักร กทม 10900.โทร.(66) 0 2942 6900  ต่อ 3011,3014
Thai Developer & Translation : Assistant Professor Prachid Tinnabutr : Division of Art ,Chandrakasem Rajabhat University,Jatuchak,Bangkok ,Thailand.10900. Tel :(66) 02 9426900 ext:3011,3014
Last update:28 March 2007 .
Personal Address : 144/157 Moo 1 ,Changwatana Rd.Pakkret District ,Nonthaburi Province,Thailand,11120 Tel/Fax:(66)0 2962 9505  prachid@prachid.com,prachid@wittycomputer.com ,Mobile Phone : (66) 08 9667 0091
URL : http://www.prachid.com, http://www.wittycomputer.com, http://www.e107thailand.com(Official International Sites)
*/
define("LAN_PM", "การรับ-ส่งข่าวสารส่วนตัว");
define("LAN_PM_1", "ส่งข้อความ");
define("LAN_PM_2", "ถึง");
define("LAN_PM_3", "ดูก่อน");
define("LAN_PM_4", "ระดับสมาชิก");
define("LAN_PM_5", "เรื่อง");
define("LAN_PM_6", "ข้อความ");
define("LAN_PM_7", "อารมณ์");
define("LAN_PM_8", "แนบไฟล์");
define("LAN_PM_9", "อ่านข้อความที่ได้รับ");
define("LAN_PM_10", "ส่งอีเมลแจ้งถึงเราเมื่อข้อความนี้ถูกอ่าน");
define("LAN_PM_11", "เพิ่มไฟล์ส่งขึ้นอีก");
define("LAN_PM_12", "คุณไม่ได้รับสิทธิ์ให้เข้าใช้ระบบส่งข้อความส่วนตัว");
define("LAN_PM_13", "กล่องข้อความขาออกของคุณเต็ม {PERCENT}% ,ต้องลบออกบ้างจึงจะใช้งานได้");
define("LAN_PM_14", "ผิดพลาด:เป็นไปได้ว่าการส่งสำเนา, PM ยังไม่ได้ส่งออก");
define("LAN_PM_15", "คุณไม่ได้รับอนุญาตให้ส่งถึงสมาชิกระดับนี้");
define("LAN_PM_16", "ต้องเป็นสมาชิกของระดับ");
define("LAN_PM_17", "ไม่พบชื่อผู้ใช้");
define("LAN_PM_18", "คุณไม่ได้รับอนุญาตให้ส่งข้อความถึง: ");
define("LAN_PM_19", "กล่องขาออกของคุณเต็ม, คุณจึงได่ได้รับอนุญาตให้ส่ง");
define("LAN_PM_21", "การส่งข้อความเพิ่มนี้ จะมีขนาดเกินกว่าที่ระบบกำหนดขนาดไว้สูงสุด,และส่งไม่ได้");
define("LAN_PM_22", "ไฟล์ที่ส่งขึ้นล้มเหลว");
define("LAN_PM_23", "คุณไม่ได้รับอนุญาตให้ส่งเอกสารแนบ");
define("LAN_PM_24", "ลบ PM");
define("LAN_PM_25", "กล่องข้อความเข้า");
define("LAN_PM_26", "กล่องข้อความออก");
define("LAN_PM_27", "ยังไม่ได้อ่าน");
define("LAN_PM_28", "N/A");
define("LAN_PM_29", "ข้อความที่ส่งไปแล้ว");
define("LAN_PM_30", "ข้อความที่อ่านแล้ว");
define("LAN_PM_31", "จาก");
define("LAN_PM_32", "ได้รับ");
define("LAN_PM_33", "ส่งแล้ว");
define("LAN_PM_34", "ไม่มีข้อความ");
define("LAN_PM_35", "ส่งข้อความใหม่");
define("LAN_PM_36", "ทั้งหมด");
define("LAN_PM_37", "ยังไม่อ่าน");
define("LAN_PM_38", "ส่งข้อความไปถึงสมาชิกระดับ");
define("LAN_PM_39", "การส่งข้อความล้มเหลว");
define("LAN_PM_40", "ส่งข้อความส่วนตัวถึงผู้ใช");
define("LAN_PM_41", "การส่งข้อความเข้าในกล่องขาออกของคุณล้มเหลว");
define("LAN_PM_42", "ลบข้อความออกจากกล่องข้อความขาเข้าให้แล้ว");
define("LAN_PM_43", "ลบข้อความออกจากกล่องข้อความขาออกให้แล้ว");
define("LAN_PM_44", "อนุมัติ: {UNAME} ให้สามารถส่งข้อความส่วนตัวได้แล้ว");
define("LAN_PM_45", "ผิดพลาด: เอาการห้ามออกไม่ได้, ไม่ทราบสาเหตุ");
define("LAN_PM_46", "สั่งห้ามไม่ได้กับ {UNAME}");
define("LAN_PM_47", "เพิ่มการห้าม: {UNAME}ยังไม่อนุญาตให้ส่งข้อความ ให้ยาวนานออกไปอีก");
define("LAN_PM_48", "ผิดพลาด: ไม่สามารถเพิ่มคำสั่งห้ามได้ ไม่ทราบสาเหตุ");
define("LAN_PM_49", "ผิดพลาด: เพิ่มคำสั่งห้ามใช้ ให้กับ {UNAME}เรียบร้อยแล้ว");
define("LAN_PM_50", "ห้ามผู้ใช้");
define("LAN_PM_51", "ไม่ห้ามผู้ใช้");
define("LAN_PM_52", "ลบ");
define("LAN_PM_53", "ลบที่เลือกไว้");
define("LAN_PM_54", "ข้อความเดิม");
define("LAN_PM_55", "ตอบกลับ");
define("LAN_PM_56", "คุณไม่ได้รับอนุญาตให้ตอบข้อความนี้");
define("LAN_PM_57", "ไม่พบข้อความ");
define("LAN_PM_58", "ตอบ: ");
define("LAN_PM_59", "ไปที่หน้า: ");
define("LAN_PM_60", "คุณไม่ได้รับอนุญาตให้อ่านข้อความนี้");
define("LAN_PM_61", "ไม่มีชื่อเรื่อง");
define("LAN_PM_62", "ไฟล์: [{FILENAME}] มีขนาดเกินกว่าที่กำหนดให้แบนส่ง");
define("LAN_PM_63", "class:");
define("LAN_PM_100", "มึข้อความส่วนตัวถึงผู้ใช้จาก ");
define("LAN_PM_101", "คุณได้รับข้อความจาก");
define("LAN_PM_102", "ข้อความส่งจาก: ");
define("LAN_PM_103", "เรื่อง: ");
define("LAN_PM_104", "จำนวนไฟล์ที่แนบ: ");
define("LAN_PM_105", "คุณสามารถดูข้อความส่วนตัวถึงผู้ใช้ที่: ");
define("LAN_PM_106", "ข้อความส่วนตัวถึงผู้ใช้อ่านโดย ");
define("LAN_PM_107", "ข้อความของคุณที่ส่งไปถึง {UNAME} ได้ถูกอ่านแล้ว ");
define("LAN_PM_108", "ส่งข้อความเมื่อ: ");
define("LAN_PM_109", "ข้อความใหม่");
define("LAN_PM_110", "ตกลง");
define("LAN_PM_111", "อ่าน");


?>