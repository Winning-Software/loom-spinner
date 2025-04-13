<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\Config;

use Loom\Spinner\Classes\Collection\FilePathCollection;
use Loom\Spinner\Classes\File\Interface\DataPathInterface;
use Loom\Spinner\Classes\File\SpinnerFilePath;
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
        if ($input->getOption('node-disabled')) {
            return false;
        }

        return $this->getEnvironmentOption('node', 'enabled');
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
            'config' => new SpinnerFilePath('config'),
            'defaultSpinnerConfig' => new SpinnerFilePath('config/spinner.yaml'),
            'envTemplate' => new SpinnerFilePath('config/.template.env'),
            'data' => new SpinnerFilePath('data'),
            'phpYamlTemplate' => new SpinnerFilePath('config/php.yaml'),
            'phpFpmDataDirectory' => new SpinnerFilePath('config/php-fpm'),
            DataPathInterface::CONFIG_PHP_FPM_DIRECTORY => new SpinnerFilePath(DataPathInterface::CONFIG_PHP_FPM_DIRECTORY),
            'nodeDockerfileTemplate' => new SpinnerFilePath('config/php-fpm/Node.Dockerfile'),
        ]);
    }
}