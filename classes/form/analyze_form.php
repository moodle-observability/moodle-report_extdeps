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

namespace report_extdeps\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Asks for an on demand analysis of a course, a provider or the whole site.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class analyze_form extends \moodleform {
    /**
     * Form definition.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('select', 'scope', get_string('scope', 'report_extdeps'), [
            'provider' => get_string('scope_provider', 'report_extdeps'),
            'course' => get_string('scope_course', 'report_extdeps'),
            'site' => get_string('scope_site', 'report_extdeps'),
        ]);
        $mform->setType('scope', PARAM_ALPHA);

        $mform->addElement(
            'select',
            'provider',
            get_string('provider', 'report_extdeps'),
            \report_extdeps\local\helper::provider_options()
        );
        $mform->setType('provider', PARAM_ALPHANUMEXT);
        $mform->hideIf('provider', 'scope', 'neq', 'provider');

        $mform->addElement('course', 'courseid', get_string('course'));
        $mform->hideIf('courseid', 'scope', 'neq', 'course');

        $mform->addElement('advcheckbox', 'dryrun', get_string('dryrun', 'report_extdeps'));
        $mform->addHelpButton('dryrun', 'dryrun', 'report_extdeps');
        $mform->addElement('advcheckbox', 'strict', get_string('strict', 'report_extdeps'));
        $mform->addHelpButton('strict', 'strict', 'report_extdeps');

        $this->add_action_buttons(false, get_string('runanalysis', 'report_extdeps'));
    }

    /**
     * A course is needed when the scope is a course.
     *
     * @param array $data Data.
     * @param array $files Files.
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (($data['scope'] ?? '') === 'course' && empty($data['courseid'])) {
            $errors['courseid'] = get_string('required');
        }
        return $errors;
    }
}
