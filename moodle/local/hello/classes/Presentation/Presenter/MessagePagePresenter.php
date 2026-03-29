<?php
declare(strict_types=1);

namespace local_hello\Presentation\Presenter;

use local_hello\Infrastructure\Support\UrlBuilder;

class MessagePagePresenter
{
    private UrlBuilder $urlbuilder;

    public function __construct(UrlBuilder $urlbuilder)
    {
        $this->urlbuilder = $urlbuilder;
    }

    public function buildTemplateData(
        string $baseurl,
        array $baseparams,
        string $query,
        string $sort,
        int $perpage,
        array $allowedperpages,
        int $page,
        int $maxpage,
        int $totalrecords,
        int $messageid,
        string $message,
        array $records
    ): array {
        $sortoptions = [
            [
                'value' => 'recent',
                'label' => get_string('sortrecent', 'local_hello'),
                'selected' => $sort === 'recent',
            ],
            [
                'value' => 'oldest',
                'label' => get_string('sortoldest', 'local_hello'),
                'selected' => $sort === 'oldest',
            ],
        ];

        $perpageoptions = [];
        foreach ($allowedperpages as $value) {
            $perpageoptions[] = [
                'value' => $value,
                'selected' => $perpage === $value,
            ];
        }

        $recorditems = [];
        foreach ($records as $record) {
            $recorditems[] = [
                'time' => userdate((int) $record->timecreated),
                'message' => (string) $record->message,
                'editurl' => $this->urlbuilder->build($baseurl, array_merge($baseparams, ['editid' => (int) $record->id, 'page' => $page])),
                'deleteaction' => $baseurl,
                'sesskey' => sesskey(),
                'page' => $page,
                'deleteid' => (int) $record->id,
                'query' => $query,
                'sort' => $sort,
                'perpage' => $perpage,
            ];
        }

        return [
            'filteraction' => $baseurl,
            'clearfilterurl' => $baseurl,
            'query' => $query,
            'sort' => $sort,
            'sortoptions' => $sortoptions,
            'perpage' => $perpage,
            'perpageoptions' => $perpageoptions,
            'messageformaction' => $baseurl,
            'sesskey' => sesskey(),
            'page' => $page,
            'messageid' => $messageid,
            'message' => $message,
            'isediting' => $messageid > 0,
            'cancelurl' => $this->urlbuilder->build($baseurl, array_merge($baseparams, ['page' => $page])),
            'hasrecords' => $recorditems !== [],
            'records' => $recorditems,
            'haspagination' => $totalrecords > $perpage,
            'hasprevious' => $page > 0,
            'previousurl' => $this->urlbuilder->build($baseurl, array_merge($baseparams, ['page' => ($page - 1)])),
            'hasnext' => $page < $maxpage,
            'nexturl' => $this->urlbuilder->build($baseurl, array_merge($baseparams, ['page' => ($page + 1)])),
        ];
    }
}
