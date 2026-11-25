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
 * Site view: every place where an external dependency is used, with filters and export.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$all = optional_param('all', 0, PARAM_BOOL);

admin_externalpage_setup(
    'report_extdeps',
    '',
    null,
    new moodle_url('/report/extdeps/occurrences.php'),
    ['pagelayout' => 'report']
);
$PAGE->navbar->add(get_string('alloccurrences', 'report_extdeps'));

$report = \core_reportbuilder\system_report_factory::create(
    \report_extdeps\systemreports\occurrences::class,
    context_system::instance(),
    '',
    '',
    0,
    ['courseid' => 0, 'all' => $all]
);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('alloccurrences', 'report_extdeps'));
echo $OUTPUT->render_from_template('report_extdeps/summary', [
    'cards' => [],
    'nocards' => true,
    'toggle' => \report_extdeps\local\helper::toggle_link(new moodle_url('/report/extdeps/occurrences.php'), (bool) $all),
]);
echo $report->output();
echo $OUTPUT->footer();
