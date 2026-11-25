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

use context_system;
use core_course\reportbuilder\local\entities\course_category;
use core_reportbuilder\local\entities\course;
use core_reportbuilder\system_report;
use lang_string;
use report_extdeps\reportbuilder\local\entities\course_summary;

/**
 * Courses that use external dependencies, with their counters (site view).
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class courses extends system_report {
    /**
     * Main table, entities, columns and filters.
     *
     * @return void
     */
    protected function initialise(): void {
        $courseentity = new course();
        $coursealias = $courseentity->get_table_alias('course');
        $this->set_main_table('course', $coursealias);
        $this->add_entity($courseentity);

        $summary = (new course_summary())->set_table_alias('course', $coursealias);
        $this->add_entity($summary);

        $category = new course_category();
        $categoryalias = $category->get_table_alias('course_categories');
        $this->add_entity($category->add_join(
            "JOIN {course_categories} {$categoryalias} ON {$categoryalias}.id = {$coursealias}.category"
        ));

        $this->add_base_fields("{$coursealias}.id");
        $this->add_base_condition_sql(
            "EXISTS (SELECT 1 FROM {tool_extdeps_occurrence} xo WHERE xo.courseid = {$coursealias}.id)"
        );

        $this->add_columns_from_entities([
            'course_summary:report',
            'course_category:name',
            'course:visible',
            'course:enddate',
            'course_summary:brokenoccurrences',
            'course_summary:brokenresources',
            'course_summary:occurrences',
            'course_summary:lastscan',
        ]);
        $this->set_initial_sort_column('course_summary:brokenoccurrences', SORT_DESC);

        $this->add_filters_from_entities([
            'course_summary:hasbroken',
            'course:fullname',
            'course_category:name',
            'course:visible',
            'course:enddate',
        ]);

        $this->set_downloadable(true, get_string('downloadcourses', 'report_extdeps'));
        $this->set_default_no_results_notice(new lang_string('nocourses', 'report_extdeps'));
    }

    /**
     * Only who can see the site report.
     *
     * @return bool
     */
    protected function can_view(): bool {
        return has_capability('report/extdeps:view', context_system::instance());
    }

    /**
     * Name of the report.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('coursesreport', 'report_extdeps');
    }
}
