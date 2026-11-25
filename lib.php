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
 * Callbacks of report_extdeps.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Adds the report to the course reports.
 *
 * @param navigation_node $navigation Reports node of the course.
 * @param stdClass $course Course.
 * @param context $context Course context.
 * @return void
 */
function report_extdeps_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/extdeps:viewcourse', $context)) {
        $url = new moodle_url('/report/extdeps/course.php', ['id' => $course->id]);
        $navigation->add(
            get_string('pluginname', 'report_extdeps'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'report_extdeps',
            new pix_icon('i/report', '')
        );
    }
}
