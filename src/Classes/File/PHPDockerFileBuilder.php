<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\File;

use Loom\Spinner\Classes\Config\Config;
use Loom\Spinner\Classes\File\Interface\DataPathInterface;
use Symfony\Component\Console\Input\InputInterface;

class PHPDockerFileBuilder extends AbstractFileBuilder
{
    /**
     * @throws \Exception
     */
    public function __construct(Config $config)
    {
        $projectPhpFpmDockerfile = $config->getFilePath('projectPhpFpmDockerfile');

        if (!$projectPhpFpmDockerfile instanceof SpinnerFilePath) {
            throw new \Exception('Project PHP-FPM Dockerfile not found');
        }

        return parent::__construct($projectPhpFpmDockerfile, $config);
    }

    /**
     * @throws \Exception
     */
    public function build(InputInterface $input): PHPDockerFileBuilder
    {
        $this->setInitialContent();

        $this->content = str_replace('${PHP_VERSION}', (string) $this->config->getPhpVersion($input), $this->content);

        if ($this->config->isNodeEnabled($input)) {
            $this->content .= "\r\n\r\n" . file_get_contents($this->config->getFilePaths()->get('nodeDockerfileTemplate')->getAbsolutePath());
            $this->content = str_replace('${NODE_VERSION}', (string) $this->config->getNodeVersion($input), $this->content);
        }

        return $this;
    }

    private function setInitialContent(): void
    {
        $this->content = file_get_contents(
            $this->config->getFilePaths()->get(DataPathInterface::CONFIG_PHP_FPM_DOCKERFILE)->getAbsolutePath()
        );
    }
}