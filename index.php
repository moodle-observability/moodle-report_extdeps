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
 * Site view: external dependencies of every course.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('report_extdeps', '', null, '', ['pagelayout' => 'report']);

$context = context_system::instance();
$report = \core_reportbuilder\system_report_factory::create(\report_extdeps\systemreports\courses::class, $context);

$buttons = [
    ['url' => (new moodle_url('/report/extdeps/occurrences.php'))->out(false),
        'label' => get_string('alloccurrences', 'report_extdeps')],
];
if (has_capability('tool/extdeps:manage', $context)) {
    $buttons[] = ['url' => (new moodle_url('/report/extdeps/analyze.php'))->out(false),
        'label' => get_string('runanalysis', 'report_extdeps')];
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'report_extdeps'));
echo $OUTPUT->render_from_template('report_extdeps/summary', [
    'cards' => \report_extdeps\local\helper::cards(),
    'buttons' => $buttons,
]);
echo $OUTPUT->heading(get_string('coursesreport', 'report_extdeps'), 3);
echo $report->output();
echo $OUTPUT->footer();
