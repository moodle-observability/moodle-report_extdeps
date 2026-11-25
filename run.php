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
 * Result of an on demand analysis: progress, verdicts and the evidence of each one.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use report_extdeps\local\helper;

require(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);

$run = $DB->get_record('tool_extdeps_run', ['id' => $id], '*', MUST_EXIST);
$context = \tool_extdeps\external\get_run::run_context($run);
if ($context instanceof context_course) {
    require_login($context->instanceid);
} else {
    require_login();
}
require_capability('tool/extdeps:analyze', $context);

$items = helper::run_items($run);

if ($download !== '') {
    $columns = [
        'provider' => get_string('provider', 'report_extdeps'),
        'key' => get_string('resourcekey', 'report_extdeps'),
        'verdict' => get_string('verdict', 'report_extdeps'),
        'status' => get_string('status', 'report_extdeps'),
        'method' => get_string('method', 'report_extdeps'),
        'http' => 'HTTP',
        'evidence' => get_string('evidence', 'report_extdeps'),
    ];
    $rows = array_map(fn(array $item): array => [
        'provider' => $item['provider'],
        'key' => $item['resourcekey'],
        'verdict' => $item['verdict'],
        'status' => $item['status'],
        'method' => $item['method'],
        'http' => $item['http'],
        'evidence' => $item['evidence'],
    ], $items);
    \core\dataformat::download_data('extdeps-run-' . $run->id, $download, $columns, new ArrayIterator($rows));
    die;
}

$url = new moodle_url('/report/extdeps/run.php', ['id' => $run->id]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('runresult', 'report_extdeps'));
$PAGE->set_heading(get_string('runresult', 'report_extdeps'));
$pending = in_array($run->status, ['queued', 'running'], true);
if ($pending) {
    // Shows the progress until the ad hoc task finishes.
    $PAGE->set_periodic_refresh_delay(10);
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('report_extdeps/run', helper::run_context_data($run, $items, $pending));
if (!$pending && $items) {
    echo $OUTPUT->download_dataformat_selector(
        get_string('download'),
        $url->out_omit_querystring(),
        'download',
        ['id' => $run->id]
    );
}
echo $OUTPUT->footer();
