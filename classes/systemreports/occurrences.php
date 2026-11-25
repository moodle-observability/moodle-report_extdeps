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

namespace report_extdeps\systemreports;

use context_course;
use context_system;
use core_course\reportbuilder\local\entities\course_category;
use core_reportbuilder\local\entities\course;
use core_reportbuilder\local\report\action;
use core_reportbuilder\system_report;
use lang_string;
use moodle_url;
use pix_icon;
use report_extdeps\local\helper;
use report_extdeps\reportbuilder\local\entities\occurrence;
use report_extdeps\reportbuilder\local\entities\resource;
use stdClass;

/**
 * Places where external dependencies are used, of one course (parameter courseid) or of the whole site.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class occurrences extends system_report {
    /**
     * Main table, entities, columns, filters and actions.
     *
     * @return void
     */
    protected function initialise(): void {
        $courseid = (int) $this->get_parameter('courseid', 0, PARAM_INT);

        $occurrence = new occurrence();
        $alias = $occurrence->get_table_alias('tool_extdeps_occurrence');
        $this->set_main_table('tool_extdeps_occurrence', $alias);
        $this->add_entity($occurrence);

        $resource = new resource();
        $resourcealias = $resource->get_table_alias('tool_extdeps_resource');
        $this->add_entity($resource->add_join(
            "JOIN {tool_extdeps_resource} {$resourcealias} ON {$resourcealias}.id = {$alias}.resourceid"
        ));

        $courseentity = new course();
        $coursealias = $courseentity->get_table_alias('course');
        $coursejoin = "JOIN {course} {$coursealias} ON {$coursealias}.id = {$alias}.courseid";
        $this->add_entity($courseentity->add_join($coursejoin));

        $category = new course_category();
        $categoryalias = $category->get_table_alias('course_categories');
        $this->add_entity($category
            ->add_join($coursejoin)
            ->add_join("JOIN {course_categories} {$categoryalias} ON {$categoryalias}.id = {$coursealias}.category"));

        $this->add_base_fields("{$alias}.id, {$alias}.resourceid, {$alias}.courseid, {$alias}.cmid");
        if ($courseid) {
            $this->add_base_condition_simple("{$alias}.courseid", $courseid);
        }
        // By default only the broken ones: the available ones are the large majority and hide what needs work.
        if (!$this->get_parameter('all', 0, PARAM_BOOL)) {
            $this->add_base_condition_sql("{$resourcealias}.confirmedstatus IN (" . helper::broken_in() . ')');
        }

        // Few columns: what to do is shown once per situation above the report, not repeated on each row.
        $columns = $courseid ? ['occurrence:section'] : ['course:coursefullnamewithlink'];
        $this->add_columns_from_entities(array_merge($columns, [
            'occurrence:activity',
            'resource:provider',
            'resource:url',
            'resource:status',
            'resource:timeconfirmed',
            'occurrence:edit',
        ]));
        $this->set_initial_sort_column($courseid ? 'occurrence:section' : 'course:coursefullnamewithlink', SORT_ASC);

        $filters = ['resource:broken', 'resource:status', 'resource:provider', 'occurrence:modname',
            'occurrence:cmvisible', 'resource:resourcekey', 'resource:timeconfirmed'];
        if (!$courseid) {
            $filters = array_merge(['course:fullname', 'course_category:name', 'course:visible'], $filters);
        }
        $this->add_filters_from_entities($filters);

        $this->add_row_actions();
        $this->set_downloadable(true, get_string('downloadoccurrences', 'report_extdeps'));
        $this->set_default_no_results_notice(new lang_string('nooccurrences', 'report_extdeps'));
    }

    /**
     * History of the resource and, for activities, a check right now.
     *
     * @return void
     */
    private function add_row_actions(): void {
        $this->add_action(new action(
            new moodle_url('/report/extdeps/resource.php', ['id' => ':resourceid', 'courseid' => ':courseid']),
            new pix_icon('i/report', ''),
            [],
            false,
            new lang_string('history', 'report_extdeps')
        ));
        $this->add_action((new action(
            new moodle_url('/report/extdeps/verify.php', ['cmid' => ':cmid', 'sesskey' => sesskey()]),
            new pix_icon('i/reload', ''),
            [],
            false,
            new lang_string('verifynow', 'report_extdeps')
        ))->add_callback(static function (stdClass $row): bool {
            return !empty($row->cmid)
                && has_capability('tool/extdeps:analyze', context_course::instance((int) $row->courseid));
        }));
    }

    /**
     * The site report for who has report/extdeps:view; the course report for who sees that course.
     *
     * @return bool
     */
    protected function can_view(): bool {
        $courseid = (int) $this->get_parameter('courseid', 0, PARAM_INT);
        if ($courseid) {
            return has_capability('report/extdeps:viewcourse', context_course::instance($courseid));
        }
        return has_capability('report/extdeps:view', context_system::instance());
    }

    /**
     * Name of the report.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('occurrencesreport', 'report_extdeps');
    }
}
