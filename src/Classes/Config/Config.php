<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\Config;

use Loom\Spinner\Classes\Collection\FilePathCollection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;

class Config
{
    public function __construct(private readonly FilePathCollection $filePaths)
    {
    }

    /**
     * @throws \Exception
     */
    protected function getEnvironmentOption(string $service, string $option): mixed
    {
        if ($this->filePaths->get('projectCustomConfig')?->exists()) {
            $config = Yaml::parseFile(
                $this->filePaths->get('projectCustomConfig')->getAbsolutePath()
            )['options']['environment'] ?? null;

            if ($config) {
                if (isset($config[$service][$option])) {
                    return $config[$service][$option];
                }
            }
        }

        return $this->getDefaultConfig()['environment'][$service][$option] ?? null;
    }

    /**
     * @throws \Exception
     */
    protected function getDefaultConfig()
    {
        return Yaml::parseFile($this->filePaths->get('defaultSpinnerConfig')?->getAbsolutePath())['options']
            ?? null;
    }

    /**
     * @throws \Exception
     */
    public function getDefaultPhpVersionArgument(): ?float
    {
        return $this->getEnvironmentOption('php', 'version');
    }

    /**
     * @throws \Exception
     */
    protected function isNodeEnabled(InputInterface $input): bool
    {
        if ($input->getOption('node-disabled')) {
            return true;
        }

        return $this->getEnvironmentOption('node', 'enabled');
    }
}