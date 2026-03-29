<?php
declare(strict_types=1);

namespace local_hello\Infrastructure\Factory;

use local_hello\Application\Service\MessageService;
use local_hello\Infrastructure\Repository\MessageRepository;

class MessageServiceFactory
{
    public static function create(\moodle_database $database): MessageService
    {
        $repository = new MessageRepository($database);

        return new MessageService($repository);
    }
}
