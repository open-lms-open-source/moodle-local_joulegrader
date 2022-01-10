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
 * @copyright  Copyright (c) 2022 Open LMS (https://www.openlms.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Open-arviointitoiminto';
$string['joulegrader:grade'] = 'Arvioi töitä Open-arviointitoiminnolla';
$string['joulegrader:view'] = 'Näytä arvioidut työt Open-arviointitoiminnossa';
$string['gradebook'] = 'Arviointikirja';
$string['nothingtodisplay'] = 'Ei näytettävää';
$string['needsgrading'] = 'Näytä arviointia edellyttävät aktiviteetit';
$string['allactivities'] = 'Näytä kaikki aktiviteetit';
$string['mobilenotsupported'] = 'Open-arviointitoiminto ei tällä hetkellä tue mobiiliselaimia';
$string['exitfullscreen'] = 'Poistu koko näytön tilasta';
$string['fullscreen'] = 'Koko näytön tila';
$string['returncourse'] = 'Palaa kurssille';
$string['grading'] = 'Arviointi';
$string['nogradeableareas'] = 'Ei arvioitavia aktiviteetteja';
$string['nogradeableusers'] = 'Ei arvioitavia käyttäjiä';
$string['showonlyuserposts'] = 'Näytä vain käyttäjien viestit';
$string['groupbydiscussion'] = 'Ryhmitä keskustelun mukaan';
$string['activity'] = 'Arvioitava aktiviteetti';
$string['activitynav'] = 'Arvioitavat aktiviteetit';
$string['activitynav_help'] = 'Tämän widgetin avulla voit valita arvioitavan aktiviteetin.';
$string['group'] = 'Ryhmä';
$string['groupnav'] = 'Ryhmät';
$string['groupnav_help'] = 'Tämän widgetin avulla voit valita ryhmän.';
$string['user'] = 'Käyttäjä';
$string['usernav'] = 'Käyttäjät';
$string['usernav_help'] = 'Tämän widgetin avulla voit valita arvioitavan käyttäjän.';
$string['navviewlabel'] = 'Näytä {$a}';
$string['commentdeleted'] = 'Käyttäjä {$a->deletedby} poisti viestin {$a->deletedon}';
$string['deletecomment'] = 'Poista kommentti ajalta {$a}';
$string['previous'] = 'Edellinen {$a}';
$string['next'] = 'Seuraava {$a}';
$string['assignmentavailable'] = 'Valittavissa';
$string['on'] = '{$a}';
$string['until'] = '{$a} saakka';
$string['lastedited'] = 'Viimeksi muokattu {$a}';
$string['assign23-latesubmission'] = 'Tämä palautus oli myöhässä {$a}.';
$string['assign23-userextensiondate'] = 'Olet saanut lisäaikaa {$a} asti';
$string['downloadall'] = 'Lataa kaikki tiedostot';
$string['download'] = 'lataa';
$string['viewinline'] = 'näytä esikatselu';
$string['activitycomments'] = 'Aktiviteetin kommentit';
$string['overallfeedback'] = 'Suorituksen yleispalaute';
$string['filefeedback'] = 'Tiedostopalaute';
$string['attemptnumber'] = 'Suorituskerta {$a->attemptnumber}: {$a->attempttime}';
$string['viewingattempt'] = 'Tarkastellaan suoritusta';
$string['attemptstatus'] = 'Opiskelija on yrittänyt suoritusta {$a->number}/{$a->outof} kertaa.';
$string['assignmentstatus'] = 'Tehtävän tila';
$string['unlimited'] = 'rajoittamaton';
$string['gradebookgrade'] = 'Arviointikirjan arvosana';
$string['attemptgrade'] = 'Suorituskerran arvosana';
$string['gradeoutof'] = 'Arvosana (0 - {$a})';
$string['gradeoutofrange'] = 'Arvosana ei ole alueella';
$string['overridetext'] = 'Ohjaaja on luonut arvosanan tälle aktiviteetille suoraan arviointikirjaan. Valitse tämä ruutu, jos haluat korvata kyseisen arvosanan.';
$string['save'] = 'Tallenna arvosana';
$string['saveandnext'] = 'Tallenna ja arvioi seuraava';
$string['gradingdisabled'] = 'Tämän aktiviteetin arviointi on lukittu. Jos haluat tehdä uuden arvioinnin, avaa lukitus arviointikirjassa.';
$string['applytoall'] = 'Anna sama arvosana ja palaute koko ryhmälle';
$string['applytoall_help'] = 'Jos valitset Kyllä, kaikki ryhmän jäsenet saavat saman arvosanan ja palautteen riippumatta arviointikirjan nykyisestä arvosanasta tai palautteesta.';
$string['criteria'] = 'Kriteeri';
$string['checklist'] = 'Tarkistuslista';
$string['gradesaved'] = 'Arvosana päivitetty';
$string['gradesavedx'] = '{$a} arvosanaa päivitetty';
$string['couldnotsave'] = 'Arvosanan päivitys epäonnistui';
$string['couldnotsavex'] = 'Arvosanan ({$a}) päivitys epäonnistui';
$string['notgraded'] = 'Tehtävää ei ole arvioitu';
$string['viewchecklistteacher'] = 'Arvioi tarkistuslistan avulla';
$string['viewrubricteacher'] = 'Arvioi arviointimatriisin avulla';
$string['viewcheckliststudent'] = 'Näytä arvioinnin tarkistuslista';
$string['viewrubricstudent'] = 'Näytä arviointimatriisi';
$string['viewguidestudent'] = 'Näytä arviointiopas';
$string['viewguideteacher'] = 'Arvioi arviointioppaan avulla';
$string['guide'] = 'Arviointiopas';
$string['rubric'] = 'Arviointimatriisi';
$string['rubricerror'] = 'Valitse yksi taso kullekin arviointikriteerille';
$string['guideerror'] = 'Huolehdi, että kunkin arviointikriteerin arvosana on kelvollinen';
$string['score'] = 'Sijoitus';
$string['gradeoverriddenstudent'] = '(Korvaa arviointikirjan arvosana: {$a})';
$string['close'] = 'Sulje';
$string['allfiles'] = 'Kaikki tiedostot';
$string['add'] = 'Tallenna kommentti';
$string['attachments'] = 'Liitteet';
$string['commentrequired'] = 'Kommentti on pakollinen';
$string['commentloop'] = 'Kommenttisilmukka';
$string['notreleased'] = 'Tehtävän arvosanaa ei ole vielä julkaistu';
$string['eventgraderviewed'] = 'Open-arviointitoimintoa katseltu';
$string['eventactivitygraded'] = 'Aktiviteetti arvioitu Open-arviointitoiminnossa';
$string['eventcommentdeleted'] = 'Kommentti poistettu Open-arviointitoiminnossa';
$string['eventcommentadded'] = 'Kommentti lisätty Open-arviointitoiminnossa';
$string['privacy:metadata:preference:fullscreen'] = 'Ilmaisee, käyttääkö käyttäjä arviointitoimintoa koko näytön tilassa';
$string['privacy:metadata:preference:showpostsgrouped'] = 'Ilmaisee, ryhmitteleekö käyttäjä Open-keskustelualueet niitä arvostellessaan';
$string['privacy:request:preference:fullscreenyes'] = 'Käyttäjä käyttää mieluummin Open-arviointitoimintoa koko näytön tilassa';
$string['privacy:request:preference:fullscreenno'] = 'Käyttäjä käyttää mieluummin Open-arviointitoimintoa normaalinäkymässä';
$string['privacy:request:preference:hsupostsgroupedyes'] = 'Käyttäjä haluaa mieluummin ryhmitellä Open-keskustelualueet niitä arvostellessaan';
$string['privacy:request:preference:hsupostsgroupedno'] = 'Käyttäjä ei halua ryhmitellä Open-keskustelualueita niitä arvostellessaan';
