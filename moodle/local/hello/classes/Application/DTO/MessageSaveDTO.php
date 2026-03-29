<?php
declare(strict_types=1);

namespace local_hello\Application\DTO;

class MessageSaveDTO
{
    private int $userid;
    private int $messageid;
    private string $message;

    public function __construct(int $userid, int $messageid, string $message)
    {
        $this->userid = $userid;
        $this->messageid = $messageid;
        $this->message = $message;
    }

    public function getUserId(): int
    {
        return $this->userid;
    }

    public function getMessageId(): int
    {
        return $this->messageid;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
