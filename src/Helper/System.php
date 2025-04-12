<?php

declare(strict_types=1);

namespace Loom\Spinner\Helper;

class System
{
    private string $operatingSystem;

    public function __construct()
    {
        $this->operatingSystem = PHP_OS_FAMILY;
    }

    public function getName(): string
    {
        return $this->operatingSystem;
    }

    public function isWindows(): bool
    {
        return $this->operatingSystem === 'Windows';
    }

    public function isMacOS(): bool
    {
        return $this->operatingSystem === 'Darwin';
    }

    public function isLinux(): bool
    {
        return $this->operatingSystem === 'Linux';
    }

    public function isDockerEngineRunning(): bool
    {
        exec('docker info 2>&1', $output, $dockerInfoError);

        return $dockerInfoError === 0;
    }
}