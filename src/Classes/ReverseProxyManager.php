<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes;

use Symfony\Component\Console\Style\SymfonyStyle;

readonly class ReverseProxyManager
{
    private const string PROXY_CONTAINER_NAME = 'loom-spinner-reverse-proxy';

    public function __construct(private SymfonyStyle $style)
    {
    }

    public function startProxyContainerIfNotRunning(): void
    {
        $status = shell_exec(sprintf('docker inspect -f "{{.State.Running}}" %s 2>/dev/null', self::PROXY_CONTAINER_NAME));
        $status = is_string($status) ? trim($status) : null;

        if ($status !== 'true') {
            $this->style->info('Starting reverse proxy container...');
            exec(sprintf('docker start %s', self::PROXY_CONTAINER_NAME));
            $this->style->success('Reverse proxy is now running.');
        }
    }
}
