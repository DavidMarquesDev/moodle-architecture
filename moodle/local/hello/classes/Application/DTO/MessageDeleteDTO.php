<?php
declare(strict_types=1);

namespace local_hello\Application\DTO;

class MessageDeleteDTO
{
    private int $userid;
    private int $messageid;

    public function __construct(int $userid, int $messageid)
    {
        $this->userid = $userid;
        $this->messageid = $messageid;
    }

    public function getUserId(): int
    {
        return $this->userid;
    }

    public function getMessageId(): int
    {
        return $this->messageid;
    }
}
