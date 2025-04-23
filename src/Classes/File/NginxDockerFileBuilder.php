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
        return parent::__construct($config->getDataDirectory() . '/nginx/Dockerfile', $config);
    }

    public function build(InputInterface $input): AbstractFileBuilder
    {
        $this->content = $this->config->getConfigFileContents('nginx/Dockerfile');

        return $this;
    }
}