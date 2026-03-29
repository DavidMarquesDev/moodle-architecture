<?php
declare(strict_types=1);

namespace local_hello\Application\DTO;

class MessageFilterDTO
{
    private int $page;
    private int $perpage;
    private string $query;
    private string $sort;

    public function __construct(int $page, int $perpage, string $query, string $sort)
    {
        $this->page = $page;
        $this->perpage = $perpage;
        $this->query = $query;
        $this->sort = $sort;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perpage;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getSort(): string
    {
        return $this->sort;
    }
}
