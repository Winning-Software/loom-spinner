<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\Config;

use Loom\Spinner\Classes\OS\System;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;

class Config
{
    private string $spinnerRootPath;
    private string $configDirectory;
    private string $dataDirectory;

    public function __construct(string $projectName = '', private readonly ?string $projectWorkPath = null)
    {
        $this->spinnerRootPath = dirname(__DIR__, 3);
        $this->configDirectory = sprintf('%s/config', $this->spinnerRootPath);
        $this->dataDirectory = sprintf('%s/.spinner/environments/%s', $this->getHomeDirectory(), $projectName);
    }

    public function getDataDirectory(): string
    {
        return $this->dataDirectory;
    }

    public function getConfigFilePath(string $fileName): string
    {
        return sprintf('%s/%s', $this->configDirectory, $fileName);
    }

    public function getConfigFileContents(string $fileName): string|null
    {
        if (file_exists($path = sprintf('%s/%s', $this->configDirectory, $fileName))) {
            return file_get_contents($path);
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

    /**
     * @return array<string, array<string, boolean|float|int|string>>|null
     */
    public function getProjectCustomConfig(): ?array
    {
        if ($this->projectWorkPath && file_exists($configFilePath = $this->getConfigYamlPath())) {
            return Yaml::parseFile($configFilePath)['options']['environment'];
        }

        return null;
    }

    /**
     * @return array<string, array<string, boolean|float|int|string>>|null
     */
    protected function getDefaultConfig(): ?array
    {
        return Yaml::parseFile($this->getConfigYamlPath())['options']['environment']
            ?? null;
    }

    private function getHomeDirectory(): string
    {
        if ($home = getenv('HOME')) {
            return $home;
        }

        if ((new System())->isWindows()) {
            return getenv('USERPROFILE') ?: getenv('HOMEDRIVE') . getenv('HOMEPATH');
        }

        throw new \RuntimeException('Unable to determine home directory.');
    }

    private function getConfigYamlPath(): string
    {
        return sprintf('%s/spinner.yaml', $this->configDirectory);
    }
}
