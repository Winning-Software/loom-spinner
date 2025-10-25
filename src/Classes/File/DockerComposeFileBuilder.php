<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\File;

use Loom\Spinner\Classes\Config\Config;
use Symfony\Component\Console\Input\InputInterface;

class DockerComposeFileBuilder extends AbstractFileBuilder
{
    /**
     * @param Config $config
     * @param array<string, int> $ports
     *
     * @throws \Exception
     */
    public function __construct(Config $config, private readonly array $ports)
    {
        parent::__construct($config->getDataDirectory() . '/docker-compose.yaml', $config);
    }

    /**
     * @throws \Exception
     */
    public function build(InputInterface $input): DockerComposeFileBuilder
    {
        if (!$content = $this->config->getConfigFileContents('php.yaml')) {
            throw new \Exception('Could not locate default PHP configuration file.');
        }

        $this->content = $content;

        if ($this->config->isServerEnabled($input)) {
            $this->addNginxConfig();
        }

        if ($this->config->isDatabaseEnabled($input) && $driver = $this->config->getDatabaseDriver($input)) {
            $databaseDriver = strtolower($driver);

            if (in_array($databaseDriver, ['sqlite3', 'sqlite'])) {
                $this->addSqliteDatabaseConfig();
            }

            if ($databaseDriver === 'mysql') {
                $this->addMysqlDatabaseConfig();
            }

            $this->addNetworks();
        }

        return $this;
    }

    /**
     * @throws \Exception
     */
    private function addNginxConfig(): void
    {
        if (!$nginxContent = $this->config->getConfigFileContents('nginx.yaml')) {
            throw new \Exception('Could not locate the default Nginx configuration file.');
        }

        $this->content .= str_replace(
            'services:',
            '',
            $nginxContent
        );
        $this->content = str_replace(
            './nginx/conf.d',
            $this->config->getDataDirectory() . '/nginx/conf.d',
            $this->content
        );
    }

    /**
     * @throws \Exception
     */
    private function addSqliteDatabaseConfig(): void
    {
        if (!$sqlLiteConfig = $this->config->getConfigFileContents('sqlite.yaml')) {
            throw new \Exception('Could not locate the default SQLite configuration file.');
        }

        $sqlLiteConfig = str_replace('volumes:', '', $sqlLiteConfig);
        $this->content .= $sqlLiteConfig;
    }

    /**
     * @throws \Exception
     */
    private function addMysqlDatabaseConfig(): void
    {
        if (!$mysqlConfig = $this->config->getConfigFileContents('mysql.yaml')) {
            throw new \Exception('Could not locate the default MySQL configuration file.');
        }

        $rootPassword = $this->config->getEnvironmentOption('database', 'rootPassword');

        if (!is_string($rootPassword) || $rootPassword === '') {
            throw new \Exception('The root database password is invalid.');
        }

        $mysqlConfig = str_replace('services:', '', $mysqlConfig);
        $mysqlConfig = str_replace('${ROOT_PASSWORD}', $rootPassword, $mysqlConfig);
        $mysqlConfig = str_replace('${DATABASE_PORT}', (string) $this->ports['database'], $mysqlConfig);
        $this->content .= $mysqlConfig;
    }

    /**
     * @throws \Exception
     */
    private function addNetworks(): void
    {
        if (!$networksConfig = $this->config->getConfigFileContents('network.yaml')) {
            throw new \Exception('Could not locate the default network configuration file.');
        }

        $this->addNewLine();
        $this->content .= $networksConfig;
    }
}
