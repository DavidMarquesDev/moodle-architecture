<?php
declare(strict_types=1);

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_hello_get_messages' => [
        'classname' => 'local_hello\\external\\get_messages',
        'description' => 'Retorna as mensagens do usuário informado ou do usuário autenticado.',
        'type' => 'read',
        'ajax' => true,
    ],
];
