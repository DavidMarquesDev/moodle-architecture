<?php
declare(strict_types=1);

namespace local_hello\Application\Service;

use local_hello\Application\DTO\MessageDeleteDTO;
use local_hello\Application\DTO\MessageFilterDTO;
use local_hello\Application\DTO\MessageSaveDTO;
use local_hello\Domain\Repository\MessageRepositoryInterface;

class MessageService
{
    private MessageRepositoryInterface $repository;

    public function __construct(MessageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function normalizeSort(string $sort): string
    {
        if (!in_array($sort, ['recent', 'oldest'], true)) {
            return 'recent';
        }

        return $sort;
    }

    public function normalizePerPage(int $requestedperpage, array $allowedperpages): int
    {
        if (!in_array($requestedperpage, $allowedperpages, true)) {
            return 10;
        }

        return $requestedperpage;
    }

    public function handleDelete(MessageDeleteDTO $dto): array
    {
        $record = $this->repository->findByIdForUser($dto->getMessageId(), $dto->getUserId());
        if ($record === null) {
            return ['errorkey' => 'messageownererror'];
        }

        $this->repository->deleteForUser($dto->getMessageId(), $dto->getUserId());

        return ['successkey' => 'messagedeleted'];
    }

    public function handleSave(MessageSaveDTO $dto, int $maxmessages): array
    {
        $message = trim($dto->getMessage());

        if ($message === '') {
            return ['errorkey' => 'emptymessage'];
        }

        if ($dto->getMessageId() > 0) {
            $updated = $this->repository->updateForUser($dto->getMessageId(), $dto->getUserId(), $message);
            if (!$updated) {
                return ['errorkey' => 'messageownererror'];
            }

            return ['successkey' => 'messageupdated'];
        }

        $currentcount = $this->repository->countByUser($dto->getUserId());
        if ($currentcount >= $maxmessages) {
            return ['errorkey' => 'messagelimitreached', 'errordata' => $maxmessages];
        }

        $this->repository->createForUser($dto->getUserId(), $message, time());

        return ['successkey' => 'messagesaved'];
    }

    public function loadEditMessage(int $messageid, int $userid): array
    {
        $record = $this->repository->findByIdForUser($messageid, $userid);
        if ($record === null) {
            return ['message' => '', 'messageid' => 0, 'errorkey' => 'messageownererror'];
        }

        return ['message' => (string) $record->message, 'messageid' => (int) $record->id];
    }

    public function listMessages(int $userid, MessageFilterDTO $filter): array
    {
        $totalrecords = $this->repository->countFilteredByUser($userid, $filter->getQuery());
        $maxpage = 0;

        if ($totalrecords > 0) {
            $maxpage = (int) ceil($totalrecords / $filter->getPerPage()) - 1;
        }

        $page = min(max(0, $filter->getPage()), $maxpage);
        $offset = $page * $filter->getPerPage();
        $order = $filter->getSort() === 'oldest' ? 'timecreated ASC' : 'timecreated DESC';

        $records = $this->repository->getFilteredByUser(
            $userid,
            $filter->getQuery(),
            $order,
            $offset,
            $filter->getPerPage()
        );

        return [
            'records' => $records,
            'totalrecords' => $totalrecords,
            'maxpage' => $maxpage,
            'page' => $page,
            'perpage' => $filter->getPerPage(),
        ];
    }
}
