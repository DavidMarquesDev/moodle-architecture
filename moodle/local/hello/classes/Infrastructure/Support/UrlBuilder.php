<?php
declare(strict_types=1);

namespace local_hello\Infrastructure\Support;

class UrlBuilder
{
    public function build(string $baseurl, array $params = []): string
    {
        $querystring = http_build_query($params);

        if ($querystring === '') {
            return $baseurl;
        }

        return $baseurl . '?' . $querystring;
    }
}
