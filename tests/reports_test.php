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

namespace report_extdeps;

use core_reportbuilder\system_report_factory;
use report_extdeps\local\helper;
use report_extdeps\systemreports\courses;
use report_extdeps\systemreports\occurrences;
use tool_extdeps\local\scanner;

/**
 * Tests of the system reports, the source of custom reports and the helper.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \report_extdeps\systemreports\courses
 * @covers     \report_extdeps\systemreports\occurrences
 * @covers     \report_extdeps\reportbuilder\datasource\dependencies
 * @covers     \report_extdeps\local\helper
 */
final class reports_test extends \advanced_testcase {
    /**
     * A course with two videos, one of them confirmed as private.
     *
     * @return array{0: \stdClass, 1: \stdClass}
     */
    private function course_with_videos(): array {
        global $DB;
        $course = $this->getDataGenerator()->create_course(['fullname' => 'Course with videos']);
        $page = $this->getDataGenerator()->create_module('page', [
            'course' => $course->id,
            'name' => 'Page with videos',
            'content' => '<iframe src="https://www.youtube.com/embed/AAAAAAAAAAA"></iframe>'
                . '<iframe src="https://www.youtube.com/embed/BBBBBBBBBBB"></iframe>',
        ]);
        scanner::scan_course($course->id);
        $DB->set_field('tool_extdeps_resource', 'confirmedstatus', 'private', ['resourcekey' => 'AAAAAAAAAAA']);
        $DB->set_field('tool_extdeps_resource', 'confirmedstatus', 'ok', ['resourcekey' => 'BBBBBBBBBBB']);
        return [$course, $page];
    }

    /**
     * Cards count occurrences per provider and situation.
     *
     * @return void
     */
    public function test_cards(): void {
        $this->resetAfterTest();
        [$course] = $this->course_with_videos();
        $cards = helper::cards($course->id);
        $this->assertCount(1, $cards);
        $this->assertSame(1, $cards[0]['broken']);
        $this->assertSame(1, $cards[0]['ok']);
        $this->assertTrue($cards[0]['hasbroken']);
        $this->assertSame([], helper::cards($this->getDataGenerator()->create_course()->id));
    }

    /**
     * The site report lists the course with its counters; the course report only its own occurrences.
     *
     * @return void
     */
    public function test_system_reports(): void {
        global $PAGE;
        $this->resetAfterTest();
        $this->setAdminUser();
        $PAGE->set_url(new \moodle_url('/report/extdeps/index.php'));
        [$course] = $this->course_with_videos();
        [$other] = $this->course_with_videos();

        $report = system_report_factory::create(courses::class, \context_system::instance());
        $output = $report->output();
        $this->assertStringContainsString('Course with videos', $output);

        $report = $this->course_report((int) $course->id);
        $output = $report->output();
        $this->assertStringContainsString('Page with videos', $output);
        $this->assertStringContainsString('modedit.php', $output);
        $this->assertStringContainsString('verify.php', $output);
        $this->assertNotEmpty($other);
    }

    /**
     * A teacher sees the course report and not the site report.
     *
     * @return void
     */
    public function test_can_view(): void {
        $this->resetAfterTest();
        [$course] = $this->course_with_videos();
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $this->setUser($teacher);

        $report = $this->course_report((int) $course->id);
        $report->require_can_view();

        $this->expectException(\core_reportbuilder\exception\report_access_exception::class);
        system_report_factory::create(courses::class, \context_system::instance())->require_can_view();
    }

    /**
     * A custom report from the source shows only broken dependencies by default.
     *
     * @return void
     */
    public function test_datasource(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->course_with_videos();

        /** @var \core_reportbuilder_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('core_reportbuilder');
        $report = $generator->create_report([
            'name' => 'Broken',
            'source' => \report_extdeps\reportbuilder\datasource\dependencies::class,
            'default' => 1,
        ]);
        $rows = $this->get_custom_report_rows((int) $report->get('id'));
        $this->assertCount(1, $rows);
        $this->assertStringContainsString('AAAAAAAAAAA', implode(' ', array_map('strval', $rows[0])));
    }

    /**
     * The occurrences report of a course.
     *
     * @param int $courseid Course.
     * @return occurrences
     */
    private function course_report(int $courseid): occurrences {
        return system_report_factory::create(
            occurrences::class,
            \context_course::instance($courseid),
            '',
            '',
            0,
            ['courseid' => $courseid]
        );
    }

    /**
     * Rows of a custom report.
     *
     * @param int $reportid Report id.
     * @return array
     */
    private function get_custom_report_rows(int $reportid): array {
        $table = \core_reportbuilder\table\custom_report_table_view::create($reportid);
        $table->setup();
        $table->query_db(100, false);
        $rows = [];
        foreach ($table->rawdata as $row) {
            $rows[] = array_values((array) $row);
        }
        return $rows;
    }

    /**
     * Places of edit of each source.
     *
     * @return void
     */
    public function test_edit_url(): void {
        $row = (object) ['source' => 'course.summary', 'courseid' => 5, 'cmid' => null, 'sectionid' => null,
            'itemid' => 5, 'modname' => null];
        $this->assertStringContainsString('/course/edit.php?id=5', helper::edit_url($row)->out(false));
        $row->source = 'course_sections.summary';
        $row->sectionid = 9;
        $this->assertStringContainsString('/course/editsection.php?id=9', helper::edit_url($row)->out(false));
        $row = (object) ['source' => 'question.questiontext', 'courseid' => 5, 'cmid' => 7, 'sectionid' => 1,
            'itemid' => 3, 'modname' => 'quiz'];
        $this->assertStringContainsString('/mod/quiz/edit.php?cmid=7', helper::edit_url($row)->out(false));
        $row->source = 'page.content';
        $row->modname = 'page';
        $this->assertStringContainsString('/course/modedit.php?update=7', helper::edit_url($row)->out(false));
        $row->source = 'book_chapters.content';
        $this->assertStringContainsString('/mod/book/edit.php?cmid=7&id=3', helper::edit_url($row)->out(false));
    }
}
