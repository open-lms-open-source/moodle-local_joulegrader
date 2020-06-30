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

$string['pluginname'] = 'Avaliador aberto';
$string['joulegrader:grade'] = 'Avaliar trabalho usando o Avaliador aberto';
$string['joulegrader:view'] = 'Visualizar trabalho avaliado com o Avaliador aberto';
$string['gradebook'] = 'Boletim de notas';
$string['nothingtodisplay'] = 'Nada a ser exibido';
$string['needsgrading'] = 'Exibir atividades que necessitam de avaliação';
$string['allactivities'] = 'Exibir todas as atividades';
$string['mobilenotsupported'] = 'No momento, o Avaliador aberto não tem suporte para navegadores móveis';
$string['exitfullscreen'] = 'Sair do modo de tela cheia';
$string['fullscreen'] = 'Modo de tela cheia';
$string['returncourse'] = 'Voltar ao curso';
$string['grading'] = 'Avaliação';
$string['nogradeableareas'] = 'Nenhuma atividade passível de avaliação';
$string['nogradeableusers'] = 'Nenhum usuário passível de avaliação';
$string['showonlyuserposts'] = 'Exibir somente as postagens do usuário';
$string['groupbydiscussion'] = 'Agrupar por discussão';
$string['activity'] = 'Atividade passível de avaliação';
$string['activitynav'] = 'Atividades passíveis de avaliação';
$string['activitynav_help'] = 'Use este widget para selecionar qual atividade passível de avaliação você deseja avaliar.';
$string['group'] = 'Grupo';
$string['groupnav'] = 'Grupos';
$string['groupnav_help'] = 'Use este widget para selecionar um grupo.';
$string['user'] = 'Usuário';
$string['usernav'] = 'Usuários';
$string['usernav_help'] = 'Use este widget para selecionar qual usuário você deseja avaliar.';
$string['navviewlabel'] = 'Visualizar {$a}';
$string['commentdeleted'] = 'O usuário {$a->deletedby} excluiu a postagem em {$a->deletedon}';
$string['deletecomment'] = 'Excluir comentário feito em {$a}';
$string['previous'] = '{$a} anterior';
$string['next'] = 'Próximo {$a}';
$string['assignmentavailable'] = 'Disponível';
$string['on'] = 'em {$a}';
$string['until'] = 'até {$a}';
$string['lastedited'] = 'Última edição em {$a}';
$string['assign23-latesubmission'] = 'Este envio foi feito {$a} depois do prazo.';
$string['assign23-userextensiondate'] = 'Prorrogação concedida até: {$a}';
$string['downloadall'] = 'Fazer o download de todos os arquivos';
$string['download'] = 'Fazer o download';
$string['viewinline'] = 'visualizar dentro do sistema';
$string['activitycomments'] = 'Comentários da atividade';
$string['overallfeedback'] = 'Comentários globais';
$string['filefeedback'] = 'Comentários do arquivo';
$string['attemptnumber'] = 'Tentativa {$a->attemptnumber}: {$a->attempttime}';
$string['viewingattempt'] = 'Visualizando tentativa';
$string['attemptstatus'] = 'O aluno realizou {$a->number} de {$a->outof} tentativas.';
$string['assignmentstatus'] = 'Status da tarefa';
$string['unlimited'] = 'ilimitado';
$string['gradebookgrade'] = 'Nota atual no Boletim de notas';
$string['attemptgrade'] = 'Nota da tentativa';
$string['gradeoutof'] = 'Nota (de um total de {$a})';
$string['gradeoutofrange'] = 'A nota está fora do intervalo';
$string['overridetext'] = 'Anteriormente, um instrutor criava uma nota para essa atividade diretamente no Boletim de notas. Marque esta caixa se também quiser substituir essa nota.';
$string['save'] = 'Salvar nota';
$string['saveandnext'] = 'Salvar nota e prosseguir';
$string['gradingdisabled'] = 'A avaliação desta atividade está bloqueada. Para ativar a avaliação, desbloqueie a nota no Boletim de notas.';
$string['applytoall'] = 'Aplicar notas e comentários para todo o grupo.';
$string['applytoall_help'] = 'Se "Sim" for selecionado, todos os membros do grupo receberão a nota e os comentários, independentemente de quaisquer nota ou comentários existentes no Boletim de notas.';
$string['criteria'] = 'Critérios';
$string['checklist'] = 'Lista de progresso';
$string['gradesaved'] = 'Notas atualizadas com sucesso';
$string['gradesavedx'] = '{$a} notas atualizadas com sucesso';
$string['couldnotsave'] = 'Não foi possível atualizar a nota';
$string['couldnotsavex'] = 'Não foi possível atualizar a nota para {$a}';
$string['notgraded'] = 'Tarefa não avaliada';
$string['viewchecklistteacher'] = 'Avaliar com lista de progresso';
$string['viewrubricteacher'] = 'Avaliar com critério de avaliação';
$string['viewcheckliststudent'] = 'Visualizar lista de progresso de avaliação';
$string['viewrubricstudent'] = 'Visualizar critério de avaliação';
$string['viewguidestudent'] = 'Visualizar guia de pontuação da avaliação';
$string['viewguideteacher'] = 'Avaliar com guia de pontuação';
$string['guide'] = 'Guia de pontuação';
$string['rubric'] = 'Critério de avaliação';
$string['rubricerror'] = 'Selecione um nível para cada critério';
$string['guideerror'] = 'Insira uma nota válida para cada critério';
$string['score'] = 'Pontuação';
$string['gradeoverriddenstudent'] = '(Sobrescrever no Boletim de notas: {$a})';
$string['close'] = 'Fechar';
$string['allfiles'] = 'Todos os arquivos';
$string['add'] = 'Salvar comentário';
$string['attachments'] = 'Anexos';
$string['commentrequired'] = 'Comentário obrigatório';
$string['commentloop'] = 'Loop de comentário';
$string['notreleased'] = 'Nota da tarefa ainda não liberada';
$string['eventgraderviewed'] = 'Avaliador aberto visualizado';
$string['eventactivitygraded'] = 'Atividade avaliada no Avaliador aberto';
$string['eventcommentdeleted'] = 'Comentário excluído no Avaliador aberto';
$string['eventcommentadded'] = 'Comentário adicionado no Avaliador aberto';
$string['privacy:metadata:preference:fullscreen'] = 'Se um usuário tem ou não o avaliador em tela inteira';
$string['privacy:metadata:preference:showpostsgrouped'] = 'Se um usuário agrupa ou não Fóruns abertos ao avaliá-los';
$string['privacy:request:preference:fullscreenyes'] = 'O usuário prefere o Avaliador aberto em tela inteira';
$string['privacy:request:preference:fullscreenno'] = 'O usuário prefere o Avaliador aberto em visualização normal';
$string['privacy:request:preference:hsupostsgroupedyes'] = 'O usuário prefere os Fóruns abertos agrupados ao avaliá-los';
$string['privacy:request:preference:hsupostsgroupedno'] = 'O usuário prefere os Fóruns abertos não agrupados ao avaliá-los';
