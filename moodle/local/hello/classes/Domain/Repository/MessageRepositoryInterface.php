<?php
declare(strict_types=1);

namespace local_hello\Domain\Repository;

interface MessageRepositoryInterface
{
    public function findByIdForUser(int $messageid, int $userid): ?\stdClass;

    public function createForUser(int $userid, string $message, int $timecreated): int;

    public function updateForUser(int $messageid, int $userid, string $message): bool;

    public function deleteForUser(int $messageid, int $userid): void;

    public function countByUser(int $userid): int;

    public function countFilteredByUser(int $userid, string $query): int;

    public function getFilteredByUser(
        int $userid,
        string $query,
        string $order,
        int $offset,
        int $perpage
    ): array;
}
