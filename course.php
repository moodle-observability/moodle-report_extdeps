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
 * Course view: external dependencies of one course, with what to do and where to edit.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$all = optional_param('all', 0, PARAM_BOOL);
$course = get_course($id);
require_login($course);
$context = context_course::instance($course->id);
require_capability('report/extdeps:viewcourse', $context);

$PAGE->set_url(new moodle_url('/report/extdeps/course.php', ['id' => $course->id, 'all' => (int) $all]));
$PAGE->set_pagelayout('report');
$shortname = format_string($course->shortname, true, ['context' => $context]);
$PAGE->set_title(get_string('pluginname', 'report_extdeps') . ': ' . $shortname);
$PAGE->set_heading(format_string($course->fullname, true, ['context' => $context]));

$report = \core_reportbuilder\system_report_factory::create(
    \report_extdeps\systemreports\occurrences::class,
    $context,
    '',
    '',
    0,
    ['courseid' => $course->id, 'all' => $all]
);

$buttons = [];
if (has_capability('tool/extdeps:analyze', $context)) {
    $buttons[] = [
        'url' => (new moodle_url('/report/extdeps/verify.php'))->out(false),
        'label' => get_string('verifycourse', 'report_extdeps'),
        'post' => true,
        'sesskey' => sesskey(),
        'courseid' => $course->id,
    ];
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'report_extdeps'));
echo $OUTPUT->render_from_template('report_extdeps/summary', [
    'cards' => \report_extdeps\local\helper::cards($course->id),
    'buttons' => $buttons,
    'intro' => get_string('courseintro', 'report_extdeps'),
    'showadvice' => true,
    'toggle' => \report_extdeps\local\helper::toggle_link(
        new moodle_url('/report/extdeps/course.php', ['id' => $course->id]),
        (bool) $all
    ),
]);
echo $report->output();
echo $OUTPUT->footer();
