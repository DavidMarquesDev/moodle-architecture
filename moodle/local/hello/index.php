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

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('welcome', 'local_hello'));
echo $OUTPUT->box(get_string('greeting', 'local_hello', fullname($USER)));
echo $OUTPUT->footer();
