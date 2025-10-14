<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\File;

use Loom\Spinner\Classes\Config\Config;
use Symfony\Component\Console\Input\InputInterface;

class PHPDockerFileBuilder extends AbstractFileBuilder
{
    /**
     * @throws \Exception
     */
    public function __construct(Config $config)
    {
        parent::__construct($config->getDataDirectory() . '/php-fpm/Dockerfile', $config);
    }

    /**
     * @throws \Exception
     */
    public function build(InputInterface $input): PHPDockerFileBuilder
    {
        $this->setInitialContent();

        $this->content = str_replace('${PHP_VERSION}', (string) $this->config->getPhpVersion($input), $this->content);

        file_put_contents(
            $this->config->getDataDirectory() . '/php-fpm/opcache.ini',
            $this->config->getConfigFileContents('php-fpm/opcache.ini')
        );

        if ($this->config->isDatabaseEnabled($input)) {
            if (in_array($this->config->getDatabaseDriver($input), ['sqlite3', 'sqlite'])) {
                $this->addNewLine();
                $this->content .= $this->config->getConfigFileContents('php-fpm/Sqlite.Dockerfile');
            }

            if ($this->config->getDatabaseDriver($input) ==='mysql') {
                $this->addNewLine();
                $this->content .= $this->config->getConfigFileContents('php-fpm/MySQL.Dockerfile');
            }
        }

        $this->content = str_replace('${NODE_VERSION}', (string) $this->config->getNodeVersion($input), $this->content);

        if ($this->config->isXdebugEnabled($input)) {
            $this->addNewLine();
            $this->content .= $this->config->getConfigFileContents('php-fpm/XDebug.Dockerfile');
            file_put_contents(
                $this->config->getDataDirectory() . '/php-fpm/xdebug.ini',
                $this->config->getConfigFileContents('php-fpm/xdebug.ini')
            );
        }

        return $this;
    }

    /**
     * @throws \Exception
     */
    private function setInitialContent(): void
    {
        if (!$content = $this->config->getConfigFileContents('php-fpm/Dockerfile')) {
            throw new \Exception('Could not locate the PHP Dockerfile.');
        }

        $this->content = $content;
    }
}
