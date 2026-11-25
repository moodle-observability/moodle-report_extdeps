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
use core_reportbuilder\local\filters\boolean_select;
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use lang_string;
use report_extdeps\local\helper;
use stdClass;

/**
 * Place of a course where an external dependency is used.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class occurrence extends base {
    /**
     * Tables used by the entity.
     *
     * @return string[]
     */
    protected function get_default_tables(): array {
        return ['tool_extdeps_occurrence', 'course_sections'];
    }

    /**
     * Title of the entity.
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entityoccurrence', 'report_extdeps');
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
     * Join of the section of the occurrence.
     *
     * @return string
     */
    private function section_join(): string {
        $alias = $this->get_table_alias('tool_extdeps_occurrence');
        $sections = $this->get_table_alias('course_sections');
        return "LEFT JOIN {course_sections} {$sections} ON {$sections}.id = {$alias}.sectionid";
    }

    /**
     * Columns of the entity.
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $alias = $this->get_table_alias('tool_extdeps_occurrence');
        $sections = $this->get_table_alias('course_sections');
        $columns = [];

        $columns[] = (new column('section', new lang_string('section'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->add_join($this->section_join())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$sections}.section, {$alias}.courseid")
            ->set_is_sortable(true, ["{$sections}.section"])
            ->add_callback(static function ($value, stdClass $row): string {
                if ($row->section === null) {
                    return '';
                }
                return helper::section_name((int) $row->courseid, (int) $row->section);
            });

        $columns[] = (new column('activity', new lang_string('activity'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$alias}.cmid, {$alias}.courseid, {$alias}.modname, {$alias}.source")
            ->set_is_sortable(true, ["{$alias}.modname", "{$alias}.cmid"])
            ->add_callback(static function ($value, stdClass $row): string {
                return helper::place_link($row);
            });

        $columns[] = (new column('modname', new lang_string('activitytype', 'report_extdeps'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.modname")
            ->set_is_sortable(true)
            ->add_callback([helper::class, 'modname']);

        $columns[] = (new column('source', new lang_string('field', 'report_extdeps'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.source")
            ->set_is_sortable(true);

        $columns[] = (new column('edit', new lang_string('edit'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$alias}.cmid, {$alias}.courseid, {$alias}.sectionid, {$alias}.source, {$alias}.itemid, "
                . "{$alias}.modname")
            ->add_callback(static function ($value, stdClass $row): string {
                return helper::edit_link($row);
            });

        $columns[] = (new column('cmvisible', new lang_string('activityvisible', 'report_extdeps'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_BOOLEAN)
            ->add_field("{$alias}.cmvisible")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'boolean_as_text']);

        $columns[] = (new column('timefound', new lang_string('timefound', 'report_extdeps'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$alias}.timefound")
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
        $alias = $this->get_table_alias('tool_extdeps_occurrence');
        $filters = [];

        $filters[] = (new filter(
            select::class,
            'modname',
            new lang_string('activitytype', 'report_extdeps'),
            $this->get_entity_name(),
            "{$alias}.modname"
        ))
            ->add_joins($this->get_joins())
            ->set_options_callback([helper::class, 'modname_options']);

        $filters[] = (new filter(
            boolean_select::class,
            'cmvisible',
            new lang_string('activityvisible', 'report_extdeps'),
            $this->get_entity_name(),
            "{$alias}.cmvisible"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timefound',
            new lang_string('timefound', 'report_extdeps'),
            $this->get_entity_name(),
            "{$alias}.timefound"
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }
}
