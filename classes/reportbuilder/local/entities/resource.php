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
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use lang_string;
use report_extdeps\local\helper;
use stdClass;

/**
 * External dependency (one distinct video, package or address) checked by tool_extdeps.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class resource extends base {
    /**
     * Tables used by the entity.
     *
     * @return string[]
     */
    protected function get_default_tables(): array {
        return ['tool_extdeps_resource'];
    }

    /**
     * Title of the entity.
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entityresource', 'report_extdeps');
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
     * Columns of the entity.
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $alias = $this->get_table_alias('tool_extdeps_resource');
        $columns = [];

        $columns[] = (new column('provider', new lang_string('provider', 'report_extdeps'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.provider")
            ->set_is_sortable(true)
            ->add_callback([helper::class, 'provider_name']);

        $columns[] = (new column('resourcekey', new lang_string('resourcekey', 'report_extdeps'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.resourcekey")
            ->set_is_sortable(true);

        $columns[] = (new column('url', new lang_string('url', 'report_extdeps'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$alias}.url, {$alias}.resourcekey")
            ->add_callback(static function (?string $url, stdClass $row): string {
                if ($url === null || $url === '') {
                    return s((string) $row->resourcekey);
                }
                // The identifier is shorter than the address; the address goes in the link and in the title.
                return \html_writer::link(
                    new \moodle_url($url),
                    s((string) $row->resourcekey),
                    ['target' => '_blank', 'rel' => 'noopener', 'title' => $url]
                );
            });

        $columns[] = (new column('status', new lang_string('status', 'report_extdeps'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.confirmedstatus")
            ->set_is_sortable(true)
            ->add_callback([helper::class, 'status_name']);

        $columns[] = (new column('lastresult', new lang_string('lastresult', 'report_extdeps'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.status")
            ->set_is_sortable(true)
            ->add_callback([helper::class, 'status_name']);

        $columns[] = (new column('timeconfirmed', new lang_string('since', 'report_extdeps'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$alias}.timeconfirmed")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate'], get_string('strftimedatetimeshort', 'core_langconfig'));

        $columns[] = (new column('lastcheck', new lang_string('lastcheck', 'report_extdeps'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$alias}.lastcheck")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate'], get_string('strftimedatetimeshort', 'core_langconfig'));

        $columns[] = (new column('advice', new lang_string('advice', 'report_extdeps'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$alias}.provider, {$alias}.confirmedstatus")
            ->add_callback(static function ($value, stdClass $row): string {
                return helper::advice((string) $row->provider, (string) $row->confirmedstatus);
            });

        return $columns;
    }

    /**
     * Filters of the entity.
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        $alias = $this->get_table_alias('tool_extdeps_resource');
        $filters = [];

        $filters[] = (new filter(
            select::class,
            'provider',
            new lang_string('provider', 'report_extdeps'),
            $this->get_entity_name(),
            "{$alias}.provider"
        ))
            ->add_joins($this->get_joins())
            ->set_options_callback([helper::class, 'provider_options']);

        $filters[] = (new filter(
            select::class,
            'status',
            new lang_string('status', 'report_extdeps'),
            $this->get_entity_name(),
            "{$alias}.confirmedstatus"
        ))
            ->add_joins($this->get_joins())
            ->set_options_callback([helper::class, 'status_options']);

        $broken = helper::broken_in();
        $filters[] = (new filter(
            select::class,
            'broken',
            new lang_string('onlybroken', 'report_extdeps'),
            $this->get_entity_name(),
            "CASE WHEN {$alias}.confirmedstatus IN ({$broken}) THEN 1 ELSE 0 END"
        ))
            ->add_joins($this->get_joins())
            ->set_options([1 => new lang_string('yes'), 0 => new lang_string('no')]);

        $filters[] = (new filter(
            text::class,
            'resourcekey',
            new lang_string('resourcekey', 'report_extdeps'),
            $this->get_entity_name(),
            "{$alias}.resourcekey"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timeconfirmed',
            new lang_string('since', 'report_extdeps'),
            $this->get_entity_name(),
            "{$alias}.timeconfirmed"
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }
}
