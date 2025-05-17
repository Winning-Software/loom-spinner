<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\Config;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;

class Config
{
    private string $spinnerRootPath;
    private string $configDirectory;
    private string $dataDirectory;
    private ?string $projectWorkPath = null;

    public function __construct(string $projectName, ?string $projectWorkPath = null)
    {
        $projectName = empty($projectName) ? '' : $projectName;
        $this->spinnerRootPath = dirname(__DIR__, 3);
        $this->configDirectory = $this->spinnerRootPath . '/config';
        $this->dataDirectory = $this->spinnerRootPath . '/data/environments/' . $projectName;

        if ($projectWorkPath) {
            $this->projectWorkPath = $projectWorkPath;
        }
    }

    public function getDataDirectory(): string
    {
        return $this->dataDirectory;
    }

    public function getConfigFilePath(string $fileName): string
    {
        return $this->configDirectory . '/' . $fileName;
    }

    public function getConfigFileContents(string $fileName): string|null
    {
        if (file_exists($this->configDirectory . '/' . $fileName)) {
            return file_get_contents($this->configDirectory . '/' . $fileName);
        }

        return null;
    }

    public function projectDataExists(string $projectName): bool
    {
        return file_exists($this->dataDirectory . '/environments/' . $projectName);
    }

    public function getPhpVersion(InputInterface $input): float
    {
        if ($input->getOption('php')) {
            return (float) $input->getOption('php');
        }

        return (float) $this->getEnvironmentOption('php', 'version');
    }

    /**
     * @throws \Exception
     */
    public function isServerEnabled(InputInterface $input): bool
    {
        if ($input->getOption('disable-server')) {
            return false;
        }

        return $this->getEnvironmentOption('server', 'enabled');
    }

    /**
     * @throws \Exception
     */
    public function isXDebugEnabled(InputInterface $input): bool
    {
        if ($input->getOption('disable-xdebug')) {
            return false;
        }

        return $this->getEnvironmentOption('php', 'xdebug');
    }

    /**
     * @throws \Exception
     */
    public function isDatabaseEnabled(InputInterface $input): bool
    {
        if ($input->getOption('disable-database')) {
            return false;
        }

        return (bool) $this->getEnvironmentOption('database', 'enabled');
    }

    /**
     * @throws \Exception
     */
    public function getDatabaseDriver(InputInterface $input): ?string
    {
        if ($input->getOption('database')) {
            return (string) $input->getOption('database');
        }

        return (string) $this->getEnvironmentOption('database', 'driver');
    }

    /**
     * @throws \Exception
     */
    public function getNodeVersion(InputInterface $input): ?int
    {
        if ($input->getOption('node')) {
            return (int) $input->getOption('node');
        }

        return (int) $this->getEnvironmentOption('node','version');
    }

    public function getEnvironmentOption(string $service, string $option): mixed
    {
        $projectCustomConfig = $this->getProjectCustomConfig();

        if ($projectCustomConfig) {
            return $projectCustomConfig[$service][$option] ?? $this->getDefaultConfig()[$service][$option] ?? null;
        }

        return $this->getDefaultConfig()[$service][$option] ?? null;
    }

    public function getProjectCustomConfig(): ?array
    {
        if ($this->projectWorkPath && file_exists($this->projectWorkPath . '/spinner.yaml')) {
            return Yaml::parseFile($this->projectWorkPath . '/spinner.yaml')['options']['environment'];
        }

        return null;
    }

    protected function getDefaultConfig(): ?array
    {
        return Yaml::parseFile($this->configDirectory . '/spinner.yaml')['options']['environment']
            ?? null;
    }
}