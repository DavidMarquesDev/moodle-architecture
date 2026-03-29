<?php
declare(strict_types=1);

namespace local_hello\form;

use moodleform;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class message_form extends moodleform
{
    public function definition(): void
    {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $savebuttonlabel = (string) ($customdata['savebuttonlabel'] ?? get_string('savebutton', 'local_hello'));

        $mform->addElement('hidden', 'messageid');
        $mform->setType('messageid', PARAM_INT);

        $mform->addElement('hidden', 'page');
        $mform->setType('page', PARAM_INT);

        $mform->addElement('hidden', 'q');
        $mform->setType('q', PARAM_TEXT);

        $mform->addElement('hidden', 'sort');
        $mform->setType('sort', PARAM_ALPHA);

        $mform->addElement('hidden', 'perpage');
        $mform->setType('perpage', PARAM_INT);

        $mform->addElement('textarea', 'message', get_string('messagefield', 'local_hello'), ['rows' => 4, 'cols' => 80]);
        $mform->setType('message', PARAM_TEXT);
        $mform->addRule('message', null, 'required', null, 'client');

        $buttons = [];
        $buttons[] = $mform->createElement('submit', 'save', $savebuttonlabel);
        $buttons[] = $mform->createElement('cancel', 'cancel', get_string('cancelbutton', 'local_hello'));
        $mform->addGroup($buttons, 'formbuttons', '', [' '], false);
    }
}
