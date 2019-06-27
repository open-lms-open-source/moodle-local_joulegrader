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
$string['joulegrader:grade'] = 'Giv karakter for arbejde via Open Grader';
$string['joulegrader:view'] = 'Vis arbejde, der er givet karakter for via Open Grader';
$string['gradebook'] = 'Karakterbog';
$string['nothingtodisplay'] = 'Intet at vise';
$string['needsgrading'] = 'Vis aktiviteter, der kræver karaktergivning';
$string['allactivities'] = 'Vis alle aktiviteter';
$string['mobilenotsupported'] = 'Open Grader understøtter ikke mobilbrowsere på nuværende tidspunkt';
$string['exitfullscreen'] = 'Afslut fuldskærmstilstand';
$string['fullscreen'] = 'Fuldskærmstilstand';
$string['returncourse'] = 'Retur til kursus';
$string['grading'] = 'Karaktergivning';
$string['nogradeableareas'] = 'Ingen aktiviteter, der kan gives karakter for';
$string['nogradeableusers'] = 'Ingen brugere, der kan gives karakter til';
$string['showonlyuserposts'] = 'Vis kun brugeres indlæg';
$string['groupbydiscussion'] = 'Gruppér efter diskussion';
$string['activity'] = 'Aktivitet, der kan gives karakter for';
$string['activitynav'] = 'Aktiviteter, der kan gives karakter for';
$string['activitynav_help'] = 'Brug denne widget til at vælge, hvilken vurderbar aktivitet der skal gives karakter for.';
$string['group'] = 'Gruppe';
$string['groupnav'] = 'Grupper';
$string['groupnav_help'] = 'Brug denne widget til at vælge en gruppe.';
$string['user'] = 'Bruger';
$string['usernav'] = 'Brugere';
$string['usernav_help'] = 'Brug denne widget til at vælge, hvilken bruger der skal gives karakter til.';
$string['navviewlabel'] = 'Vis {$a}';
$string['commentdeleted'] = 'Bruger {$a->deletedby} slettede indlæg den {$a->deletedon}';
$string['deletecomment'] = 'Slet kommentar lavet den {$a}';
$string['previous'] = 'Forrige {$a}';
$string['next'] = 'Næste {$a}';
$string['assignmentavailable'] = 'Tilgængelig';
$string['on'] = 'den {$a}';
$string['until'] = 'indtil {$a}';
$string['lastedited'] = 'Senest redigeret den {$a}';
$string['assign23-latesubmission'] = 'Denne indsendelse var forsinket med {$a}.';
$string['assign23-userextensiondate'] = 'Afleveringsfristen blev forlænget til: {$a}';
$string['downloadall'] = 'Download alle filer';
$string['download'] = 'download';
$string['viewinline'] = 'vis indføjet';
$string['activitycomments'] = 'Aktivitetskommentarer';
$string['overallfeedback'] = 'Samlet feedback';
$string['filefeedback'] = 'Fil med feedback';
$string['attemptnumber'] = 'Forsøg {$a->attemptnumber}: {$a->attempttime}';
$string['viewingattempt'] = 'Viser forsøg';
$string['attemptstatus'] = 'Studerende har foretaget {$a->number} ud af {$a->outof} forsøg.';
$string['assignmentstatus'] = 'Opgavestatus';
$string['unlimited'] = 'ubegrænset';
$string['gradebookgrade'] = 'Aktuel karakter i karakterbog';
$string['attemptgrade'] = 'Forsøgskarakter';
$string['gradeoutof'] = 'Karakter (ud af {$a})';
$string['gradeoutofrange'] = 'Karakteren er uden for område';
$string['overridetext'] = 'En underviser har tidligere oprettet en karakter for denne aktivitet direkte i karakterbogen.  Markér dette afkrydsningsfelt, hvis du også ønsker at erstatte denne karakter.';
$string['save'] = 'Gem karakter';
$string['saveandnext'] = 'Gem karakter og næste';
$string['gradingdisabled'] = 'Denne aktivitets karaktergivning er låst. Frigiv karakteren via Gradebook for at aktivere karaktergivning.';
$string['applytoall'] = 'Anvend karakterer og feedback til hele gruppen';
$string['applytoall_help'] = 'Hvis "Ja" vælges, modtager alle gruppemedlemmer karakteren og feedbacken uanset eksisterende karakterer eller feedback i karakterbogen.';
$string['criteria'] = 'Kriterier';
$string['checklist'] = 'Tjekliste';
$string['gradesaved'] = 'Karakter blev opdateret';
$string['gradesavedx'] = '{$a} karakterer blev opdateret';
$string['couldnotsave'] = 'Karakteren kan ikke opdateres:';
$string['couldnotsavex'] = 'Karakter for {$a} kan ikke opdateres';
$string['notgraded'] = 'Karakter ikke afgivet for opgave';
$string['viewchecklistteacher'] = 'Giv karakter med tjekliste';
$string['viewrubricteacher'] = 'Giv karakter med vurderingskriterium';
$string['viewcheckliststudent'] = 'Vis tjekliste for karaktergivning';
$string['viewrubricstudent'] = 'Vis vurderingskriterium for karaktergivning';
$string['viewguidestudent'] = 'Vis vejledning til karaktergivning';
$string['viewguideteacher'] = 'Giv karakter med vejledning til karaktergivning';
$string['guide'] = 'Vejledning til karaktergivning';
$string['rubric'] = 'Vurderingskriterium';
$string['rubricerror'] = 'Vælg et niveau for hvert kriterium';
$string['guideerror'] = 'Angiv en gyldig karakter for hvert kriterium';
$string['score'] = 'Pointresultat';
$string['gradeoverriddenstudent'] = '(Tilsidesæt i karakterbog: {$a})';
$string['close'] = 'Luk';
$string['allfiles'] = 'Alle filer';
$string['add'] = 'Gem kommentar';
$string['attachments'] = 'Vedhæftede filer';
$string['commentrequired'] = 'Kommentar påkrævet';
$string['commentloop'] = 'Kommentarløkke';
$string['notreleased'] = 'Karakter for opgave endnu ikke frigivet';
$string['eventgraderviewed'] = 'Open Grader vist';
$string['eventactivitygraded'] = 'Karakter er afgivet for aktiviteten i Open Grader';
$string['eventcommentdeleted'] = 'Kommentar blev slettet i Open Grader';
$string['eventcommentadded'] = 'Kommentar blev tilføjet i Open Grader';
$string['privacy:metadata:preference:fullscreen'] = 'Hvorvidt en bruger har karaktergiveren på fuld skærm';
$string['privacy:metadata:preference:showpostsgrouped'] = 'Hvorvidt en bruger grupperer Open Forums, når der gives karakter for dem';
$string['privacy:request:preference:fullscreenyes'] = 'Brugeren foretrækker Open Grader i fuld skærm';
$string['privacy:request:preference:fullscreenno'] = 'Brugeren foretrækker Open Grader i normal visning';
$string['privacy:request:preference:hsupostsgroupedyes'] = 'Brugeren foretrækker, at Open Forums er grupperet, når der gives karakterer for dem';
$string['privacy:request:preference:hsupostsgroupedno'] = 'Brugeren foretrækker, at Open Forums ikke grupperes, når der gives karakterer for dem';
