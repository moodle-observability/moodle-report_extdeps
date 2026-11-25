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
 * Checks the external dependencies of an activity now, or queues the check of a course.
 *
 * An activity is checked during the request (at most a few resources); a course is checked by a single
 * ad hoc task of tool_extdeps, queued only by this explicit request.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_extdeps\local\analysis;
use tool_extdeps\task\run_analysis;

require(__DIR__ . '/../../config.php');

$cmid = optional_param('cmid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

if ($cmid) {
    $courseid = (int) $DB->get_field('course_modules', 'course', ['id' => $cmid], MUST_EXIST);
}
$course = get_course($courseid);
require_login($course);
require_sesskey();
$context = context_course::instance($course->id);
require_capability('tool/extdeps:analyze', $context);
$PAGE->set_url(new moodle_url('/report/extdeps/verify.php'));

if ($cmid) {
    \core_php_time_limit::raise(300);
    $run = analysis::create_run('cm', $cmid, null, (int) $USER->id, false, false);
    $run = analysis::execute($run);
} else {
    $run = $DB->get_record_select(
        'tool_extdeps_run',
        "scope = 'course' AND scopeid = :courseid AND status IN ('queued', 'running')",
        ['courseid' => $course->id],
        '*',
        IGNORE_MULTIPLE
    );
    if (!$run) {
        $run = analysis::create_run('course', $course->id, null, (int) $USER->id, false, false);
        run_analysis::queue((int) $run->id);
    }
}

redirect(new moodle_url('/report/extdeps/run.php', ['id' => $run->id]));
