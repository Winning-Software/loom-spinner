<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\File;

use Loom\Spinner\Classes\Config\Config;
use Symfony\Component\Console\Input\InputInterface;

class ProxyFileBuilder extends AbstractFileBuilder
{
    /**
     * @param InputInterface $input
     *
     * @throws \Exception
     *
     * @return AbstractFileBuilder
     */
    public function build(InputInterface $input): AbstractFileBuilder
    {
        if (!$content = $this->config->getConfigFileContents('reverse-proxy.yaml')) {
            throw new \Exception('Could not locate the default reverse proxy configuration file');
        }

        $this->content = $content;

        return $this;
    }
}
