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

namespace report_extdeps\reportbuilder\datasource;

use core_course\reportbuilder\local\entities\course_category;
use core_reportbuilder\datasource;
use core_reportbuilder\local\entities\course;
use report_extdeps\reportbuilder\local\entities\occurrence;
use report_extdeps\reportbuilder\local\entities\resource;

/**
 * Source of custom reports: external dependencies of the courses and where they are used.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dependencies extends datasource {
    /**
     * Name of the source.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('datasource', 'report_extdeps');
    }

    /**
     * Main table and entities.
     *
     * @return void
     */
    protected function initialise(): void {
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

        $this->add_all_from_entities();
    }

    /**
     * Columns of a new report.
     *
     * @return string[]
     */
    public function get_default_columns(): array {
        return [
            'course:coursefullnamewithlink',
            'occurrence:activity',
            'resource:provider',
            'resource:url',
            'resource:status',
            'resource:timeconfirmed',
        ];
    }

    /**
     * Filters of a new report.
     *
     * @return string[]
     */
    public function get_default_filters(): array {
        return ['resource:provider', 'resource:status', 'course:fullname'];
    }

    /**
     * Conditions of a new report.
     *
     * @return string[]
     */
    public function get_default_conditions(): array {
        return ['resource:broken'];
    }

    /**
     * Values of the conditions of a new report: only broken dependencies.
     *
     * @return array
     */
    public function get_default_condition_values(): array {
        return ['resource:broken_operator' => \core_reportbuilder\local\filters\select::EQUAL_TO, 'resource:broken_value' => 1];
    }
}
