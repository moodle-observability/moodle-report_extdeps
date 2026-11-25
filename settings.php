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
 * Site administration entry of report_extdeps.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('reports', new admin_externalpage(
    'report_extdeps',
    new lang_string('pluginname', 'report_extdeps'),
    new moodle_url('/report/extdeps/index.php'),
    'report/extdeps:view'
));
$ADMIN->add('reports', new admin_externalpage(
    'report_extdeps_analyze',
    new lang_string('runanalysis', 'report_extdeps'),
    new moodle_url('/report/extdeps/analyze.php'),
    'tool/extdeps:manage',
    true
));

// The report has no settings of its own.
$settings = null;
