<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\File;

use Loom\Spinner\Classes\Config\Config;
use Symfony\Component\Console\Input\InputInterface;

class NginxDockerFileBuilder extends AbstractFileBuilder
{
    /**
     * @throws \Exception
     */
    public function __construct(Config $config)
    {
        parent::__construct($config->getDataDirectory() . '/nginx/Dockerfile', $config);
    }

    /**
     * @param InputInterface $input
     *
     * @throws \Exception
     *
     * @return AbstractFileBuilder
     */
    public function build(InputInterface $input): AbstractFileBuilder
    {
        if (!$nginxDockerfileContent = $this->config->getConfigFileContents('nginx/Dockerfile')) {
            throw new \Exception('Could not locate the Nginx Dockerfile.');
        }

        $this->content = $nginxDockerfileContent;

        return $this;
    }
}
