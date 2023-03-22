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
$string['joulegrader:grade'] = 'Qualifica la feina amb Open Grader';
$string['joulegrader:view'] = 'Mostra les feines qualificades amb Open Grader';
$string['gradebook'] = 'Butlletí de qualificacions';
$string['nothingtodisplay'] = 'No hi ha res per mostrar';
$string['needsgrading'] = 'Mostra les activitats que requereixen qualificació';
$string['allactivities'] = 'Mostra totes les activitats';
$string['mobilenotsupported'] = 'Actualment Open Grader no és compatible amb els navegadors de dispositius mòbils';
$string['exitfullscreen'] = 'Surt del mode de pantalla completa';
$string['fullscreen'] = 'Mode de pantalla completa';
$string['returncourse'] = 'Torna al curs';
$string['grading'] = 'Qualificació';
$string['nogradeableareas'] = 'Activitats no qualificables';
$string['nogradeableusers'] = 'Usuaris no qualificables';
$string['showonlyuserposts'] = 'Mostra únicament els apunts dels usuaris';
$string['groupbydiscussion'] = 'Agrupa per debat';
$string['activity'] = 'Activitat qualificable';
$string['activitynav'] = 'Activitats qualificables';
$string['activitynav_help'] = 'Utilitzeu aquest giny per seleccionar l’activitat qualificable que voleu qualificar.';
$string['group'] = 'Grup';
$string['groupnav'] = 'Grups';
$string['groupnav_help'] = 'Utilitzeu aquest giny per seleccionar un grup.';
$string['user'] = 'Usuari';
$string['usernav'] = 'Usuaris';
$string['usernav_help'] = 'Utilitzeu aquest giny per seleccionar l’usuari que voleu qualificar.';
$string['navviewlabel'] = 'Visualització {$a}';
$string['commentdeleted'] = 'L’usuari {$a->deletedby} va suprimir el missatge el {$a->deletedon}';
$string['deletecomment'] = 'El comentari es va suprimir el {$a}';
$string['previous'] = 'Anterior {$a}';
$string['next'] = 'Següent {$a}';
$string['assignmentavailable'] = 'Disponible';
$string['on'] = 'el {$a}';
$string['until'] = 'fins a {$a}';
$string['lastedited'] = 'Darrera edició el {$a}';
$string['assign23-latesubmission'] = 'Aquesta tramesa s’ha retardat {$a}.';
$string['assign23-userextensiondate'] = 'Pròrroga concedida fins: {$a}';
$string['downloadall'] = 'Descarrega tots els fitxers';
$string['download'] = 'descarrega';
$string['viewinline'] = 'mostra-ho en línia';
$string['activitycomments'] = 'Comentaris d’activitat';
$string['overallfeedback'] = 'Retroalimentació global';
$string['filefeedback'] = 'Retroacció amb fitxer';
$string['attemptnumber'] = 'Intent {$a->attemptnumber}: {$a->attempttime}';
$string['viewingattempt'] = 'S’està visualitzant l’intent';
$string['attemptstatus'] = 'L’estudiant ha fet {$a->number} de {$a->outof} intents.';
$string['assignmentstatus'] = 'Estat de la tasca';
$string['unlimited'] = 'sense límit';
$string['gradebookgrade'] = 'Qualificació actual del butlletí de qualificacions';
$string['attemptgrade'] = 'Qualificació de l’intent';
$string['gradeoutof'] = 'Qualificació (de {$a})';
$string['gradeoutofrange'] = 'La qualificació està fora dels límits';
$string['overridetext'] = 'Anteriorment, un instructor va crear una qualificació per a aquesta activitat directament al butlletí de qualificacions. Marqueu aquesta casella si també voleu substituir aquesta qualificació.';
$string['save'] = 'Desa la qualificació';
$string['saveandnext'] = 'Desa la qualificació i vés al següent';
$string['gradingdisabled'] = 'La qualificació d’activitats està blocada Per habilitar la qualificació, desbloqueu la qualificació des del butlletí de qualificacions.';
$string['applytoall'] = 'Aplica les qualificacions i la retroacció al grup sencer';
$string['applytoall_help'] = 'Si heu seleccionat &quot;Sí&quot; tots els membres del grup rebran la qualificació i la retroacció independentment de si hi ha cap qualificació o retroacció al butlletí de qualificacions.';
$string['criteria'] = 'Criteris';
$string['checklist'] = 'Llista de comprovació';
$string['gradesaved'] = 'Qualificació actualitzada correctament';
$string['gradesavedx'] = '{$a} qualificacions actualitzades correctament';
$string['couldnotsave'] = 'No s’ha pogut actualitzar la qualificació';
$string['couldnotsavex'] = 'No s’ha pogut actualitzar la qualificació de {$a}';
$string['notgraded'] = 'Tasca no qualificada';
$string['viewchecklistteacher'] = 'Qualificació amb llista de comprovació';
$string['viewrubricteacher'] = 'Qualificació amb rúbrica';
$string['viewcheckliststudent'] = 'Mostra la llista de comprovació de qualificació';
$string['viewrubricstudent'] = 'Mostra la rúbrica de qualificació';
$string['viewguidestudent'] = 'Mostra la guia de marques de qualificació';
$string['viewguideteacher'] = 'Qualifica amb la guia de qualificació';
$string['guide'] = 'Guia de qualificació';
$string['rubric'] = 'Rúbrica';
$string['rubricerror'] = 'Seleccioneu un nivell per a cada criteri';
$string['guideerror'] = 'Proporcioneu una qualificació vàlida per a cada criteri';
$string['score'] = 'Puntuació';
$string['gradeoverriddenstudent'] = '(Excepció al butlletí de qualificacions: {$a})';
$string['close'] = 'Tanca';
$string['allfiles'] = 'Tots els fitxers';
$string['add'] = 'Desa el comentari';
$string['attachments'] = 'Fitxers adjunts';
$string['commentrequired'] = 'Comentari obligatori';
$string['commentloop'] = 'Cicle de comentaris';
$string['notreleased'] = 'Encara no s’han publicat les qualificacions en el butlletí de qualificacions';
$string['eventgraderviewed'] = 'S\'ha visualitzat Open Grader';
$string['eventactivitygraded'] = 'Activitat qualificada amb Open Grader';
$string['eventcommentdeleted'] = 'Comentari suprimit a Open Grader';
$string['eventcommentadded'] = 'Comentari afegit a Open Grader';
$string['privacy:metadata:preference:fullscreen'] = 'Si l\'usuari té Grader a pantalla completa o no';
$string['privacy:metadata:preference:showpostsgrouped'] = 'Si l\'usuari agrupa o no els fòrums d\'Open Forum per qualificar-los';
$string['privacy:request:preference:fullscreenyes'] = 'L\'usuari prefereix Open Grader a pantalla completa';
$string['privacy:request:preference:fullscreenno'] = 'L\'usuari prefereix una visualització normal de l\'Open Grader';
$string['privacy:request:preference:hsupostsgroupedyes'] = 'L\'usuari prefereix que els fòrums d\'Open Forum estiguin agrupats per qualificar-los';
$string['privacy:request:preference:hsupostsgroupedno'] = 'L\'usuari prefereix que els fòrums d\'Open Forum no estiguin agrupats per qualificar-los';
