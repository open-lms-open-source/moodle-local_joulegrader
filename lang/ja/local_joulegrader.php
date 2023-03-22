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
 * @copyright  Copyright (c) 2023 Open LMS (https://www.openlms.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Open Grader';
$string['joulegrader:grade'] = 'Open Graderで作業を評定する';
$string['joulegrader:view'] = 'Open Graderで評定済みの作業を表示する';
$string['gradebook'] = '評定表';
$string['nothingtodisplay'] = '表示するものはありません';
$string['needsgrading'] = '評定に必要なアクティビティを表示する';
$string['allactivities'] = 'すべてのアクティビティを表示する';
$string['mobilenotsupported'] = '現在、Open Graderではモバイルブラウザをサポートしていません';
$string['exitfullscreen'] = 'フルスクリーンモードを終了する';
$string['fullscreen'] = 'フルスクリーンモード';
$string['returncourse'] = 'コースに戻る';
$string['grading'] = '採点';
$string['nogradeableareas'] = '評定可能なアクティビティがありません';
$string['nogradeableusers'] = '評定可能なユーザがいません';
$string['showonlyuserposts'] = 'ユーザの投稿のみを表示する';
$string['groupbydiscussion'] = 'ディスカッション別のグループ';
$string['activity'] = '評定可能なアクティビティ';
$string['activitynav'] = '評定可能なアクティビティ';
$string['activitynav_help'] = 'このウィジェットでは、評定対象の評定可能なアクティビティを選択できます。';
$string['group'] = 'グループ';
$string['groupnav'] = 'グループ';
$string['groupnav_help'] = 'このウィジェットでは、グループを選択できます。';
$string['user'] = 'ユーザ';
$string['usernav'] = 'ユーザ';
$string['usernav_help'] = 'このウィジェットでは、評定対象のユーザを選択できます。';
$string['navviewlabel'] = '{$a} を表示する';
$string['commentdeleted'] = 'ユーザ {$a->deletedby} は、{$a->deletedon} に投稿を削除しました';
$string['deletecomment'] = '{$a} に作成されたコメントを削除する';
$string['previous'] = '前の {$a}';
$string['next'] = '次の {$a}';
$string['assignmentavailable'] = '利用可能';
$string['on'] = '日時：{$a}';
$string['until'] = '-> {$a}';
$string['lastedited'] = '前回の編集日時：{$a}';
$string['assign23-latesubmission'] = '{$a} 遅く提出されました。';
$string['assign23-userextensiondate'] = '次の日時まで延長が許可されました：{$a}';
$string['downloadall'] = 'すべてのファイルをダウンロードする';
$string['download'] = 'ダウンロード';
$string['viewinline'] = 'インライン表示する';
$string['activitycomments'] = 'アクティビティのコメント';
$string['overallfeedback'] = '全体フィードバック';
$string['filefeedback'] = 'ファイルフィードバック';
$string['attemptnumber'] = '受験 {$a->attemptnumber}: {$a->attempttime}';
$string['viewingattempt'] = '受験の表示';
$string['attemptstatus'] = '学生は、{$a->outof} の受験のうち {$a->number} 回受験しました。';
$string['assignmentstatus'] = '課題のステータス';
$string['unlimited'] = '無制限';
$string['gradebookgrade'] = '評定表内の現在の評定';
$string['attemptgrade'] = '答案成績';
$string['gradeoutof'] = '評点 ({$a} 点中)';
$string['gradeoutofrange'] = '評点が範囲外です';
$string['overridetext'] = '従来、講師は、このアクティビティの評定を評定表で直接作成していました。この評定を置換する場合は、このチェックボックスもオンにしてください。';
$string['save'] = '評点を保存する';
$string['saveandnext'] = '評点と次を保存する';
$string['gradingdisabled'] = 'このアクティビティの評定はロックされています。評定を有効にするには、評定表で評定のロックを解除してください。';
$string['applytoall'] = 'グループすべてに評定およびフィードバックを提出する';
$string['applytoall_help'] = '[はい]を選択すると、評定表の既存の評定またはフィードバックに関係なく、すべてのグループメンバーに評定とフィードバックが送信されます。';
$string['criteria'] = 'クライテリア';
$string['checklist'] = 'チェックリスト';
$string['gradesaved'] = '評定が正常に更新されました';
$string['gradesavedx'] = '{$a} 個の評定が正常に更新されました';
$string['couldnotsave'] = '評定を更新できませんでした';
$string['couldnotsavex'] = '{$a} の評定を更新できませんでした';
$string['notgraded'] = '課題は評定されていません';
$string['viewchecklistteacher'] = 'チェックリストで評定する';
$string['viewrubricteacher'] = 'ルーブリックで評定する';
$string['viewcheckliststudent'] = '評定チェックリストを表示する';
$string['viewrubricstudent'] = '評定ルーブリックを表示する';
$string['viewguidestudent'] = '評定の評定ガイドを表示する';
$string['viewguideteacher'] = '評定ガイドで評定する';
$string['guide'] = '評定ガイド';
$string['rubric'] = 'ルーブリック';
$string['rubricerror'] = 'クライテリアごとにレベルを1つ選択してください';
$string['guideerror'] = 'クライテリアごとに有効な評点を入力してください';
$string['score'] = 'スコア';
$string['gradeoverriddenstudent'] = '(評点表のオーバーライド：{$a})';
$string['close'] = 'クローズ';
$string['allfiles'] = 'すべてのファイル';
$string['add'] = 'コメントを保存する';
$string['attachments'] = '添付ファイル';
$string['commentrequired'] = 'コメントが必要です';
$string['commentloop'] = 'コメントループ';
$string['notreleased'] = '課題の評定がリリースされていません';
$string['eventgraderviewed'] = 'Open Graderが表示されました';
$string['eventactivitygraded'] = 'Open Graderでアクティビティが評定されました';
$string['eventcommentdeleted'] = 'Open Graderでコメントが削除されました';
$string['eventcommentadded'] = 'Open Graderでコメントが追加されました';
$string['privacy:metadata:preference:fullscreen'] = 'ユーザがGraderを全画面で表示するかどうかを指定します';
$string['privacy:metadata:preference:showpostsgrouped'] = '評定時にユーザがOpenフォーラムをグループ化するかどうかを指定します';
$string['privacy:request:preference:fullscreenyes'] = 'ユーザはOpen Graderを全画面で表示することを優先します';
$string['privacy:request:preference:fullscreenno'] = 'ユーザは標準表示のOpen Graderを優先します';
$string['privacy:request:preference:hsupostsgroupedyes'] = 'ユーザは評定時にOpenフォーラムをグループ化することを優先します';
$string['privacy:request:preference:hsupostsgroupedno'] = 'ユーザは評定時にOpenフォーラムをグループ化しないことを優先します';
