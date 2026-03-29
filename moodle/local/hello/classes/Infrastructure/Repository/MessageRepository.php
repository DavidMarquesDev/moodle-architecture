<?php
declare(strict_types=1);

namespace local_hello\Infrastructure\Repository;

use local_hello\Domain\Repository\MessageRepositoryInterface;

class MessageRepository implements MessageRepositoryInterface
{
    private \moodle_database $database;

    public function __construct(\moodle_database $database)
    {
        $this->database = $database;
    }

    public function findByIdForUser(int $messageid, int $userid): ?\stdClass
    {
        $record = $this->database->get_record(
            'local_hello_messages',
            ['id' => $messageid, 'userid' => $userid],
            'id, userid, message, timecreated',
            IGNORE_MISSING
        );

        if ($record === false) {
            return null;
        }

        return $record;
    }

    public function createForUser(int $userid, string $message, int $timecreated): int
    {
        $record = new \stdClass();
        $record->userid = $userid;
        $record->message = $message;
        $record->timecreated = $timecreated;

        return (int) $this->database->insert_record('local_hello_messages', $record);
    }

    public function updateForUser(int $messageid, int $userid, string $message): bool
    {
        $record = $this->findByIdForUser($messageid, $userid);
        if ($record === null) {
            return false;
        }

        $record->message = $message;
        $this->database->update_record('local_hello_messages', $record);

        return true;
    }

    public function deleteForUser(int $messageid, int $userid): void
    {
        $this->database->delete_records('local_hello_messages', ['id' => $messageid, 'userid' => $userid]);
    }

    public function countByUser(int $userid): int
    {
        return $this->database->count_records('local_hello_messages', ['userid' => $userid]);
    }

    public function countFilteredByUser(int $userid, string $query): int
    {
        [$sql, $params] = $this->buildFilterSql($userid, $query);

        return $this->database->count_records_select('local_hello_messages', $sql, $params);
    }

    public function getFilteredByUser(
        int $userid,
        string $query,
        string $order,
        int $offset,
        int $perpage
    ): array {
        [$sql, $params] = $this->buildFilterSql($userid, $query);

        return $this->database->get_records_select(
            'local_hello_messages',
            $sql,
            $params,
            $order,
            'id, message, timecreated',
            $offset,
            $perpage
        );
    }

    private function buildFilterSql(int $userid, string $query): array
    {
        $sql = 'userid = :userid';
        $params = ['userid' => $userid];

        if ($query !== '') {
            $sql .= ' AND ' . $this->database->sql_like('message', ':query', false, false);
            $params['query'] = '%' . $query . '%';
        }

        return [$sql, $params];
    }
}
