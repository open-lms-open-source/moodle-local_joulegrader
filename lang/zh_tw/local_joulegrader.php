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
 * @copyright  Copyright (c) 2025 Open LMS (https://www.openlms.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Open Grader';
$string['joulegrader:grade'] = '透過 Open Grader 進行評分工作';
$string['joulegrader:view'] = '透過 Open Grader 檢視已評分的工作';
$string['gradebook'] = '成績單';
$string['nothingtodisplay'] = '無可顯示';
$string['needsgrading'] = '顯示需要評分的活動';
$string['allactivities'] = '顯示所有活動';
$string['mobilenotsupported'] = 'Open Grader 目前不支援行動裝置瀏覽器';
$string['exitfullscreen'] = '離開全螢幕模式';
$string['fullscreen'] = '全螢幕模式';
$string['returncourse'] = '返回課程';
$string['grading'] = '評分';
$string['nogradeableareas'] = '無可評分的活動';
$string['nogradeableusers'] = '無可評分的使用者';
$string['showonlyuserposts'] = '僅顯示使用者的文章';
$string['groupbydiscussion'] = '依討論分組';
$string['activity'] = '可評分的活動';
$string['activitynav'] = '可評分的活動';
$string['activitynav_help'] = '使用此小工具來選取所要評分的可評分活動';
$string['group'] = '群組';
$string['groupnav'] = '小組';
$string['groupnav_help'] = '使用此小工具來選取小組。';
$string['user'] = '使用者';
$string['usernav'] = '個使用者';
$string['usernav_help'] = '使用此小工具來選取所要評分的使用者。';
$string['navviewlabel'] = '檢視 {$a}';
$string['commentdeleted'] = '使用者 {$a->deletedby} 已在 {$a->deletedon} 刪除文章';
$string['deletecomment'] = '刪除在 {$a} 上所作的評語';
$string['previous'] = '上一個 {$a}';
$string['next'] = '下一個 {$a}';
$string['assignmentavailable'] = '可用';
$string['on'] = '於 {$a}';
$string['until'] = '直到 {$a}';
$string['lastedited'] = '最後編輯於 {$a}';
$string['assign23-latesubmission'] = '此送出項目遲交 {$a}。';
$string['assign23-userextensiondate'] = '延期繳交日期至：{$a}';
$string['downloadall'] = '下載所有檔案';
$string['download'] = '下載';
$string['viewinline'] = '檢視內嵌項目';
$string['activitycomments'] = '活動評語';
$string['activitycomment'] = '評語';
$string['overallfeedback'] = '整體意見回應';
$string['filefeedback'] = '檔案意見回應';
$string['attemptnumber'] = '嘗試 {$a->attemptnumber} 次：{$a->attempttime}';
$string['viewingattempt'] = '檢視嘗試';
$string['attemptstatus'] = '學員已嘗試 {$a->number} 次 (共 {$a->outof} 次)。';
$string['assignmentstatus'] = '作業狀態';
$string['unlimited'] = '不限';
$string['gradebookgrade'] = '成績單中的目前成績';
$string['attemptgrade'] = '嘗試成績';
$string['gradeoutof'] = '成績 (總分為 {$a})';
$string['gradeoutofrange'] = '成績超出範圍';
$string['overridetext'] = '先前曾有講師直接在成績單內為此活動建立成績。若您想要取代該成績，請選取此方塊。';
$string['save'] = '儲存成績';
$string['saveandnext'] = '儲存成績並繼續下一步';
$string['gradingdisabled'] = '此活動的評分已鎖定。若要啟用評分，請透過成績單將成績解除鎖定。';
$string['applytoall'] = '將成績和意見回應套用至整個小組';
$string['applytoall_help'] = '若選取「是」，則無論成績單中是否已有成績或意見回應，所有小組成員都將接收到成績和意見回應。';
$string['criteria'] = '準則';
$string['checklist'] = '檢查清單';
$string['gradesaved'] = '成績成功更新';
$string['gradesavedx'] = '{$a} 成績成功更新';
$string['couldnotsave'] = '成績無法更新';
$string['couldnotsavex'] = '{$a} 的成績無法更新';
$string['notgraded'] = '未評分的作業';
$string['viewchecklistteacher'] = '使用檢查清單評分';
$string['viewrubricteacher'] = '使用題目評分';
$string['viewcheckliststudent'] = '檢視用於評分的檢查清單';
$string['viewrubricstudent'] = '檢視用於評分的題目';
$string['viewguidestudent'] = '檢視用於評分的評閱指南';
$string['viewguideteacher'] = '使用評閱指南評分';
$string['guide'] = '評閱指南';
$string['rubric'] = '題目';
$string['rubricerror'] = '請選取各個準則的層級';
$string['guideerror'] = '請提供各個準則的有效成績';
$string['score'] = '得分';
$string['gradeoverriddenstudent'] = '(成績單中的取代成績：{$a})';
$string['close'] = '關閉';
$string['allfiles'] = '所有檔案';
$string['add'] = '儲存評語';
$string['attachments'] = '附件';
$string['commentrequired'] = '必要評語';
$string['commentloop'] = '評語迴圈';
$string['notreleased'] = '尚未發佈的作業成績';
$string['eventgraderviewed'] = '已檢視的 Open Grader';
$string['eventactivitygraded'] = 'Open Grader 中已評分的活動';
$string['eventcommentdeleted'] = 'Open Grader 中已刪除的評語';
$string['eventcommentadded'] = 'Open Grader 中已新增的評語';
$string['privacy:metadata:preference:fullscreen'] = '使用者是否以全螢幕顯示 Grader';
$string['privacy:metadata:preference:showpostsgrouped'] = '使用者對 Open 論壇評分時是否將其分組';
$string['privacy:request:preference:fullscreenyes'] = '使用者偏好以全螢幕顯示 Open Grader';
$string['privacy:request:preference:fullscreenno'] = '使用者偏好以標準檢視顯示 Open Grader';
$string['privacy:request:preference:hsupostsgroupedyes'] = '使用者偏好在對 Open 論壇評分時將其分組';
$string['privacy:request:preference:hsupostsgroupedno'] = '使用者偏好在對 Open 論壇評分時不將其分組';
