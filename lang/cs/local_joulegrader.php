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
$string['joulegrader:grade'] = 'Klasifikovat práci prostřednictvím pluginu Open Grader';
$string['joulegrader:view'] = 'Zobrazit práci klasifikovanou prostřednictvím pluginu Open Grader';
$string['gradebook'] = 'Centrum klasifikace';
$string['nothingtodisplay'] = 'Žádné položky k zobrazení';
$string['needsgrading'] = 'Zobrazit aktivity vyžadující klasifikaci';
$string['allactivities'] = 'Zobrazit všechny aktivity';
$string['mobilenotsupported'] = 'Plugin Open Grader aktuálně nepodporuje mobilní prohlížeče.';
$string['exitfullscreen'] = 'Ukončit režim celé obrazovky';
$string['fullscreen'] = 'Režim celé obrazovky';
$string['returncourse'] = 'Návrat do kurzu';
$string['grading'] = 'Klasifikace';
$string['nogradeableareas'] = 'Žádné klasifikovatelné aktivity';
$string['nogradeableusers'] = 'Žádní klasifikovatelní uživatelé';
$string['showonlyuserposts'] = 'Zobrazit pouze příspěvky uživatele';
$string['groupbydiscussion'] = 'Seskupit podle diskuze';
$string['activity'] = 'Klasifikovatelná aktivita';
$string['activitynav'] = 'Klasifikovatelné aktivity';
$string['activitynav_help'] = 'Pomocí této pomůcky lze vybrat, kterou klasifikovatelnou aktivitu chcete klasifikovat.';
$string['group'] = 'Skupina';
$string['groupnav'] = 'Skupiny';
$string['groupnav_help'] = 'Pomocí této pomůcky lze vybrat skupinu.';
$string['user'] = 'Uživatel';
$string['usernav'] = 'Uživatelé';
$string['usernav_help'] = 'Pomocí této pomůcky lze vybrat, kterého uživatele chcete klasifikovat.';
$string['navviewlabel'] = 'Zobrazit {$a}';
$string['commentdeleted'] = 'Uživatel {$a->deletedby} odstranil příspěvek dne {$a->deletedon}.';
$string['deletecomment'] = 'Odstranit komentář vytvořený dne {$a}';
$string['previous'] = 'Předchozí {$a}';
$string['next'] = 'Další {$a}';
$string['assignmentavailable'] = 'K dispozici';
$string['on'] = 'dne {$a}';
$string['until'] = 'do {$a}';
$string['lastedited'] = 'Datum poslední úpravy: {$a}';
$string['assign23-latesubmission'] = 'Uživatel {$a} odeslal tento příspěvek pozdě.';
$string['assign23-userextensiondate'] = 'Prodloužení poskytnuto do: {$a}';
$string['downloadall'] = 'Stáhnout všechny soubory';
$string['download'] = 'stáhnout';
$string['viewinline'] = 'zobrazit vložené';
$string['activitycomments'] = 'Komentáře k aktivitě';
$string['overallfeedback'] = 'Celková zpětná vazba';
$string['filefeedback'] = 'Zpětná vazba souboru';
$string['attemptnumber'] = 'Pokus {$a->attemptnumber}: {$a->attempttime}';
$string['viewingattempt'] = 'Pokus o zobrazení';
$string['attemptstatus'] = 'Student provedl {$a->number} z {$a->outof} pokusů.';
$string['assignmentstatus'] = 'Stav úkolu';
$string['unlimited'] = 'bez omezení';
$string['gradebookgrade'] = 'Aktuální klasifikace v centru klasifikace';
$string['attemptgrade'] = 'Klasifikace pokusu';
$string['gradeoutof'] = 'Klasifikace (z {$a})';
$string['gradeoutofrange'] = 'Klasifikace je mimo rozsah';
$string['overridetext'] = 'Instruktor dříve vytvořil klasifikaci této aktivity přímo v centru klasifikace. Toto políčko zaškrtněte, chcete-li tuto klasifikaci také změnit.';
$string['save'] = 'Uložit klasifikaci';
$string['saveandnext'] = 'Uložit klasifikaci a další';
$string['gradingdisabled'] = 'Klasifikace této aktivity je uzamčena. Odemkněte ji pomocí centra klasifikace.';
$string['applytoall'] = 'Použít klasifikace a zpětnou vazbu na celou skupinu';
$string['applytoall_help'] = 'Vyberete-li Ano, získají všichni členové skupiny klasifikaci a zpětnou vazbu bez ohledu na všechny existující klasifikace a zpětné vazby v centru klasifikace.';
$string['criteria'] = 'Kritéria';
$string['checklist'] = 'Kontrolní seznam';
$string['gradesaved'] = 'Klasifikace byla úspěšně aktualizována.';
$string['gradesavedx'] = 'Počet úspěšně aktualizovaných klasifikací: {$a}';
$string['couldnotsave'] = 'Klasifikaci nelze aktualizovat.';
$string['couldnotsavex'] = 'Klasifikaci uživatele {$a} nelze aktualizovat.';
$string['notgraded'] = 'Úkoly nebyly aktualizovány.';
$string['viewchecklistteacher'] = 'Klasifikace s kontrolním seznamem';
$string['viewrubricteacher'] = 'Klasifikace s předpisem kritérií';
$string['viewcheckliststudent'] = 'Zobrazit klasifikační kontrolní seznam';
$string['viewrubricstudent'] = 'Zobrazit klasifikační předpis kritérií';
$string['viewguidestudent'] = 'Zobrazit klasifikační průvodce hodnocením';
$string['viewguideteacher'] = 'Klasifikovat s průvodcem hodnocením';
$string['guide'] = 'Průvodce hodnocením';
$string['rubric'] = 'Předpis kritérií';
$string['rubricerror'] = 'Vyberte jednu úroveň pro každé kritérium.';
$string['guideerror'] = 'Zadejte platnou klasifikaci pro každé kritérium.';
$string['score'] = 'Skóre';
$string['gradeoverriddenstudent'] = '(Přepsat v centru klasifikace: {$a})';
$string['close'] = 'Zavřít';
$string['allfiles'] = 'Všechny soubory';
$string['add'] = 'Uložit komentář';
$string['attachments'] = 'Přílohy';
$string['commentrequired'] = 'Komentář je povinný.';
$string['commentloop'] = 'Opakování komentáře';
$string['notreleased'] = 'Klasifikace úkolu nebyla ještě uvolněna.';
$string['eventgraderviewed'] = 'Zobrazen plugin Open Grader';
$string['eventactivitygraded'] = 'Klasifikována aktivita v pluginu Open Grader';
$string['eventcommentdeleted'] = 'Odstraněn komentář v pluginu Open Grader';
$string['eventcommentadded'] = 'Přidán komentář v pluginu Open Grader';
$string['privacy:metadata:preference:fullscreen'] = 'Informace, zda má uživatel nástroj Grader na celé obrazovce';
$string['privacy:metadata:preference:showpostsgrouped'] = 'Informace, zda má uživatel při klasifikaci seskupená fóra Open';
$string['privacy:request:preference:fullscreenyes'] = 'Uživatel preferuje zobrazení pluginu Open Grader na celé obrazovce.';
$string['privacy:request:preference:fullscreenno'] = 'Uživatel preferuje normální zobrazení pluginu Open Grader.';
$string['privacy:request:preference:hsupostsgroupedyes'] = 'Uživatel preferuje seskupení fór Open při jejich klasifikaci.';
$string['privacy:request:preference:hsupostsgroupedno'] = 'Uživatel preferuje neseskupování fór Open při jejich klasifikaci.';
