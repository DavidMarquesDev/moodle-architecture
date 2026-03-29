<?php
declare(strict_types=1);

require_once(__DIR__ . '/../../config.php');

require_login();

$context = \context::instance_by_id(\context_system::instance()->id);
require_capability('local/hello:view', $context);

$PAGE->set_url('/local/hello/index.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_hello'));
$PAGE->set_heading(get_string('pluginname', 'local_hello'));

$message = optional_param('message', '', PARAM_TEXT);
$save = optional_param('save', 0, PARAM_BOOL);
$error = '';

if ($save) {
    require_sesskey();
    $message = trim($message);

    if ($message === '') {
        $error = get_string('emptymessage', 'local_hello');
    } else {
        $record = new \stdClass();
        $record->userid = $USER->id;
        $record->message = $message;
        $record->timecreated = time();
        $DB->insert_record('local_hello_messages', $record);

        redirect('/local/hello/index.php', get_string('messagesaved', 'local_hello'));
    }
}

$records = $DB->get_records(
    'local_hello_messages',
    ['userid' => $USER->id],
    'timecreated DESC',
    'id, message, timecreated'
);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('welcome', 'local_hello'));
echo $OUTPUT->box(get_string('greeting', 'local_hello', fullname($USER)));

if ($error !== '') {
    echo $OUTPUT->notification($error, 'notifyproblem');
}

echo '<form method="post" action="">';
echo '<input type="hidden" name="sesskey" value="' . sesskey() . '">';
echo '<div><label for="id_message">' . get_string('messagefield', 'local_hello') . '</label></div>';
echo '<div><textarea id="id_message" name="message" rows="4" cols="80"></textarea></div>';
echo '<div><button type="submit" name="save" value="1">' . get_string('savebutton', 'local_hello') . '</button></div>';
echo '</form>';

echo $OUTPUT->heading(get_string('mymessages', 'local_hello'), 3);

if ($records === []) {
    echo $OUTPUT->box(get_string('nomessages', 'local_hello'));
} else {
    echo '<ul>';
    foreach ($records as $record) {
        echo '<li>' . s(userdate((int) $record->timecreated)) . ' - ' . s($record->message) . '</li>';
    }
    echo '</ul>';
}

echo $OUTPUT->footer();
