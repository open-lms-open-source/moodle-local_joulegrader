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

$string['pluginname'] = 'Valutatore Open';
$string['joulegrader:grade'] = 'Valuta lavoro tramite il valutatore di Open';
$string['joulegrader:view'] = 'Visualizza lavoro valutato tramite il valutatore Open';
$string['gradebook'] = 'Registro voti';
$string['nothingtodisplay'] = 'Nessun elemento da visualizzare';
$string['needsgrading'] = 'Mostra attività da valutare';
$string['allactivities'] = 'Mostra tutte le attività';
$string['mobilenotsupported'] = 'Il valutatore Open attualmente non supporta i browser per dispositivi mobili';
$string['exitfullscreen'] = 'Esci da modalità schermo intero';
$string['fullscreen'] = 'Modalità schermo intero';
$string['returncourse'] = 'Torna al corso';
$string['grading'] = 'Valutazione';
$string['nogradeableareas'] = 'Nessuna attività valutabile';
$string['nogradeableusers'] = 'Nessuno utente valutabile';
$string['showonlyuserposts'] = 'Mostra solo messaggi utente';
$string['groupbydiscussion'] = 'Gruppo per discussione';
$string['activity'] = 'Attività valutabile';
$string['activitynav'] = 'Attività valutabili';
$string['activitynav_help'] = 'Utilizza questo widget per selezionare l\'attività valutabile da valutare.';
$string['group'] = 'Gruppo';
$string['groupnav'] = 'Gruppi';
$string['groupnav_help'] = 'Utilizza questo widget per selezionare un gruppo.';
$string['user'] = 'Utente';
$string['usernav'] = 'Utenti';
$string['usernav_help'] = 'Utilizza questo widget per selezionare l\'utente da valutare.';
$string['navviewlabel'] = 'Visualizza {$a}';
$string['commentdeleted'] = 'Messaggio eliminato da {$a->deletedby} il {$a->deletedon}';
$string['deletecomment'] = 'Elimina commento pubblicato il {$a}';
$string['previous'] = 'Precedente {$a}';
$string['next'] = 'Successivo {$a}';
$string['assignmentavailable'] = 'Disponibile';
$string['on'] = 'il {$a}';
$string['until'] = 'fino a {$a}';
$string['lastedited'] = 'Ultima modifica il {$a}';
$string['assign23-latesubmission'] = 'Consegna in ritardo di {$a}.';
$string['assign23-userextensiondate'] = 'Proroga concessa fino a: {$a}';
$string['downloadall'] = 'Scarica tutti i file';
$string['download'] = 'download';
$string['viewinline'] = 'visualizza online';
$string['activitycomments'] = 'Commenti attività';
$string['overallfeedback'] = 'Feedback complessivo';
$string['filefeedback'] = 'Feedback del file';
$string['attemptnumber'] = 'Tentativo {$a->attemptnumber}: {$a->attempttime}';
$string['viewingattempt'] = 'Visualizzazione tentativo';
$string['attemptstatus'] = 'Lo studente ha eseguito {$a->number} tentativi su {$a->outof}.';
$string['assignmentstatus'] = 'Stato compito';
$string['unlimited'] = 'illimitato';
$string['gradebookgrade'] = 'Voto corrente nel registro voti';
$string['attemptgrade'] = 'Tentativo di voto';
$string['gradeoutof'] = 'Voto (su {$a})';
$string['gradeoutofrange'] = 'Il voto non rientra nell\'intervallo';
$string['overridetext'] = 'Un docente ha precedentemente creato un voto per questa attività direttamente nel Registro voti.  Se desideri sostituire anche quel voto, seleziona questa casella.';
$string['save'] = 'Salva voto';
$string['saveandnext'] = 'Salva voto e prosegui';
$string['gradingdisabled'] = 'La valutazione di questa attività è bloccata. Per attivarla, sblocca il voto tramite il Registro voti.';
$string['applytoall'] = 'Applica i voti e il feedback a tutto il gruppo';
$string['applytoall_help'] = 'Selezionando "Sì", tutti i membri del gruppo riceveranno un voto e un feedback indipendentemente da qualsiasi voto o feedback esistente nel Registro voti.';
$string['criteria'] = 'Criteri';
$string['checklist'] = 'Elenco di controllo';
$string['gradesaved'] = 'Voto aggiornato correttamente';
$string['gradesavedx'] = '{$a} voti caricati correttamente';
$string['couldnotsave'] = 'Impossibile aggiornare il voto';
$string['couldnotsavex'] = 'Impossibile aggiornare il voto per {$a}';
$string['notgraded'] = 'Compito non valutato';
$string['viewchecklistteacher'] = 'Voto con elenco di controllo';
$string['viewrubricteacher'] = 'Voto con rubrica';
$string['viewcheckliststudent'] = 'Visualizza elenco di controllo valutazione';
$string['viewrubricstudent'] = 'Visualizza rubrica valutazione';
$string['viewguidestudent'] = 'Visualizza guida alla valutazione';
$string['viewguideteacher'] = 'Voto con guida alla valutazione';
$string['guide'] = 'Guida alla valutazione';
$string['rubric'] = 'Rubrica';
$string['rubricerror'] = 'Seleziona un livello per ciascun criterio';
$string['guideerror'] = 'Fornisci un voto valido per ciascun criterio';
$string['score'] = 'Punteggio';
$string['gradeoverriddenstudent'] = '(Modifica in Registro voti: {$a})';
$string['close'] = 'Chiudi';
$string['allfiles'] = 'Tutti i file';
$string['add'] = 'Salva commento';
$string['attachments'] = 'Allegati';
$string['commentrequired'] = 'Commento obbligatorio';
$string['commentloop'] = 'Ciclo di commenti';
$string['notreleased'] = 'Il voto del compito non è stato ancora comunicato';
$string['eventgraderviewed'] = 'Valutatore Open visualizzato';
$string['eventactivitygraded'] = 'Attività valutata nel valutatore di Open';
$string['eventcommentdeleted'] = 'Commento eliminato nel valutatore di Open';
$string['eventcommentadded'] = 'Commento aggiunto nel valutatore di Open';
$string['privacy:metadata:preference:fullscreen'] = 'Se un utente dispone o meno del valutatore a schermo intero';
$string['privacy:metadata:preference:showpostsgrouped'] = 'Se un utente raggruppa o meno i forum Open durante la valutazione degli stessi';
$string['privacy:request:preference:fullscreenyes'] = 'L\'utente preferisce il valutatore Open a schermo intero';
$string['privacy:request:preference:fullscreenno'] = 'L\'utente preferisce il valutatore Open in visualizzazione normale';
$string['privacy:request:preference:hsupostsgroupedyes'] = 'L\'utente preferisce che i forum Open siano raggruppati durante la valutazione';
$string['privacy:request:preference:hsupostsgroupedno'] = 'L\'utente preferisce che i forum Open non siano raggruppati durante la valutazione';
