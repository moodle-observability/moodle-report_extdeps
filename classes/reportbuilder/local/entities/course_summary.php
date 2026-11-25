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

namespace report_extdeps\reportbuilder\local\entities;

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use lang_string;
use report_extdeps\local\helper;

/**
 * Counters of external dependencies per course.
 *
 * Joined to the course entity by giving it the alias of the course table.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_summary extends base {
    /**
     * Tables used by the entity.
     *
     * @return string[]
     */
    protected function get_default_tables(): array {
        return ['course'];
    }

    /**
     * Title of the entity.
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entitycoursesummary', 'report_extdeps');
    }

    /**
     * Adds the columns and filters.
     *
     * @return base
     */
    public function initialise(): base {
        foreach ($this->get_all_columns() as $column) {
            $this->add_column($column);
        }
        foreach ($this->get_all_filters() as $filter) {
            $this->add_filter($filter)->add_condition($filter);
        }
        return $this;
    }

    /**
     * Count of occurrences of the course, optionally only the broken ones.
     *
     * @param bool $broken Only broken.
     * @param bool $resources Count distinct resources instead of occurrences.
     * @return string
     */
    private function count_sql(bool $broken, bool $resources = false): string {
        $course = $this->get_table_alias('course');
        $what = $resources ? 'COUNT(DISTINCT r.id)' : 'COUNT(o.id)';
        $where = $broken ? ' AND r.confirmedstatus IN (' . helper::broken_in() . ')' : '';
        return "(SELECT {$what}
                   FROM {tool_extdeps_occurrence} o
                   JOIN {tool_extdeps_resource} r ON r.id = o.resourceid
                  WHERE o.courseid = {$course}.id{$where})";
    }

    /**
     * Columns of the entity.
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $course = $this->get_table_alias('course');
        $columns = [];

        $columns[] = (new column('report', new lang_string('course'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$course}.fullname, {$course}.id")
            ->set_is_sortable(true, ["{$course}.fullname"])
            ->add_callback(static function (?string $fullname, \stdClass $row): string {
                $context = \context_course::instance((int) $row->id, IGNORE_MISSING);
                $name = $context ? format_string((string) $fullname, true, ['context' => $context]) : s((string) $fullname);
                return \html_writer::link(new \moodle_url('/report/extdeps/course.php', ['id' => $row->id]), $name);
            });

        $columns[] = (new column(
            'brokenoccurrences',
            new lang_string('brokenoccurrences', 'report_extdeps'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field($this->count_sql(true), 'brokenoccurrences')
            ->set_is_sortable(true);

        $columns[] = (new column(
            'brokenresources',
            new lang_string('brokenresources', 'report_extdeps'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field($this->count_sql(true, true), 'brokenresources')
            ->set_is_sortable(true);

        $columns[] = (new column('occurrences', new lang_string('occurrences', 'report_extdeps'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field($this->count_sql(false), 'occurrences')
            ->set_is_sortable(true);

        $columns[] = (new column('lastscan', new lang_string('lastscan', 'report_extdeps'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field(
                "(SELECT MAX(s.lastscan) FROM {tool_extdeps_coursestate} s WHERE s.courseid = {$course}.id)",
                'lastscan'
            )
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate'], get_string('strftimedatetimeshort', 'core_langconfig'));

        return $columns;
    }

    /**
     * Filters of the entity.
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        return [
            (new filter(
                select::class,
                'hasbroken',
                new lang_string('onlybroken', 'report_extdeps'),
                $this->get_entity_name(),
                'CASE WHEN ' . $this->count_sql(true) . ' > 0 THEN 1 ELSE 0 END'
            ))
                ->add_joins($this->get_joins())
                ->set_options([1 => new lang_string('yes'), 0 => new lang_string('no')]),
        ];
    }
}
