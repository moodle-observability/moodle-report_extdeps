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
 * Strings of report_extdeps (en).
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activitytype'] = 'Activity type';
$string['activityvisible'] = 'Activity visible';
$string['advice'] = 'What to do';
$string['alloccurrences'] = 'All occurrences';
$string['brokencount'] = '{$a} broken';
$string['brokenoccurrences'] = 'Broken occurrences';
$string['brokenresources'] = 'Broken dependencies';
$string['clearexception'] = 'Remove the false positive';
$string['courseintro'] = 'Videos, packages and other external content used by this course. Broken ones show what to do and a link to edit the place where they are used.';
$string['coursesreport'] = 'Courses';
$string['datasource'] = 'External dependencies';
$string['downloadcourses'] = 'external-dependencies-courses';
$string['downloadoccurrences'] = 'external-dependencies';
$string['dryrun'] = 'Dry run';
$string['dryrun_help'] = 'Reports only: no situation changes and no alert is sent.';
$string['entitycoursesummary'] = 'External dependencies of the course';
$string['entityoccurrence'] = 'Occurrence';
$string['entityresource'] = 'External dependency';
$string['evidence'] = 'Evidence';
$string['exceptionactive'] = 'Accepted as a false positive:';
$string['exceptioncleared'] = 'The false positive was removed.';
$string['exceptionmarked'] = 'Accepted as a false positive. It will no longer send alerts until the content changes.';
$string['exceptionreason'] = 'Why it is a false positive';
$string['extdeps:view'] = 'See the external dependencies of the site';
$string['extdeps:viewcourse'] = 'See the external dependencies of a course';
$string['falsepositive'] = 'False positive';
$string['falsepositive_help'] = 'If the content works for the students although it is reported as broken, accept it as a false positive. It goes on being checked, but sends no alerts.';
$string['field'] = 'Field';
$string['from'] = 'From';
$string['history'] = 'History';
$string['httpstats'] = '{$a->requests} requests, {$a->seconds} s waiting for the providers, slowest {$a->maxseconds} s';
$string['lastcheck'] = 'Last check';
$string['lastresult'] = 'Last result';
$string['lastscan'] = 'Last scan';
$string['markexception'] = 'Accept as false positive';
$string['method'] = 'Method';
$string['nocourses'] = 'No course uses external dependencies.';
$string['nohistory'] = 'No change of situation yet.';
$string['nooccurrences'] = 'No external dependency found.';
$string['noplaces'] = 'Not used in any course.';
$string['nothingfound'] = 'Nothing found yet.';
$string['occurrences'] = 'Occurrences';
$string['occurrencesreport'] = 'External dependencies';
$string['okcount'] = '{$a} available';
$string['onlybroken'] = 'Broken';
$string['pendingcount'] = '{$a} not checked';
$string['pendinginfo'] = 'The analysis runs in the background; this page refreshes by itself.';
$string['places'] = 'Where it is used';
$string['pluginname'] = 'External dependencies';
$string['privacy:metadata'] = 'The External dependencies report stores no personal data; it shows the data of tool_extdeps.';
$string['progress'] = 'Progress';
$string['provider'] = 'Provider';
$string['resourcekey'] = 'Identifier';
$string['resourcetitle'] = 'External dependency';
$string['runanalysis'] = 'Run analysis';
$string['runanalysis_help'] = 'Checks now the external dependencies of a course, a provider or the whole site, in the background. The result shows the verdict and the evidence of each one.';
$string['runfailed'] = 'The analysis failed. See the task logs.';
$string['runresult'] = 'Analysis result';
$string['runstatus_done'] = 'Done';
$string['runstatus_failed'] = 'Failed';
$string['runstatus_queued'] = 'Queued';
$string['runstatus_running'] = 'Running';
$string['scope'] = 'Scope';
$string['scope_cm'] = 'Activity';
$string['scope_course'] = 'Course';
$string['scope_provider'] = 'Provider';
$string['scope_resource'] = 'Dependency';
$string['scope_site'] = 'Whole site';
$string['sectionsummary'] = 'Section summary';
$string['showall'] = 'Show the available ones too';
$string['showbroken'] = 'Show only the broken ones';
$string['since'] = 'Since';
$string['sincedate'] = 'since {$a}';
$string['status'] = 'Situation';
$string['strict'] = 'Strict mode';
$string['strict_help'] = 'Uses a second, independent method when the provider has one, and shows when they disagree.';
$string['timefound'] = 'Found on';
$string['to'] = 'To';
$string['url'] = 'Address';
$string['verdict'] = 'Verdict';
$string['verifycourse'] = 'Check this course now';
$string['verifynow'] = 'Check now';
