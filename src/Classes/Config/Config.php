<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\Config;

use Loom\Spinner\Classes\Collection\FilePathCollection;
use Loom\Spinner\Classes\File\Interface\DataPathInterface;
use Loom\Utility\FilePath\FilePath;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;

class Config
{
    private FilePathCollection $filePaths;

    public function __construct()
    {
        $this->setFilePaths();
    }

    public function getFilePaths(): FilePathCollection
    {
        return $this->filePaths;
    }

    public function getFilePath(string $key): ?FilePath
    {
        return $this->filePaths->get($key);
    }

    public function addFilePath(FilePath $filePath, string $key): void
    {
        $this->filePaths->add($filePath, $key);
    }

    /**
     * @throws \Exception
     */
    public function getPhpVersion(InputInterface $input): ?float
    {
        if ($input->getOption('php')) {
            return (float) $input->getOption('php');
        }

        return (float) $this->getEnvironmentOption('php', 'version');
    }

    /**
     * @throws \Exception
     */
    public function isNodeEnabled(InputInterface $input): bool
    {
        if ($input->getOption('disable-node')) {
            return false;
        }

        return $this->getEnvironmentOption('node', 'enabled');
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

        return $this->getEnvironmentOption('database', 'enabled');
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

    /**
     * @throws \Exception
     */
    public function getEnvironmentOption(string $service, string $option): mixed
    {
        $projectCustomConfig = $this->filePaths->get('projectCustomConfig');

        if ($projectCustomConfig->exists()) {
            $customConfig = Yaml::parseFile($projectCustomConfig->getAbsolutePath())['options']['environment']
                ?? null;

            if ($customConfig) {
                if (isset($customConfig[$service][$option])) {
                    return $customConfig[$service][$option];
                }
            }
        }

        return $this->getDefaultConfig()['environment'][$service][$option] ?? null;
    }

    /**
     * @throws \Exception
     */
    protected function getDefaultConfig(): ?array
    {
        return Yaml::parseFile($this->filePaths->get('defaultSpinnerConfig')?->getAbsolutePath())['options']
            ?? null;
    }

    private function setFilePaths(): void
    {
        $this->filePaths = new FilePathCollection([
            'config' => new FilePath('config'),
            'defaultSpinnerConfig' => new FilePath('config/spinner.yaml'),
            'envTemplate' => new FilePath('config/.template.env'),
            'data' => new FilePath('data'),
            'phpYamlTemplate' => new FilePath('config/php.yaml'),
            'nginxYamlTemplate' => new FilePath('config/nginx.yaml'),
            'phpFpmDataDirectory' => new FilePath('config/php-fpm'),
            DataPathInterface::CONFIG_PHP_FPM_DOCKERFILE => new FilePath(DataPathInterface::CONFIG_PHP_FPM_DOCKERFILE),
            DataPathInterface::CONFIG_NGINX_DOCKERFILE => new FilePath(DataPathInterface::CONFIG_NGINX_DOCKERFILE),
            'nodeDockerfileTemplate' => new FilePath('config/php-fpm/Node.Dockerfile'),
            'xdebugIniTemplate' => new FilePath('config/php-fpm/xdebug.ini'),
            'opcacheIniTemplate' => new FilePath('config/php-fpm/opcache.ini'),
            'xdebugDockerfileTemplate' => new FilePath('config/php-fpm/XDebug.Dockerfile'),
        ]);
    }
}