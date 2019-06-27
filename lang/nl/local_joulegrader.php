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
 * @copyright  Copyright (c) 2019 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Open Grader';
$string['joulegrader:grade'] = 'Werk beoordelen via Open Grader';
$string['joulegrader:view'] = 'Beoordeeld werk weergeven via Open Grader';
$string['gradebook'] = 'Cijferlijst';
$string['nothingtodisplay'] = 'Niets om te tonen';
$string['needsgrading'] = 'Toon activiteiten die beoordeeld moeten worden';
$string['allactivities'] = 'Toon alle activiteiten';
$string['mobilenotsupported'] = 'Open Grader ondersteunt momenteel geen mobiele browsers';
$string['exitfullscreen'] = 'Verlaat volledig scherm';
$string['fullscreen'] = 'Volledig scherm';
$string['returncourse'] = 'Keer terug naar cursus';
$string['grading'] = 'Beoordelen';
$string['nogradeableareas'] = 'Geen beoordeelbare activiteiten';
$string['nogradeableusers'] = 'Geen beoordeelbare gebruikers';
$string['showonlyuserposts'] = 'Toon alleen berichten van gebruikers';
$string['groupbydiscussion'] = 'Groepeer op discussie';
$string['activity'] = 'Beoordeelbare activiteit';
$string['activitynav'] = 'Beoordeelbare activiteiten';
$string['activitynav_help'] = 'Gebruik deze widget om te selecteren welke beoordeelbare activiteit moet worden beoordeeld.';
$string['group'] = 'Groep';
$string['groupnav'] = 'Groepen';
$string['groupnav_help'] = 'Gebruik deze widget om een groep te selecteren.';
$string['user'] = 'Gebruiker';
$string['usernav'] = 'Gebruikers';
$string['usernav_help'] = 'Gebruik deze widget om te selecteren welke gebruiker moet worden beoordeeld.';
$string['navviewlabel'] = 'Bekijk {$a}';
$string['commentdeleted'] = 'Gebruiker {$a->deletedby} heeft bericht verwijderd op {$a->deletedon}';
$string['deletecomment'] = 'Verwijder opmerking gemaakt op {$a}';
$string['previous'] = 'Vorige {$a}';
$string['next'] = 'Volgende {$a}';
$string['assignmentavailable'] = 'Beschikbaar';
$string['on'] = 'op {$a}';
$string['until'] = 'tot {$a}';
$string['lastedited'] = 'Laatst bewerkt op {$a}';
$string['assign23-latesubmission'] = 'Deze inzending was te laat met {$a}.';
$string['assign23-userextensiondate'] = 'Extra tijd gegeven tot: {$a}';
$string['downloadall'] = 'Download alle bestanden';
$string['download'] = 'download';
$string['viewinline'] = 'bekijk inline';
$string['activitycomments'] = 'Opmerkingen activiteit';
$string['overallfeedback'] = 'Algemene feedback';
$string['filefeedback'] = 'Bestandsfeedback';
$string['attemptnumber'] = 'Poging {$a->attemptnumber}: {$a->attempttime}';
$string['viewingattempt'] = 'Poging weergeven';
$string['attemptstatus'] = 'Student heeft {$a->number} van de {$a->outof} pogingen gedaan.';
$string['assignmentstatus'] = 'Status opdracht';
$string['unlimited'] = 'onbeperkt';
$string['gradebookgrade'] = 'Huidig cijfer in cijferlijst';
$string['attemptgrade'] = 'Cijfer poging';
$string['gradeoutof'] = 'Cijfer (van de {$a})';
$string['gradeoutofrange'] = 'Cijfer valt buiten bereik';
$string['overridetext'] = 'Een cursusleider heeft eerder rechtstreeks in de cijferlijst een cijfer aangemaakt voor deze activiteit. Vink dit vakje aan als je dat cijfer ook wilt vervangen.';
$string['save'] = 'Bewaar cijfer';
$string['saveandnext'] = 'Bewaar cijfer en volgende';
$string['gradingdisabled'] = 'De beoordeling van deze activiteit is vergrendeld. Om de beoordeling in te schakelen, moet je het cijfer ontgrendelen via de cijferlijst.';
$string['applytoall'] = 'Pas cijfers en feedback toe op de hele groep';
$string['applytoall_help'] = 'Indien "Ja" is geselecteerd, ontvangen alle groepsleden het cijfer en de feedback ongeacht bestaande cijfers of feedback in de cijferlijst.';
$string['criteria'] = 'Criteria';
$string['checklist'] = 'Checklist';
$string['gradesaved'] = 'Cijfer met succes bijgewerkt';
$string['gradesavedx'] = '{$a} cijfers met succes bijgewerkt';
$string['couldnotsave'] = 'Cijfer kan niet worden bijgewerkt';
$string['couldnotsavex'] = 'Cijfer voor {$a} kan niet worden bijgewerkt';
$string['notgraded'] = 'Opdracht niet beoordeeld';
$string['viewchecklistteacher'] = 'Beoordeling met checklist';
$string['viewrubricteacher'] = 'Beoordeling met rubriek';
$string['viewcheckliststudent'] = 'Bekijk beoordelingschecklist';
$string['viewrubricstudent'] = 'Bekijk beoordelingsrubriek';
$string['viewguidestudent'] = 'Bekijk beoordelingshulp';
$string['viewguideteacher'] = 'Beoordeling met beoordelingshulp';
$string['guide'] = 'Beoordelingshulp';
$string['rubric'] = 'Rubriek';
$string['rubricerror'] = 'Selecteer één niveau voor elk criterium';
$string['guideerror'] = 'Geef een geldig cijfer voor elk criterium';
$string['score'] = 'Score';
$string['gradeoverriddenstudent'] = '(Overschrijven in cijferlijst: {$a})';
$string['close'] = 'Sluit';
$string['allfiles'] = 'Alle bestanden';
$string['add'] = 'Bewaar opmerking';
$string['attachments'] = 'Bijlagen';
$string['commentrequired'] = 'Opmerking vereist';
$string['commentloop'] = 'Commentaar herhalen';
$string['notreleased'] = 'Opdrachtcijfer nog niet vrijgegeven';
$string['eventgraderviewed'] = 'Open Grader weergegeven';
$string['eventactivitygraded'] = 'Activiteit beoordeeld in Open Grader';
$string['eventcommentdeleted'] = 'Opmerking verwijderd in Open Grader';
$string['eventcommentadded'] = 'Opmerking toegevoegd in Open Grader';
$string['privacy:metadata:preference:fullscreen'] = 'Of de grader al dan niet op het volledige scherm wordt weergegeven voor een gebruiker';
$string['privacy:metadata:preference:showpostsgrouped'] = 'Of de Open-forums al dan niet worden gegroepeerd wanneer een gebruiker deze beoordeelt';
$string['privacy:request:preference:fullscreenyes'] = 'De gebruiker heeft de Open Grader liever op het volledige scherm';
$string['privacy:request:preference:fullscreenno'] = 'De gebruiker heeft de Open Grader liever in de normale weergave';
$string['privacy:request:preference:hsupostsgroupedyes'] = 'De gebruiker geeft er de voorkeur aan om de Open-forums te groeperen bij het beoordelen';
$string['privacy:request:preference:hsupostsgroupedno'] = 'De gebruiker geeft er de voorkeur aan om de Open-forums niet te groeperen bij het beoordelen';
