<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\OS;

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

    public function isDockerContainerRunning(string $containerName): bool
    {
        $output = shell_exec("docker ps --filter 'name=$containerName' --format '{{.Names}}'");

        if (!$output) {
            return false;
        }

        if (in_array($containerName . '-php', explode("\n", $output))) {
            return true;
        }

        return false;
    }
}