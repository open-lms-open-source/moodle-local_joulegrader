<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @copyright  Copyright (c) 2020 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Open Grader';
$string['joulegrader:grade'] = 'งานเกรดผ่าน Open Grader';
$string['joulegrader:view'] = 'ดูงานที่ได้รับเกรดผ่าน Open Grader';
$string['gradebook'] = 'สมุดเกรด';
$string['nothingtodisplay'] = 'ไม่มีข้อมูลที่จะแสดง';
$string['needsgrading'] = 'แสดงกิจกรรมที่ต้องการให้เกรด';
$string['allactivities'] = 'แสดงกิจกรรมทั้งหมด';
$string['mobilenotsupported'] = 'Open Grader ไม่รองรับเบราว์เซอร์มือถือในปัจจุบัน';
$string['exitfullscreen'] = 'ออกจากโหมดเต็มหน้าจอ';
$string['fullscreen'] = 'โหมดเต็มหน้าจอ';
$string['returncourse'] = 'กลับไปที่รายวิชา';
$string['grading'] = 'การให้เกรด';
$string['nogradeableareas'] = 'ไม่มีกิจกรรมที่ให้เกรดได้';
$string['nogradeableusers'] = 'ไม่มีผู้ใช้งานที่ให้เกรดได้';
$string['showonlyuserposts'] = 'แสดงโพสต์ของผู้ใช้งานเท่านั้น';
$string['groupbydiscussion'] = 'จัดกลุ่มตามการอภิปราย';
$string['activity'] = 'กิจกรรมที่ให้เกรดได้';
$string['activitynav'] = 'กิจกรรมที่ให้เกรดได้';
$string['activitynav_help'] = 'ใช้วิดเจ็ตนี้เพื่อเลือกและให้เกรดกิจกรรมที่สามารถให้เกรดได้';
$string['group'] = 'กลุ่ม';
$string['groupnav'] = 'กลุ่ม';
$string['groupnav_help'] = 'ใช้วิดเจ็ตนี้เพื่อเลือกกลุ่ม';
$string['user'] = 'ผู้ใช้งาน';
$string['usernav'] = 'ผู้ใช้งาน';
$string['usernav_help'] = 'ใช้วิดเจ็ตนี้เพื่อเลือกผู้ใช้งานที่จะให้เกรด';
$string['navviewlabel'] = 'ดู {$a}';
$string['commentdeleted'] = 'ผู้ใช้งาน {$a->deletedby} ลบโพสต์เมื่อ {$a->deletedon}';
$string['deletecomment'] = 'ลบความคิดเห็นที่ทำเมื่อ {$a}';
$string['previous'] = '{$a} ก่อนหน้า';
$string['next'] = '{$a} ถัดไป';
$string['assignmentavailable'] = 'ว่างอยู่';
$string['on'] = 'เมื่อ {$a}';
$string['until'] = 'จนถึง {$a}';
$string['lastedited'] = 'แก้ไขครั้งล่าสุดเมื่อ {$a}';
$string['assign23-latesubmission'] = 'การบ้านที่ส่งนี้ล่าช้า {$a}';
$string['assign23-userextensiondate'] = 'ส่วนขยายที่ได้รับจนถึง: {$a}';
$string['downloadall'] = 'ดาวน์โหลดไฟล์ทั้งหมด';
$string['download'] = 'ดาวน์โหลด';
$string['viewinline'] = 'ดูแบบอินไลน์';
$string['activitycomments'] = 'ความคิดเห็นกิจกรรม';
$string['overallfeedback'] = 'ผลตอบรับโดยรวม';
$string['filefeedback'] = 'ผลตอบรับสำหรับไฟล์';
$string['attemptnumber'] = 'ครั้ง {$a->attemptnumber}: {$a->attempttime}';
$string['viewingattempt'] = 'ความพยายามในการดู';
$string['attemptstatus'] = 'ผู้เรียนได้ทำ {$a->number} จากความพยายาม {$a->outof} ครั้ง';
$string['assignmentstatus'] = 'สถานะงานที่มอบหมาย';
$string['unlimited'] = 'ไม่จำกัด';
$string['gradebookgrade'] = 'เกรดปัจจุบันในสมุดเกรด';
$string['attemptgrade'] = 'เกรดงานที่ทำ';
$string['gradeoutof'] = 'เกรด (จาก {$a})';
$string['gradeoutofrange'] = 'เกรดอยู่นอกระยะ';
$string['overridetext'] = 'ก่อนหน้านี้ ผู้สอนสร้างเกรดสำหรับกิจกรรมนี้โดยตรงในสมุดเกรด ทำเครื่องหมายที่ช่องนี้หากคุณต้องการแทนที่เกรดนั้นด้วยเช่นกัน';
$string['save'] = 'บันทึกเกรด';
$string['saveandnext'] = 'บันทึกเกรดและถัดไป';
$string['gradingdisabled'] = 'การให้เกรดของกิจกรรมนี้ถูกล็อค หากต้องการเปิดใช้งานการให้เกรด กรุณาปลดล็อคเกรดผ่านสมุดเกรด';
$string['applytoall'] = 'ใช้เกรดและผลตอบรับกับกลุ่มทั้งหมด';
$string['applytoall_help'] = 'หากเลือก "ใช่" สมาชิกทุกคนในกลุ่มจะได้รับเกรดและผลตอบรับโดยไม่คำนึงถึงเกรดหรือผลตอบรับที่มีอยู่ในสมุดเกรด';
$string['criteria'] = 'เกณฑ์';
$string['checklist'] = 'รายการตรวจสอบ';
$string['gradesaved'] = 'อัปเดตเกรดสำเร็จแล้ว';
$string['gradesavedx'] = 'อัปเดตเกรด {$a} สำเร็จแล้ว';
$string['couldnotsave'] = 'ไม่สามารถอัปเดตเกรดได้';
$string['couldnotsavex'] = 'เกรดสำหรับ {$a} ไม่สามารถอัปเดตได้';
$string['notgraded'] = 'งานที่มอบหมายยังไม่ได้รับเกรด';
$string['viewchecklistteacher'] = 'เกรดพร้อมรายการตรวจสอบ';
$string['viewrubricteacher'] = 'เกรดพร้อมเกณฑ์การประเมินผล';
$string['viewcheckliststudent'] = 'ดูรายการตรวจสอบการให้เกรด';
$string['viewrubricstudent'] = 'ดูเกณฑ์การประเมินผลการให้เกรด';
$string['viewguidestudent'] = 'ดูคู่มือการทำเครื่องหมายการให้เกรด';
$string['viewguideteacher'] = 'เกรดพร้อมคู่มือการทำเครื่องหมาย';
$string['guide'] = 'คู่มือการทำเครื่องหมาย';
$string['rubric'] = 'เกณฑ์การประเมินผล';
$string['rubricerror'] = 'โปรดเลือกหนึ่งระดับสำหรับแต่ละเกณฑ์';
$string['guideerror'] = 'โปรดระบุเกรดที่ถูกต้องสำหรับแต่ละเกณฑ์';
$string['score'] = 'คะแนน';
$string['gradeoverriddenstudent'] = '(แทนที่ในสมุดเกรด: {$a})';
$string['close'] = 'ปิด';
$string['allfiles'] = 'ไฟล์ทั้งหมด';
$string['add'] = 'บันทึกความคิดเห็น';
$string['attachments'] = 'สิ่งที่แนบมา';
$string['commentrequired'] = 'จำเป็นต้องแสดงความคิดเห็น';
$string['commentloop'] = 'ความคิดเห็นวนซ้ำ';
$string['notreleased'] = 'เกรดงานที่มอบหมายยังไม่เผยแพร่';
$string['eventgraderviewed'] = 'ดู Open Grader แล้ว';
$string['eventactivitygraded'] = 'กิจกรรมได้รับเกรดแล้วใน Open Grader';
$string['eventcommentdeleted'] = 'ลบความคิดเห็นแล้วใน Open Grader';
$string['eventcommentadded'] = 'เพิ่มความคิดเห็นแล้วใน Open Grader';
$string['privacy:metadata:preference:fullscreen'] = 'ระบุว่าผู้ใช้งานมีผู้ให้เกรดแบบเต็มจอหรือไม่';
$string['privacy:metadata:preference:showpostsgrouped'] = 'ระบุว่าผู้ใช้งานจะจัดกลุ่มกระดานสนทนาแบบเปิดเมื่อให้คะแนนหรือไม่';
$string['privacy:request:preference:fullscreenyes'] = 'ผู้ใช้งานต้องการผู้ให้เกรดแบบเปิดแบบเต็มหน้าจอ';
$string['privacy:request:preference:fullscreenno'] = 'ผู้ใช้งานต้องการผู้ให้เกรดแบบเปิดในมุมมองปกติ';
$string['privacy:request:preference:hsupostsgroupedyes'] = 'ผู้ใช้งานต้องการให้กระดานสนทนาแบบเปิดถูกจัดกลุ่มเมื่อให้คะแนน';
$string['privacy:request:preference:hsupostsgroupedno'] = 'ผู้ใช้งานไม่ต้องการให้การเสวนาแบบเปิดถูกจัดกลุ่มเมื่อให้คะแนน';
