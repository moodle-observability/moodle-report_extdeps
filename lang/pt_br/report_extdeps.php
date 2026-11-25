<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Strings of report_extdeps (pt_br).
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activitytype'] = 'Tipo de atividade';
$string['activityvisible'] = 'Atividade visível';
$string['advice'] = 'O que fazer';
$string['alloccurrences'] = 'Todas as ocorrências';
$string['brokencount'] = '{$a} quebradas';
$string['brokenoccurrences'] = 'Ocorrências quebradas';
$string['brokenresources'] = 'Dependências quebradas';
$string['clearexception'] = 'Remover o falso positivo';
$string['courseintro'] = 'Vídeos, pacotes e outros conteúdos externos usados neste curso. Os quebrados mostram o que fazer e um link para editar o lugar onde são usados.';
$string['coursesreport'] = 'Cursos';
$string['datasource'] = 'Dependências externas';
$string['downloadcourses'] = 'dependencias-externas-cursos';
$string['downloadoccurrences'] = 'dependencias-externas';
$string['dryrun'] = 'Só relatar';
$string['dryrun_help'] = 'Só relata: não muda nenhuma situação nem envia aviso.';
$string['entitycoursesummary'] = 'Dependências externas do curso';
$string['entityoccurrence'] = 'Ocorrência';
$string['entityresource'] = 'Dependência externa';
$string['evidence'] = 'Evidência';
$string['exceptionactive'] = 'Aceito como falso positivo:';
$string['exceptioncleared'] = 'O falso positivo foi removido.';
$string['exceptionmarked'] = 'Aceito como falso positivo. Não envia mais avisos até o conteúdo mudar.';
$string['exceptionreason'] = 'Por que é um falso positivo';
$string['extdeps:view'] = 'Ver as dependências externas do site';
$string['extdeps:viewcourse'] = 'Ver as dependências externas de um curso';
$string['falsepositive'] = 'Falso positivo';
$string['falsepositive_help'] = 'Se o conteúdo funciona para os estudantes, mesmo aparecendo como quebrado, aceite como falso positivo. Ele continua sendo verificado, mas não envia avisos.';
$string['field'] = 'Campo';
$string['from'] = 'De';
$string['history'] = 'Histórico';
$string['httpstats'] = '{$a->requests} requisições, {$a->seconds} s esperando os provedores, a mais lenta {$a->maxseconds} s';
$string['lastcheck'] = 'Última verificação';
$string['lastresult'] = 'Último resultado';
$string['lastscan'] = 'Última varredura';
$string['markexception'] = 'Aceitar como falso positivo';
$string['method'] = 'Método';
$string['nocourses'] = 'Nenhum curso usa dependências externas.';
$string['nohistory'] = 'Ainda não houve mudança de situação.';
$string['nooccurrences'] = 'Nenhuma dependência externa encontrada.';
$string['noplaces'] = 'Não é usada em nenhum curso.';
$string['nothingfound'] = 'Nada encontrado ainda.';
$string['occurrences'] = 'Ocorrências';
$string['occurrencesreport'] = 'Dependências externas';
$string['okcount'] = '{$a} disponíveis';
$string['onlybroken'] = 'Quebradas';
$string['pendingcount'] = '{$a} não verificadas';
$string['pendinginfo'] = 'A análise roda em segundo plano; esta página se atualiza sozinha.';
$string['places'] = 'Onde é usada';
$string['pluginname'] = 'Dependências externas';
$string['privacy:metadata'] = 'O relatório de Dependências externas não guarda dados pessoais; ele mostra os dados do tool_extdeps.';
$string['progress'] = 'Progresso';
$string['provider'] = 'Provedor';
$string['resourcekey'] = 'Identificador';
$string['resourcetitle'] = 'Dependência externa';
$string['runanalysis'] = 'Executar análise';
$string['runanalysis_help'] = 'Verifica agora as dependências externas de um curso, de um provedor ou do site inteiro, em segundo plano. O resultado mostra o veredito e a evidência de cada uma.';
$string['runfailed'] = 'A análise falhou. Veja os registros das tarefas.';
$string['runresult'] = 'Resultado da análise';
$string['runstatus_done'] = 'Concluída';
$string['runstatus_failed'] = 'Falhou';
$string['runstatus_queued'] = 'Na fila';
$string['runstatus_running'] = 'Em execução';
$string['scope'] = 'Escopo';
$string['scope_cm'] = 'Atividade';
$string['scope_course'] = 'Curso';
$string['scope_provider'] = 'Provedor';
$string['scope_resource'] = 'Dependência';
$string['scope_site'] = 'Site inteiro';
$string['sectionsummary'] = 'Resumo da seção';
$string['showall'] = 'Mostrar também as disponíveis';
$string['showbroken'] = 'Mostrar só as quebradas';
$string['since'] = 'Desde';
$string['sincedate'] = 'desde {$a}';
$string['status'] = 'Situação';
$string['strict'] = 'Modo estrito';
$string['strict_help'] = 'Usa um segundo método, independente, quando o provedor tem, e mostra quando eles discordam.';
$string['timefound'] = 'Encontrada em';
$string['to'] = 'Para';
$string['url'] = 'Endereço';
$string['verdict'] = 'Veredito';
$string['verifycourse'] = 'Verificar este curso agora';
$string['verifynow'] = 'Verificar agora';
