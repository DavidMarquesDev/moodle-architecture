<?php
declare(strict_types=1);

use local_hello\Application\DTO\MessageDeleteDTO;
use local_hello\Application\DTO\MessageFilterDTO;
use local_hello\Application\DTO\MessageSaveDTO;
use local_hello\Infrastructure\Factory\MessageServiceFactory;
use local_hello\Infrastructure\Support\UrlBuilder;
use local_hello\Presentation\Presenter\MessagePagePresenter;

require_once(__DIR__ . '/../../config.php');

require_login();

$context = \context::instance_by_id(\context_system::instance()->id);
require_capability('local/hello:view', $context);

$messageservice = MessageServiceFactory::create($DB);
$baseurl = $CFG->wwwroot . '/local/hello/index.php';
$allowedperpages = [5, 10, 20, 50];
$maxmessages = 100;
$page = optional_param('page', 0, PARAM_INT);
$page = max(0, $page);
$query = trim(optional_param('q', '', PARAM_TEXT));
$sort = $messageservice->normalizeSort(optional_param('sort', 'recent', PARAM_ALPHA));
$requestedperpage = optional_param('perpage', 10, PARAM_INT);
$perpage = $messageservice->normalizePerPage($requestedperpage, $allowedperpages);
$message = optional_param('message', '', PARAM_TEXT);
$messageid = optional_param('messageid', 0, PARAM_INT);
$editid = optional_param('editid', 0, PARAM_INT);
$deleteid = optional_param('deleteid', 0, PARAM_INT);
$save = optional_param('save', 0, PARAM_BOOL);
$error = '';
$baseparams = ['q' => $query, 'sort' => $sort, 'perpage' => $perpage];
$urlbuilder = new UrlBuilder();
$presenter = new MessagePagePresenter($urlbuilder);

$PAGE->set_url('/local/hello/index.php', array_merge($baseparams, ['page' => $page]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_hello'));
$PAGE->set_heading(get_string('pluginname', 'local_hello'));

if ($deleteid > 0) {
    require_sesskey();
    $result = $messageservice->handleDelete(new MessageDeleteDTO((int) $USER->id, $deleteid));
    if (isset($result['errorkey'])) {
        $error = get_string($result['errorkey'], 'local_hello');
    } else {
        redirect(
            $urlbuilder->build($baseurl, array_merge($baseparams, ['page' => $page])),
            get_string($result['successkey'], 'local_hello')
        );
    }
}

if ($save) {
    require_sesskey();
    $result = $messageservice->handleSave(
        new MessageSaveDTO((int) $USER->id, $messageid, $message),
        $maxmessages
    );

    if (isset($result['errorkey'])) {
        if (isset($result['errordata'])) {
            $error = get_string($result['errorkey'], 'local_hello', $result['errordata']);
        } else {
            $error = get_string($result['errorkey'], 'local_hello');
        }
    } else {
        redirect(
            $urlbuilder->build($baseurl, array_merge($baseparams, ['page' => $page])),
            get_string($result['successkey'], 'local_hello')
        );
    }
}

if (!$save && $editid > 0) {
    $editpayload = $messageservice->loadEditMessage($editid, (int) $USER->id);
    if (isset($editpayload['errorkey'])) {
        $error = get_string($editpayload['errorkey'], 'local_hello');
    } else {
        $message = $editpayload['message'];
        $messageid = $editpayload['messageid'];
    }
}

$listpayload = $messageservice->listMessages(
    (int) $USER->id,
    new MessageFilterDTO($page, $perpage, $query, $sort)
);
$records = $listpayload['records'];
$totalrecords = $listpayload['totalrecords'];
$maxpage = $listpayload['maxpage'];
$page = $listpayload['page'];

$templatedata = $presenter->buildTemplateData(
    $baseurl,
    $baseparams,
    $query,
    $sort,
    $perpage,
    $allowedperpages,
    $page,
    $maxpage,
    $totalrecords,
    $messageid,
    $message,
    $records
);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('welcome', 'local_hello'));
echo $OUTPUT->box(get_string('greeting', 'local_hello', fullname($USER)));

if ($error !== '') {
    echo $OUTPUT->notification($error, 'notifyproblem');
}

echo $OUTPUT->render_from_template('local_hello/page', $templatedata);

echo $OUTPUT->footer();
