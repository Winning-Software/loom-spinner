<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\File;

use Loom\Spinner\Classes\Config\Config;
use Loom\Spinner\Classes\File\Interface\DataPathInterface;
use Loom\Utility\FilePath\FilePath;
use Symfony\Component\Console\Input\InputInterface;

class NginxDockerFileBuilder extends AbstractFileBuilder
{
    /**
     * @throws \Exception
     */
    public function __construct(Config $config)
    {
        $projectNginxDockerfilePath = $config->getFilePath('projectNginxDockerfile');

        if (!$projectNginxDockerfilePath instanceof FilePath) {
            throw new \Exception('Project PHP-FPM Dockerfile not found');
        }

        return parent::__construct($projectNginxDockerfilePath, $config);
    }

    public function build(InputInterface $input): AbstractFileBuilder
    {
        $this->content = file_get_contents(
            $this->config->getFilePaths()
                ->get(DataPathInterface::CONFIG_NGINX_DOCKERFILE)
                ->getAbsolutePath()
        );

        return $this;
    }
}