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
$string['joulegrader:grade'] = 'Noter un travail avec Open Grader';
$string['joulegrader:view'] = 'Consulter le travail noté avec Open Grader';
$string['gradebook'] = 'Carnet de notes';
$string['nothingtodisplay'] = 'Rien à afficher';
$string['needsgrading'] = 'Afficher les activités à noter';
$string['allactivities'] = 'Afficher toutes les activités';
$string['mobilenotsupported'] = 'À l\'heure actuelle, Open Grader ne prend pas en charge les navigateurs mobiles';
$string['exitfullscreen'] = 'Quitter le mode plein écran';
$string['fullscreen'] = 'Mode plein écran';
$string['returncourse'] = 'Revenir au cours';
$string['grading'] = 'Évaluation';
$string['nogradeableareas'] = 'Aucune activité évaluable';
$string['nogradeableusers'] = 'Aucun utilisateur évaluable';
$string['showonlyuserposts'] = 'N\'afficher que les messages de l\'utilisateur';
$string['groupbydiscussion'] = 'Regrouper par discussion';
$string['activity'] = 'Activité évaluable';
$string['activitynav'] = 'Activités évaluables';
$string['activitynav_help'] = 'Ce widget permet de sélectionner l\'activité évaluable à noter.';
$string['group'] = 'Groupe';
$string['groupnav'] = 'Groupes';
$string['groupnav_help'] = 'Utilisez ce widget pour sélectionner un groupe.';
$string['user'] = 'Utilisateur';
$string['usernav'] = 'Utilisateurs';
$string['usernav_help'] = 'Utilisez ce widget pour sélectionner l\'utilisateur à noter.';
$string['navviewlabel'] = 'Afficher {$a}';
$string['commentdeleted'] = 'L\'utilisateur {$a->deletedby} a supprimé le message sur {$a->deletedon}';
$string['deletecomment'] = 'Supprimer le commentaire sur {$a}';
$string['previous'] = '{$a} précédent';
$string['next'] = '{$a} suivant';
$string['assignmentavailable'] = 'Disponible(s)';
$string['on'] = 'sur {$a}';
$string['until'] = 'jusqu\'au {$a}';
$string['lastedited'] = 'Dernière modification : {$a}';
$string['assign23-latesubmission'] = 'La soumission a été effectuée avec {$a} de retard.';
$string['assign23-userextensiondate'] = 'Prolongation accordée jusqu\'au : {$a}';
$string['downloadall'] = 'Télécharger tous les fichiers';
$string['download'] = 'télécharger';
$string['viewinline'] = 'voir en ligne';
$string['activitycomments'] = 'Commentaires d\'activité';
$string['overallfeedback'] = 'Feed-back général';
$string['filefeedback'] = 'Fichiers comme feed-back';
$string['attemptnumber'] = 'Tentative {$a->attemptnumber} : {$a->attempttime}';
$string['viewingattempt'] = 'Affichage de la tentative';
$string['attemptstatus'] = 'L\'étudiant a fait {$a->number} tentative(s) sur {$a->outof}.';
$string['assignmentstatus'] = 'État du devoir';
$string['unlimited'] = 'illimité';
$string['gradebookgrade'] = 'Note actuelle dans le carnet de notes';
$string['attemptgrade'] = 'Note de tentative';
$string['gradeoutof'] = 'Note (sur {$a})';
$string['gradeoutofrange'] = 'La note est en dehors de la fourchette';
$string['overridetext'] = 'Un professeur a déjà créé une note pour cette activité directement dans le carnet de notes. Cochez cette case si vous voulez aussi remplacer cette note.';
$string['save'] = 'Enregistrer la note';
$string['saveandnext'] = 'Enregistrer la note et passer à la suivante';
$string['gradingdisabled'] = 'La notation de cette activité est verrouillée. Pour l\'activer, déverrouillez cette note dans le carnet de notes.';
$string['applytoall'] = 'Appliquer les notes et le feed-back à tout le groupe';
$string['applytoall_help'] = 'Si vous choisissez Oui, tous les membres du groupe recevront la note et le feed-back indépendamment des notes et feed-backs existant dans le carnet de notes.';
$string['criteria'] = 'Critères';
$string['checklist'] = 'Check-list';
$string['gradesaved'] = 'La note a bien été mise à jour';
$string['gradesavedx'] = '{$a} notes ont bien été mises à jour';
$string['couldnotsave'] = 'La note n\'a pas pu être mise à jour';
$string['couldnotsavex'] = 'La note de {$a} n\'a pas pu être mise à jour';
$string['notgraded'] = 'Devoir non noté';
$string['viewchecklistteacher'] = 'Note avec check-list';
$string['viewrubricteacher'] = 'Note avec grille d\'évaluation';
$string['viewcheckliststudent'] = 'Afficher la check-list de notation';
$string['viewrubricstudent'] = 'Afficher la rubrique de notation';
$string['viewguidestudent'] = 'Afficher le guide d\'évaluation';
$string['viewguideteacher'] = 'Noter avec le guide d\'évaluation';
$string['guide'] = 'Guide d\'évaluation';
$string['rubric'] = 'Critères';
$string['rubricerror'] = 'Sélectionnez un niveau pour chaque critère';
$string['guideerror'] = 'Merci de fournir une note valide pour chaque critère';
$string['score'] = 'Score';
$string['gradeoverriddenstudent'] = '(Remplacer dans le carnet de notes : {$a})';
$string['close'] = 'Fermer';
$string['allfiles'] = 'Tous les fichiers';
$string['add'] = 'Enregistrer le commentaire';
$string['attachments'] = 'Annexes';
$string['commentrequired'] = 'Commentaire obligatoire';
$string['commentloop'] = 'Boucle de commentaire';
$string['notreleased'] = 'Note du devoir pas encore publiée';
$string['eventgraderviewed'] = 'Consulté dans Open Grader';
$string['eventactivitygraded'] = 'Activité notée dans Open Grader';
$string['eventcommentdeleted'] = 'Commentaire supprimé dans Open Grader';
$string['eventcommentadded'] = 'Commentaire ajouté dans Open Grader';
$string['privacy:metadata:preference:fullscreen'] = 'Indique si un utilisateur emploie Open Grader en plein écran';
$string['privacy:metadata:preference:showpostsgrouped'] = 'Indique si un utilisateur groupe les forums Open lorsqu\'il les évalue';
$string['privacy:request:preference:fullscreenyes'] = 'L\'utilisateur préfère Open Grader en plein écran';
$string['privacy:request:preference:fullscreenno'] = 'L\'utilisateur préfère l\'affichage normal d\'Open Grader';
$string['privacy:request:preference:hsupostsgroupedyes'] = 'L\'utilisateur préfère que les forums Open soient groupés lorsqu\'il les évalue';
$string['privacy:request:preference:hsupostsgroupedno'] = 'L\'utilisateur préfère que les forums Open ne soient pas groupés lorsqu\'il les évalue';
