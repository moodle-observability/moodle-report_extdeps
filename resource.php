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
 * One external dependency: situation, what to do, where it is used and the history of its situation.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use report_extdeps\local\helper;
use tool_extdeps\local\exceptions;

require(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

$resource = $DB->get_record('tool_extdeps_resource', ['id' => $id], '*', MUST_EXIST);
$systemcontext = context_system::instance();

if ($courseid) {
    $course = get_course($courseid);
    require_login($course);
    $context = context_course::instance($course->id);
    if (!has_capability('report/extdeps:view', $systemcontext)) {
        require_capability('report/extdeps:viewcourse', $context);
        // Only resources used in this course can be seen through it.
        if (!$DB->record_exists('tool_extdeps_occurrence', ['resourceid' => $resource->id, 'courseid' => $course->id])) {
            throw new moodle_exception('nopermissions', 'error', '', get_string('pluginname', 'report_extdeps'));
        }
    }
    $backurl = new moodle_url('/report/extdeps/course.php', ['id' => $course->id]);
} else {
    require_login();
    $context = $systemcontext;
    require_capability('report/extdeps:view', $context);
    $backurl = new moodle_url('/report/extdeps/index.php');
}

$url = new moodle_url('/report/extdeps/resource.php', ['id' => $resource->id, 'courseid' => $courseid]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('resourcetitle', 'report_extdeps'));
$PAGE->set_heading(get_string('resourcetitle', 'report_extdeps'));

$form = null;
if (has_capability('tool/extdeps:manage', $systemcontext)) {
    $exists = exceptions::exists((int) $resource->id);
    $form = new \report_extdeps\form\exception_form($url, ['exists' => $exists]);
    $form->set_data(['id' => $resource->id, 'courseid' => $courseid]);
    if ($data = $form->get_data()) {
        if (!empty($data->clear)) {
            exceptions::clear((int) $resource->id);
            $message = get_string('exceptioncleared', 'report_extdeps');
        } else {
            exceptions::mark((int) $resource->id, (int) $USER->id, trim($data->reason));
            $message = get_string('exceptionmarked', 'report_extdeps');
        }
        redirect($url, $message, null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('report_extdeps/resource', helper::resource_data($resource, $courseid, $backurl));
if ($form) {
    echo $OUTPUT->heading(get_string('falsepositive', 'report_extdeps'), 3);
    echo html_writer::tag('p', get_string('falsepositive_help', 'report_extdeps'), ['class' => 'text-muted']);
    $form->display();
}
echo $OUTPUT->footer();
