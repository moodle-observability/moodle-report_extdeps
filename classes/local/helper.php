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

namespace report_extdeps\local;

use tool_extdeps\local\registry;
use tool_extdeps\local\status;

/**
 * Formatting and lookups shared by the pages and the report sources.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class helper {
    /**
     * Name of a provider.
     *
     * @param string|null $provider Provider name (youtube, vimeo...).
     * @return string
     */
    public static function provider_name(?string $provider): string {
        if ($provider === null || $provider === '') {
            return '';
        }
        $component = 'extdepsprovider_' . $provider;
        if (get_string_manager()->string_exists('pluginname', $component)) {
            return get_string('pluginname', $component);
        }
        return s($provider);
    }

    /**
     * Name of a status.
     *
     * @param string|null $status Status.
     * @return string
     */
    public static function status_name(?string $status): string {
        if ($status === null || $status === '') {
            return '';
        }
        if (get_string_manager()->string_exists('status_' . $status, 'tool_extdeps')) {
            return get_string('status_' . $status, 'tool_extdeps');
        }
        return s($status);
    }

    /**
     * What to do about a broken dependency.
     *
     * @param string $provider Provider name.
     * @param string $status Confirmed status.
     * @return string
     */
    public static function advice(string $provider, string $status): string {
        if (!status::is_broken($status)) {
            return '';
        }
        $instance = registry::get($provider);
        return $instance ? $instance->get_advice($status) : '';
    }

    /**
     * Providers for a filter.
     *
     * @return array<string, string>
     */
    public static function provider_options(): array {
        $options = [];
        foreach (array_keys(registry::all()) as $name) {
            $options[$name] = self::provider_name($name);
        }
        \core_collator::asort($options);
        return $options;
    }

    /**
     * Statuses for a filter.
     *
     * @return array<string, string>
     */
    public static function status_options(): array {
        $options = [];
        foreach (status::all() as $status) {
            $options[$status] = self::status_name($status);
        }
        return $options;
    }

    /**
     * Broken statuses as an SQL list. They are fixed identifiers of tool_extdeps, never user input.
     *
     * @return string
     */
    public static function broken_in(): string {
        $quoted = array_map(
            fn(string $s): string => "'" . clean_param($s, PARAM_ALPHANUMEXT) . "'",
            status::broken()
        );
        return implode(', ', $quoted);
    }

    /**
     * Totals of occurrences and resources per provider and confirmed status, optionally of one course.
     *
     * @param int $courseid Course, or 0 for the site.
     * @return array<int, array{provider: string, status: string, resources: int, occurrences: int}>
     */
    public static function totals(int $courseid = 0): array {
        global $DB;
        $where = $courseid ? 'WHERE o.courseid = :courseid' : '';
        $sql = "SELECT r.provider, r.confirmedstatus, COUNT(DISTINCT r.id) AS resources, COUNT(o.id) AS occurrences
                  FROM {tool_extdeps_occurrence} o
                  JOIN {tool_extdeps_resource} r ON r.id = o.resourceid
                  {$where}
              GROUP BY r.provider, r.confirmedstatus
              ORDER BY r.provider, r.confirmedstatus";
        $totals = [];
        foreach ($DB->get_recordset_sql($sql, ['courseid' => $courseid]) as $row) {
            $totals[] = [
                'provider' => $row->provider,
                'status' => $row->confirmedstatus,
                'resources' => (int) $row->resources,
                'occurrences' => (int) $row->occurrences,
            ];
        }
        return $totals;
    }

    /**
     * Data for the summary cards: one card per provider with its broken and available counts.
     *
     * @param int $courseid Course, or 0 for the site.
     * @return array
     */
    public static function cards(int $courseid = 0): array {
        $cards = [];
        foreach (self::totals($courseid) as $row) {
            $card = &$cards[$row['provider']];
            $card ??= ['provider' => self::provider_name($row['provider']), 'ok' => 0, 'broken' => 0, 'pending' => 0,
                'statuses' => []];
            if ($row['status'] === status::OK) {
                $card['ok'] += $row['occurrences'];
            } else if (status::is_broken($row['status'])) {
                $card['broken'] += $row['occurrences'];
                $card['statuses'][] = ['name' => self::status_name($row['status']), 'count' => $row['occurrences'],
                    'resources' => $row['resources'], 'advice' => self::advice($row['provider'], $row['status'])];
            } else {
                $card['pending'] += $row['occurrences'];
            }
            unset($card);
        }
        foreach (array_keys($cards) as $key) {
            $cards[$key]['hasbroken'] = $cards[$key]['broken'] > 0;
        }
        return array_values($cards);
    }

    /**
     * Name of a section of a course.
     *
     * @param int $courseid Course.
     * @param int $sectionnum Section number.
     * @return string
     */
    public static function section_name(int $courseid, int $sectionnum): string {
        try {
            return get_section_name($courseid, $sectionnum);
        } catch (\Throwable $e) {
            return (string) $sectionnum;
        }
    }

    /**
     * Name of an activity type.
     *
     * @param string|null $modname Module name.
     * @return string
     */
    public static function modname(?string $modname): string {
        if ($modname === null || $modname === '') {
            return get_string('course');
        }
        if (get_string_manager()->string_exists('modulename', 'mod_' . $modname)) {
            return get_string('modulename', 'mod_' . $modname);
        }
        return s($modname);
    }

    /**
     * Activity types used by the occurrences, for a filter.
     *
     * @return array<string, string>
     */
    public static function modname_options(): array {
        global $DB;
        $options = [];
        $modnames = $DB->get_fieldset_sql('SELECT DISTINCT modname FROM {tool_extdeps_occurrence} WHERE modname IS NOT NULL');
        foreach ($modnames as $modname) {
            $options[$modname] = self::modname($modname);
        }
        \core_collator::asort($options);
        return $options;
    }

    /**
     * Course module of an occurrence, when it still exists.
     *
     * @param int $courseid Course.
     * @param int|null $cmid Course module.
     * @return \cm_info|null
     */
    private static function cm(int $courseid, ?int $cmid): ?\cm_info {
        if (!$cmid) {
            return null;
        }
        try {
            return get_fast_modinfo($courseid)->get_cm($cmid);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Link to the place where the dependency is used (activity, section or course summary).
     *
     * @param \stdClass $row Row with cmid, courseid, modname and source.
     * @return string
     */
    public static function place_link(\stdClass $row): string {
        $cm = self::cm((int) $row->courseid, $row->cmid ? (int) $row->cmid : null);
        if ($cm) {
            $url = $cm->url ?? new \moodle_url('/course/view.php', ['id' => $row->courseid]);
            return \html_writer::link($url, $cm->get_formatted_name());
        }
        if ($row->source === 'course.summary') {
            $url = new \moodle_url('/course/view.php', ['id' => $row->courseid]);
            return \html_writer::link($url, get_string('coursesummary'));
        }
        if ($row->source === 'course_sections.summary') {
            return get_string('sectionsummary', 'report_extdeps');
        }
        return s((string) $row->source);
    }

    /**
     * Link to the page where the dependency can be fixed.
     *
     * @param \stdClass $row Row with cmid, courseid, sectionid, source, itemid and modname.
     * @return string
     */
    public static function edit_link(\stdClass $row): string {
        $url = self::edit_url($row);
        if (!$url) {
            return '';
        }
        return \html_writer::link($url, get_string('edit'), ['class' => 'btn btn-sm btn-outline-secondary']);
    }

    /**
     * Address of the page where the dependency can be fixed.
     *
     * @param \stdClass $row Row with cmid, courseid, sectionid, source, itemid and modname.
     * @return \moodle_url|null
     */
    public static function edit_url(\stdClass $row): ?\moodle_url {
        $source = (string) $row->source;
        if ($source === 'course.summary') {
            return new \moodle_url('/course/edit.php', ['id' => $row->courseid]);
        }
        if ($source === 'course_sections.summary') {
            return $row->sectionid ? new \moodle_url('/course/editsection.php', ['id' => $row->sectionid]) : null;
        }
        if (!$row->cmid) {
            return null;
        }
        if ($source === 'book_chapters.content' && $row->itemid) {
            return new \moodle_url('/mod/book/edit.php', ['cmid' => $row->cmid, 'id' => $row->itemid]);
        }
        if ($source === 'lesson_pages.contents' && $row->itemid) {
            return new \moodle_url('/mod/lesson/editpage.php', ['id' => $row->cmid, 'pageid' => $row->itemid, 'edit' => 1]);
        }
        if (str_starts_with($source, 'question.') && $row->modname === 'quiz') {
            return new \moodle_url('/mod/quiz/edit.php', ['cmid' => $row->cmid]);
        }
        return new \moodle_url('/course/modedit.php', ['update' => $row->cmid, 'return' => 1]);
    }

    /**
     * Items of an on demand analysis, with the main parts of the evidence.
     *
     * @param \stdClass $run Run.
     * @return array<int, array>
     */
    public static function run_items(\stdClass $run): array {
        global $DB;
        $items = [];
        foreach ($DB->get_records('tool_extdeps_runitem', ['runid' => $run->id], 'verdict, provider, resourcekey') as $item) {
            $evidence = json_decode((string) $item->evidence, true) ?: [];
            $items[] = [
                'resourceid' => (int) $item->resourceid,
                'provider' => (string) $item->provider,
                'providername' => self::provider_name($item->provider),
                'resourcekey' => (string) $item->resourcekey,
                'verdict' => (string) $item->verdict,
                'verdictname' => self::verdict_name((string) $item->verdict),
                'status' => (string) $item->status,
                'statusname' => self::status_name($item->status),
                'method' => (string) ($evidence['method'] ?? ''),
                'http' => isset($evidence['http']) ? (string) $evidence['http'] : '',
                'reason' => (string) ($evidence['reason'] ?? ($evidence['playabilityStatus'] ?? '')),
                'evidence' => (string) $item->evidence,
                'broken' => in_array($item->verdict, ['still_broken', 'suspected'], true),
                'ok' => in_array($item->verdict, ['confirmed_ok', 'removed_from_content'], true),
            ];
        }
        return $items;
    }

    /**
     * Name of a verdict.
     *
     * @param string $verdict Verdict.
     * @return string
     */
    public static function verdict_name(string $verdict): string {
        if (get_string_manager()->string_exists('verdict_' . $verdict, 'tool_extdeps')) {
            return get_string('verdict_' . $verdict, 'tool_extdeps');
        }
        return s($verdict);
    }

    /**
     * Data of the template of an on demand analysis.
     *
     * @param \stdClass $run Run.
     * @param array $items Items from {@see run_items()}.
     * @param bool $pending Whether the run is still queued or running.
     * @return array
     */
    public static function run_context_data(\stdClass $run, array $items, bool $pending): array {
        global $DB;
        $summary = json_decode((string) $run->summary, true) ?: [];
        $http = $summary['http'] ?? null;
        unset($summary['http'], $summary['error']);
        $counters = [];
        foreach ($summary as $verdict => $count) {
            if ($count) {
                $counters[] = ['name' => self::verdict_name((string) $verdict), 'count' => (int) $count];
            }
        }
        $courseid = 0;
        if ($run->scope === 'course') {
            $courseid = (int) $run->scopeid;
        } else if ($run->scope === 'cm') {
            $courseid = (int) $DB->get_field('course_modules', 'course', ['id' => $run->scopeid]);
        }
        $total = max(1, (int) $run->total);
        foreach ($items as &$item) {
            $item['historyurl'] = $item['resourceid'] > 0
                ? (new \moodle_url('/report/extdeps/resource.php', ['id' => $item['resourceid'], 'courseid' => $courseid]))
                    ->out(false)
                : '';
        }
        unset($item);
        return [
            'id' => (int) $run->id,
            'pending' => $pending,
            'failed' => $run->status === 'failed',
            'statusname' => get_string('runstatus_' . $run->status, 'report_extdeps'),
            'scope' => get_string('scope_' . $run->scope, 'report_extdeps'),
            'dryrun' => (bool) $run->dryrun,
            'strict' => (bool) $run->strict,
            'processed' => (int) $run->processed,
            'total' => (int) $run->total,
            'percent' => $pending ? (int) round(100 * (int) $run->processed / $total) : 100,
            'counters' => $counters,
            'hascounters' => !empty($counters),
            'http' => $http ? get_string('httpstats', 'report_extdeps', (object) $http) : '',
            'items' => $items,
            'hasitems' => !empty($items),
            'backurl' => $courseid ? (new \moodle_url('/report/extdeps/course.php', ['id' => $courseid]))->out(false)
                : (new \moodle_url('/report/extdeps/index.php'))->out(false),
        ];
    }

    /**
     * Data of the template of a resource.
     *
     * @param \stdClass $resource Resource.
     * @param int $courseid Course the page was opened from, or 0 for the site.
     * @param \moodle_url $backurl Where to go back.
     * @return array
     */
    public static function resource_data(\stdClass $resource, int $courseid, \moodle_url $backurl): array {
        global $DB;
        $params = ['resourceid' => $resource->id];
        $where = 'o.resourceid = :resourceid';
        if ($courseid) {
            $where .= ' AND o.courseid = :courseid';
            $params['courseid'] = $courseid;
        }
        $places = [];
        $sql = "SELECT o.id, o.courseid, o.cmid, o.sectionid, o.source, o.itemid, o.modname, c.fullname
                  FROM {tool_extdeps_occurrence} o
                  JOIN {course} c ON c.id = o.courseid
                 WHERE {$where}
              ORDER BY c.fullname, o.cmid";
        foreach ($DB->get_records_sql($sql, $params, 0, 200) as $row) {
            $context = \context_course::instance((int) $row->courseid);
            $editurl = self::edit_url($row);
            $places[] = [
                'course' => format_string($row->fullname, true, ['context' => $context]),
                'courseurl' => (new \moodle_url('/report/extdeps/course.php', ['id' => $row->courseid]))->out(false),
                'place' => self::place_link($row),
                'source' => $row->source,
                'editurl' => $editurl ? $editurl->out(false) : '',
            ];
        }
        $history = [];
        $rows = $DB->get_records('tool_extdeps_history', ['resourceid' => $resource->id], 'timecreated DESC', '*', 0, 50);
        foreach ($rows as $row) {
            $history[] = [
                'time' => userdate((int) $row->timecreated),
                'old' => self::status_name($row->oldstatus),
                'new' => self::status_name($row->newstatus),
                'detail' => self::evidence_text((string) $row->detail),
            ];
        }
        $exception = $DB->get_record('tool_extdeps_exception', ['resourceid' => $resource->id]);
        $url = (string) $resource->url;
        return [
            'provider' => self::provider_name($resource->provider),
            'resourcekey' => $resource->resourcekey,
            'url' => clean_param($url, PARAM_URL),
            'status' => self::status_name($resource->confirmedstatus),
            'broken' => status::is_broken((string) $resource->confirmedstatus),
            'lastresult' => self::status_name($resource->status),
            'since' => $resource->timeconfirmed ? userdate((int) $resource->timeconfirmed) : '',
            'lastcheck' => $resource->lastcheck ? userdate((int) $resource->lastcheck) : '',
            'detail' => self::evidence_text((string) $resource->detail),
            'advice' => self::advice((string) $resource->provider, (string) $resource->confirmedstatus),
            'exception' => $exception ? [
                'reason' => s($exception->reason),
                'time' => userdate((int) $exception->timecreated),
            ] : null,
            'places' => $places,
            'hasplaces' => !empty($places),
            'history' => $history,
            'hashistory' => !empty($history),
            'backurl' => $backurl->out(false),
        ];
    }

    /**
     * Short text of an evidence (method, HTTP code and reason).
     *
     * @param string $json Evidence in JSON.
     * @return string
     */
    public static function evidence_text(string $json): string {
        $evidence = json_decode($json, true);
        if (!is_array($evidence)) {
            return '';
        }
        $parts = [];
        foreach (['method', 'http', 'player', 'reason', 'playabilityStatus', 'error'] as $key) {
            if (isset($evidence[$key]) && is_scalar($evidence[$key]) && $evidence[$key] !== '') {
                $parts[] = $key . ': ' . $evidence[$key];
            }
        }
        return implode(' · ', $parts);
    }

    /**
     * Link that switches between only the broken dependencies and all of them.
     *
     * @param \moodle_url $url Page.
     * @param bool $all Whether all are shown now.
     * @return array{url: string, label: string}
     */
    public static function toggle_link(\moodle_url $url, bool $all): array {
        return [
            'url' => (new \moodle_url($url, ['all' => $all ? 0 : 1]))->out(false),
            'label' => get_string($all ? 'showbroken' : 'showall', 'report_extdeps'),
        ];
    }
}
