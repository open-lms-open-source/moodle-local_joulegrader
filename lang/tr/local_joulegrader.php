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
 * @copyright  Copyright (c) 2024 Open LMS (https://www.openlms.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Open Grader';
$string['joulegrader:grade'] = 'Open Grader aracılığıyla çalışmaya not ver';
$string['joulegrader:view'] = 'Not verilen çalışmaya Open Grader aracılığıyla bak';
$string['gradebook'] = 'Not defteri';
$string['nothingtodisplay'] = 'Görüntülenecek Bir Şey Yok';
$string['needsgrading'] = 'Not Verilmesi Gereken Etkinlikleri Göster';
$string['allactivities'] = 'Tüm Etkinlikleri Göster';
$string['mobilenotsupported'] = 'Open Grader, şu anda mobil tarayıcıları desteklememektedir';
$string['exitfullscreen'] = 'Tam ekran modundan çık';
$string['fullscreen'] = 'Tam ekran modu';
$string['returncourse'] = 'Kursa geri dön';
$string['grading'] = 'Not Verme';
$string['nogradeableareas'] = 'Not verilebilir etkinlik yok';
$string['nogradeableusers'] = 'Not verilebilir kullanıcı yok';
$string['showonlyuserposts'] = 'Yalnızca kullanıcıların gönderilerini göster';
$string['groupbydiscussion'] = 'Tartışmaya göre gruplandır';
$string['activity'] = 'Not verilebilir etkinlik';
$string['activitynav'] = 'Not verilebilir etkinlikler';
$string['activitynav_help'] = 'Hangi not verilebilir etkinliğe not verileceğini seçmek için bu widget\'ı kullanın.';
$string['group'] = 'Grup';
$string['groupnav'] = 'Gruplar';
$string['groupnav_help'] = 'Bir grup seçmek için bu widget\'ı kullanın.';
$string['user'] = 'Kullanıcı';
$string['usernav'] = 'Kullanıcılar';
$string['usernav_help'] = 'Hangi kullanıcıya not verileceğini seçmek için bu widget\'ı kullanın.';
$string['navviewlabel'] = '{$a} öğesini görüntüle';
$string['commentdeleted'] = '{$a->deletedby} adlı kullanıcı, {$a->deletedon} tarihinde gönderiyi sildi';
$string['deletecomment'] = '{$a} tarihinde yapılan yorumu sil';
$string['previous'] = 'Önceki {$a}';
$string['next'] = 'Sonraki {$a}';
$string['assignmentavailable'] = 'Mevcut';
$string['on'] = '{$a} tarihinde';
$string['until'] = 'şuna dek: {$a}';
$string['lastedited'] = 'Son düzenleme tarihi: {$a}';
$string['assign23-latesubmission'] = 'Bu gönderim geç yapıldı: {$a}.';
$string['assign23-userextensiondate'] = 'Uzantının geçerlilik süresi: {$a}';
$string['downloadall'] = 'Tüm dosyaları indir';
$string['download'] = 'indir';
$string['viewinline'] = 'satır içi görüntüle';
$string['activitycomments'] = 'Etkinlik yorumları';
$string['overallfeedback'] = 'Genel geri bildirim';
$string['filefeedback'] = 'Dosya geri bildirimi';
$string['attemptnumber'] = 'Deneme {$a->attemptnumber}: {$a->attempttime}';
$string['viewingattempt'] = 'Görüntüleme denemesi';
$string['attemptstatus'] = 'Öğrenci, {$a->number} / {$a->outof} deneme yaptı.';
$string['assignmentstatus'] = 'Ödev durumu';
$string['unlimited'] = 'sınırsız';
$string['gradebookgrade'] = 'Not defterindeki mevcut not';
$string['attemptgrade'] = 'Deneme notu';
$string['gradeoutof'] = 'Not ({$a} içinden)';
$string['gradeoutofrange'] = 'Not aralık dışında';
$string['overridetext'] = 'Daha önce bir eğitmen, bu etkinlik için doğrudan not defterinde bir not oluşturdu. Bu notu da değiştirmek istiyorsanız bu kutuyu işaretleyin.';
$string['save'] = 'Notu kaydet';
$string['saveandnext'] = 'Notu kaydet ve bir sonraki';
$string['gradingdisabled'] = 'Bu etkinliğe not verme kilitli. Not vermeyi etkinleştirmek için Not Defteri aracılığıyla notun kilidini açın.';
$string['applytoall'] = 'Notlar uygulanıp tüm gruba bildirilsin mi?';
$string['applytoall_help'] = '"Evet" seçilirse not defterinde not veya geri bildirim olup olmadığına bakılmaksızın tüm grup üyeleri notu ve geri bildirimi alır.';
$string['criteria'] = 'Ölçüt';
$string['checklist'] = 'Kontrol listesi';
$string['gradesaved'] = 'Not başarıyla güncelleştirildi';
$string['gradesavedx'] = '{$a} not başarıyla güncelleştirildi';
$string['couldnotsave'] = 'Not güncelleştirilemedi';
$string['couldnotsavex'] = '{$a} için not güncelleştirilemedi';
$string['notgraded'] = 'Ödeve Not Verilmedi';
$string['viewchecklistteacher'] = 'Kontrol listesi ile not ver';
$string['viewrubricteacher'] = 'Dereceli puanlama anahtarı ile not ver';
$string['viewcheckliststudent'] = 'Not verme kontrol listesini görüntüle';
$string['viewrubricstudent'] = 'Dereceli puanlama anahtarı üzerinde not vermeyi görüntüle';
$string['viewguidestudent'] = 'Not verme işaretleme kılavuzunu görüntüle';
$string['viewguideteacher'] = 'İşaretleme kılavuzu ile not ver';
$string['guide'] = 'İşaretleme kılavuzu';
$string['rubric'] = 'Dereceli Puanlama Anahtarı';
$string['rubricerror'] = 'Lütfen her ölçüt için bir seviye seçin';
$string['guideerror'] = 'Lütfen her ölçüt için geçerli bir not girin';
$string['score'] = 'Puan';
$string['gradeoverriddenstudent'] = '(Not Defterinde geçersiz kıl: {$a})';
$string['close'] = 'Kapat';
$string['allfiles'] = 'Tüm dosyalar';
$string['add'] = 'Yorumu kaydet';
$string['attachments'] = 'Ekler';
$string['commentrequired'] = 'Yorum gerekli:';
$string['commentloop'] = 'Yorum Döngüsü';
$string['notreleased'] = 'Ödev notu henüz yayımlanmadı';
$string['eventgraderviewed'] = 'Open Grader\'a bakıldı';
$string['eventactivitygraded'] = 'Open Grader\'da etkinliğe not verildi';
$string['eventcommentdeleted'] = 'Open Grader\'da yorum silindi';
$string['eventcommentadded'] = 'Open Grader\'da yorum eklendi';
$string['privacy:metadata:preference:fullscreen'] = 'Kullanıcının, not veren uygulamasını tam ekran olarak görüntüleyip görüntülemediğini belirtir';
$string['privacy:metadata:preference:showpostsgrouped'] = 'Kullanıcının, not verirken Open Forumlarını gruplandırıp gruplandırmadığını belirtir';
$string['privacy:request:preference:fullscreenyes'] = 'Kullanıcı, Open Grader\'ı tam ekran olarak kullanmayı tercih ediyor';
$string['privacy:request:preference:fullscreenno'] = 'Kullanıcı, Open Grader\'ı normal görünümde kullanmayı tercih ediyor';
$string['privacy:request:preference:hsupostsgroupedyes'] = 'Kullanıcı, not verirken Open Forumlarının gruplandırılmasını tercih ediyor';
$string['privacy:request:preference:hsupostsgroupedno'] = 'Kullanıcı not verirken Open Forumlarının gruplandırılmamasını tercih ediyor';
