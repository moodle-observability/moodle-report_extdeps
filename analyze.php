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
 * Queues an on demand analysis of a course, a provider or the whole site.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_extdeps\local\analysis;
use tool_extdeps\task\run_analysis;

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('report_extdeps_analyze', '', null, '', ['pagelayout' => 'report']);
$context = context_system::instance();
require_capability('tool/extdeps:analyze', $context);

$form = new \report_extdeps\form\analyze_form();
if ($data = $form->get_data()) {
    $scope = in_array($data->scope, ['course', 'provider', 'site'], true) ? $data->scope : 'site';
    $scopeid = $scope === 'course' ? (int) $data->courseid : 0;
    $provider = $scope === 'provider' ? (string) $data->provider : null;
    if ($scope === 'course') {
        get_course($scopeid);
    }
    $run = $DB->get_record_select(
        'tool_extdeps_run',
        "scope = :scope AND scopeid = :scopeid AND status IN ('queued', 'running')",
        ['scope' => $scope, 'scopeid' => $scopeid],
        '*',
        IGNORE_MULTIPLE
    );
    if (!$run) {
        $run = analysis::create_run($scope, $scopeid, $provider, (int) $USER->id, (bool) $data->dryrun, (bool) $data->strict);
        run_analysis::queue((int) $run->id);
    }
    redirect(new moodle_url('/report/extdeps/run.php', ['id' => $run->id]));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('runanalysis', 'report_extdeps'));
echo html_writer::tag('p', get_string('runanalysis_help', 'report_extdeps'));
$form->display();
echo $OUTPUT->footer();
