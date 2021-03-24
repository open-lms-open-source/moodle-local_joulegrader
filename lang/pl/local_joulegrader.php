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
 * @copyright  Copyright (c) 2021 Open LMS (https://www.openlms.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Open Grader';
$string['joulegrader:grade'] = 'Oceniaj prace za pomocą modułu Open Grader';
$string['joulegrader:view'] = 'Wyświetl prace ocenione za pomocą modułu Open Grader';
$string['gradebook'] = 'Dziennik ocen';
$string['nothingtodisplay'] = 'Nic do wyświetlenia';
$string['needsgrading'] = 'Pokaż aktywności wymagające oceny';
$string['allactivities'] = 'Pokaż wszystkie aktywności';
$string['mobilenotsupported'] = 'Moduł Open Grader nie obsługuje obecnie mobilnych przeglądarek';
$string['exitfullscreen'] = 'Zamknij tryb pełnoekranowy';
$string['fullscreen'] = 'Tryb pełnoekranowy';
$string['returncourse'] = 'Powrót do kursu';
$string['grading'] = 'Ocenianie';
$string['nogradeableareas'] = 'Brak aktywności do oceny';
$string['nogradeableusers'] = 'Brak użytkowników do oceny';
$string['showonlyuserposts'] = 'Pokaż tylko wpisy użytkowników';
$string['groupbydiscussion'] = 'Grupuj wg dyskusji';
$string['activity'] = 'Aktywność do oceny';
$string['activitynav'] = 'Aktywności do oceny';
$string['activitynav_help'] = 'Ten widget pozwala wybrać, która z aktywności do oceny będzie oceniana.';
$string['group'] = 'Grupa';
$string['groupnav'] = 'Grupy';
$string['groupnav_help'] = 'Ten widget służy do wyboru grupy.';
$string['user'] = 'Użytkownik';
$string['usernav'] = 'Użytkownicy';
$string['usernav_help'] = 'Ten widget służy do wyboru użytkownika do oceny.';
$string['navviewlabel'] = 'Wyświetl {$a}';
$string['commentdeleted'] = 'Użytkownik {$a->deletedby} usunął wpis {$a->deletedon}';
$string['deletecomment'] = 'Usuń komentarz z dnia {$a}';
$string['previous'] = 'Poprzedni {$a}';
$string['next'] = 'Następny {$a}';
$string['assignmentavailable'] = 'Dostępni';
$string['on'] = 'dnia {$a}';
$string['until'] = 'do {$a}';
$string['lastedited'] = 'Ostatnia edycja dnia {$a}';
$string['assign23-latesubmission'] = 'Praca złożona z opóźnieniem o {$a}.';
$string['assign23-userextensiondate'] = 'Przedłużono termin oddania do: {$a}';
$string['downloadall'] = 'Pobierz wszystkie pliki';
$string['download'] = 'pobierz';
$string['viewinline'] = 'wyświetl w trybie inline';
$string['activitycomments'] = 'Komentarze na temat aktywności';
$string['overallfeedback'] = 'Ogólna informacja zwrotna';
$string['filefeedback'] = 'Plik z komentarzem zwrotnym';
$string['attemptnumber'] = 'Próba {$a->attemptnumber}: {$a->attempttime}';
$string['viewingattempt'] = 'Wyświetlenie próby';
$string['attemptstatus'] = 'Student podjął {$a->number} z {$a->outof} prób.';
$string['assignmentstatus'] = 'Status zadania';
$string['unlimited'] = 'nieograniczone';
$string['gradebookgrade'] = 'Bieżąca ocena w dzienniku ocen';
$string['attemptgrade'] = 'Ocena z podejścia';
$string['gradeoutof'] = 'Ocena (z maks. {$a})';
$string['gradeoutofrange'] = 'Ocena poza zakresem';
$string['overridetext'] = 'Poprzednio instruktor utworzył ocenę dla tej aktywności bezpośrednio w dzienniku ocen. Zaznacz to pole, ab zastąpić również tamtą ocenę.';
$string['save'] = 'Zapisz ocenę';
$string['saveandnext'] = 'Zapisz ocenę i przejdź dalej';
$string['gradingdisabled'] = 'Ocenianie tej aktywności jest zablokowane. Aby włączyć ocenianie, odblokuj ocenę z poziomu dziennika ocen.';
$string['applytoall'] = 'Zastosuj oceny i informacje zwrotne dla całej grupy';
$string['applytoall_help'] = 'W przypadku wybrania opcji „Tak” wszyscy członkowie grupy otrzymają ocenę z informacją zwrotną bez względu na istniejącą ocenę lub informację zwrotną w dzienniku ocen.';
$string['criteria'] = 'Kryteria';
$string['checklist'] = 'Lista kryteriów';
$string['gradesaved'] = 'Ocena pomyślnie zaktualizowana';
$string['gradesavedx'] = 'Liczba pomyślnie zaktualizowanych ocen: {$a}';
$string['couldnotsave'] = 'Nie można zaktualizować oceny';
$string['couldnotsavex'] = 'Nie można zaktualizować oceny dla {$a}';
$string['notgraded'] = 'Zadanie nieocenione';
$string['viewchecklistteacher'] = 'Ocena z formularzem kryteriów';
$string['viewrubricteacher'] = 'Ocena z rubryką';
$string['viewcheckliststudent'] = 'Wyświetl formularz kryteriów oceniania';
$string['viewrubricstudent'] = 'Wyświetl rubrykę oceniania';
$string['viewguidestudent'] = 'Wyświetl podręcznik oceniania';
$string['viewguideteacher'] = 'Ocena z podręcznikiem oceniania';
$string['guide'] = 'Podręcznik oceniania';
$string['rubric'] = 'Formularz kryteriów';
$string['rubricerror'] = 'Wybierz jeden poziom dla każdego z kryteriów';
$string['guideerror'] = 'Podaj prawidłową ocenę dla każdego kryterium';
$string['score'] = 'Punkt';
$string['gradeoverriddenstudent'] = '(Zastąp w dzienniku ocen: {$a})';
$string['close'] = 'Zamknij';
$string['allfiles'] = 'Wszystkie pliki';
$string['add'] = 'Zapisz komentarz';
$string['attachments'] = 'Załączniki';
$string['commentrequired'] = 'Wymagany komentarz';
$string['commentloop'] = 'Pętla komentarzy';
$string['notreleased'] = 'Ocena z zadania nie została jeszcze udostępniona';
$string['eventgraderviewed'] = 'Wyświetlone w module Open Grader';
$string['eventactivitygraded'] = 'Oceniono aktywność w module Open Grader';
$string['eventcommentdeleted'] = 'Usunięto komentarz w module Open Grader';
$string['eventcommentadded'] = 'Dodano komentarz w module Open Grader';
$string['privacy:metadata:preference:fullscreen'] = 'Określa czy użytkownik widzi moduł Grader w trybie pełnoekranowym';
$string['privacy:metadata:preference:showpostsgrouped'] = 'Określa, czy użytkownik grupuje fora otwarte podczas ich oceniania';
$string['privacy:request:preference:fullscreenyes'] = 'Użytkownik preferuje wyświetlanie modułu Open Grader w trybie pełnoekranowym';
$string['privacy:request:preference:fullscreenno'] = 'Użytkownik preferuje wyświetlanie modułu Open Grader w widoku normalnym';
$string['privacy:request:preference:hsupostsgroupedyes'] = 'Użytkownik preferuje, aby fora otwarte były grupowane podczas ich oceniania';
$string['privacy:request:preference:hsupostsgroupedno'] = 'Użytkownik preferuje, aby fora otwarte nie były grupowane podczas ich oceniania';
