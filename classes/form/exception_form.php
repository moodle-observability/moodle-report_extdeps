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
 * Accepts a reported failure as a false positive, or removes that acceptance.
 *
 * @package    report_extdeps
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class exception_form extends \moodleform {
    /**
     * Form definition.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;
        $exists = !empty($this->_customdata['exists']);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        if ($exists) {
            $mform->addElement('hidden', 'clear', 1);
            $mform->setType('clear', PARAM_BOOL);
            $this->add_action_buttons(false, get_string('clearexception', 'report_extdeps'));
            return;
        }
        $mform->addElement('textarea', 'reason', get_string('exceptionreason', 'report_extdeps'), ['rows' => 3, 'cols' => 60]);
        $mform->setType('reason', PARAM_TEXT);
        $mform->addRule('reason', null, 'required', null, 'client');
        $mform->addRule('reason', get_string('maximumchars', '', 1000), 'maxlength', 1000, 'client');
        $this->add_action_buttons(false, get_string('markexception', 'report_extdeps'));
    }

    /**
     * The reason cannot be only blanks.
     *
     * @param array $data Data.
     * @param array $files Files.
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['clear']) && trim((string) ($data['reason'] ?? '')) === '') {
            $errors['reason'] = get_string('required');
        }
        return $errors;
    }
}
