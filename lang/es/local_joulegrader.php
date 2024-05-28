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

$string['pluginname'] = 'Grader de Open';
$string['joulegrader:grade'] = 'Calificar trabajo a través del Grader de Open';
$string['joulegrader:view'] = 'Ver el trabajo calificado a través del Grader de Open';
$string['gradebook'] = 'Libro de calificaciones';
$string['nothingtodisplay'] = 'Nada que mostrar';
$string['needsgrading'] = 'Mostrar las actividades que requieren calificación';
$string['allactivities'] = 'Mostrar todas Las actividades';
$string['mobilenotsupported'] = 'Actualmente, el Grader de Open no es compatible con navegadores móviles';
$string['exitfullscreen'] = 'Salir del modo de pantalla completa';
$string['fullscreen'] = 'Modo de pantalla completa';
$string['returncourse'] = 'Volver al curso';
$string['grading'] = 'Calificación';
$string['nogradeableareas'] = 'No hay actividades calificables';
$string['nogradeableusers'] = 'No hay usuarios calificables';
$string['showonlyuserposts'] = 'Mostrar solo publicaciones del usuario';
$string['groupbydiscussion'] = 'Agrupar por debate';
$string['activity'] = 'Actividad calificable';
$string['activitynav'] = 'Actividades calificables';
$string['activitynav_help'] = 'Use este widget para seleccionar la actividad calificable que desea calificar.';
$string['group'] = 'Grupo';
$string['groupnav'] = 'Grupos';
$string['groupnav_help'] = 'Use este widget para seleccionar un grupo.';
$string['user'] = 'Usuario';
$string['usernav'] = 'Usuarios';
$string['usernav_help'] = 'Use este widget para seleccionar qué usuario calificar.';
$string['navviewlabel'] = 'Ver {$a}';
$string['commentdeleted'] = 'El usuario {$a->deletedby} borró un mensaje sobre {$a->deletedon}';
$string['deletecomment'] = 'Eliminar comentario hecho el {$a}';
$string['previous'] = '{$a} anterior';
$string['next'] = '{$a} siguiente';
$string['assignmentavailable'] = 'Disponible';
$string['on'] = 'el {$a}';
$string['until'] = 'hasta {$a}';
$string['lastedited'] = 'Última edición el {$a}';
$string['assign23-latesubmission'] = 'Este envío se retrasó por {$a}.';
$string['assign23-userextensiondate'] = 'Extensión otorgada hasta: {$a}';
$string['downloadall'] = 'Descargar todos los archivos';
$string['download'] = 'descargar';
$string['viewinline'] = 'ver en línea';
$string['activitycomments'] = 'Comentarios de la actividad';
$string['overallfeedback'] = 'Retroalimentación general';
$string['filefeedback'] = 'Retroalimentación del archivo';
$string['attemptnumber'] = 'Intento {$a->attemptnumber}: {$a->attempttime}';
$string['viewingattempt'] = 'Visualización de intento';
$string['attemptstatus'] = 'El estudiante ha realizado {$a->number} de {$a->outof} intentos.';
$string['assignmentstatus'] = 'Estado de la tarea';
$string['unlimited'] = 'ilimitado';
$string['gradebookgrade'] = 'Calificación actual en libro de calificaciones';
$string['attemptgrade'] = 'Calificación del intento';
$string['gradeoutof'] = 'Calificación (de {$a})';
$string['gradeoutofrange'] = 'La calificación está fuera de rango';
$string['overridetext'] = 'Anteriormente, un profesor creó una calificación para esta actividad directamente en el libro de calificaciones. Marque esta casilla si también desea reemplazar esa calificación.';
$string['save'] = 'Guardar calificación';
$string['saveandnext'] = 'Guardar calificación y siguiente';
$string['gradingdisabled'] = 'La calificación de esta actividad está bloqueada. Para habilitar la calificación, desbloquee la calificación a través del Libro de calificaciones.';
$string['applytoall'] = 'Aplicar calificaciones y retroalimentación a todo el grupo.';
$string['applytoall_help'] = 'Si se selecciona "Sí", todos los miembros del grupo recibirán la calificación y la retroalimentación, independientemente de cualquier calificación o retroalimentación existente en el libro de calificaciones.';
$string['criteria'] = 'Criterio';
$string['checklist'] = 'Lista de verificación';
$string['gradesaved'] = 'Calificación actualizada exitosamente';
$string['gradesavedx'] = 'Las calificaciones de {$a} se actualizaron exitosamente.';
$string['couldnotsave'] = 'La calificación no se pudo actualizar.';
$string['couldnotsavex'] = 'La calificación para {$a} no se pudo actualizar.';
$string['notgraded'] = 'Tarea no calificada';
$string['viewchecklistteacher'] = 'Calificar con lista de verificación';
$string['viewrubricteacher'] = 'Calificar con indicación';
$string['viewcheckliststudent'] = 'Ver lista de verificación de calificaciones';
$string['viewrubricstudent'] = 'Ver indicación de calificación';
$string['viewguidestudent'] = 'Ver guía de marcado de calificaciones';
$string['viewguideteacher'] = 'Calificar con guía de marcado';
$string['guide'] = 'Guía de marcado';
$string['rubric'] = 'Indicación';
$string['rubricerror'] = 'Seleccione un nivel para cada criterio';
$string['guideerror'] = 'Proporcione una calificación válida para cada criterio';
$string['score'] = 'Puntuación';
$string['gradeoverriddenstudent'] = '(Omitir en el libro de calificaciones: {$a})';
$string['close'] = 'Cerrar';
$string['allfiles'] = 'Todos los archivos';
$string['add'] = 'Guardar comentario';
$string['attachments'] = 'Archivos adjuntos';
$string['commentrequired'] = 'Es obligatorio introducir un comentario';
$string['commentloop'] = 'Repetición del comentario';
$string['notreleased'] = 'Calificación de la tarea aún no publicada';
$string['eventgraderviewed'] = 'Grader de Open visto';
$string['eventactivitygraded'] = 'Actividad calificada en el Grader de Open';
$string['eventcommentdeleted'] = 'Comentario eliminado en el Grader de Open';
$string['eventcommentadded'] = 'Comentario añadido en el Grader de Open';
$string['privacy:metadata:preference:fullscreen'] = 'Si un usuario tiene o no el calificador en pantalla completa';
$string['privacy:metadata:preference:showpostsgrouped'] = 'Si un usuario agrupa o no los foros de Open al calificarlos';
$string['privacy:request:preference:fullscreenyes'] = 'El usuario prefiere tener el Grader de Open en pantalla completa';
$string['privacy:request:preference:fullscreenno'] = 'El usuario prefiere tener el Grader de Open en la vista normal';
$string['privacy:request:preference:hsupostsgroupedyes'] = 'El usuario prefiere que los foros de Open estén agrupados al calificarlos';
$string['privacy:request:preference:hsupostsgroupedno'] = 'El usuario prefiere que los foros de Open no estén agrupados al calificarlos';
