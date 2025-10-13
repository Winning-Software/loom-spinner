<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\File;

use Loom\Spinner\Classes\Config\Config;
use Symfony\Component\Console\Input\InputInterface;

class DockerComposeFileBuilder extends AbstractFileBuilder
{
    /**
     * @throws \Exception
     */
    public function __construct(Config $config, private array $ports)
    {
        return parent::__construct($config->getDataDirectory() . '/docker-compose.yaml', $config);
    }

    /**
     * @throws \Exception
     */
    public function build(InputInterface $input): DockerComposeFileBuilder
    {
        $this->content = $this->config->getConfigFileContents('php.yaml');

        if ($this->config->isServerEnabled($input)) {
            $this->addNginxConfig();
        }
        if ($this->config->isDatabaseEnabled($input)) {
            $databaseDriver = strtolower($this->config->getDatabaseDriver($input));

            if (in_array($databaseDriver, ['sqlite3', 'sqlite'])) {
                $this->addSqliteDatabaseConfig();
            }

            if ($databaseDriver === 'mysql') {
                $this->addMysqlDatabaseConfig();
            }
        }

        return $this;
    }

    private function addNginxConfig(): void
    {
        $this->content .= str_replace(
            'services:',
            '',
            $this->config->getConfigFileContents('nginx.yaml')
        );
        $this->content = str_replace(
            './nginx/conf.d',
            $this->config->getConfigFilePath('nginx/conf.d'),
            $this->content
        );
    }

    private function addSqliteDatabaseConfig(): void
    {
        $sqlLiteConfig = $this->config->getConfigFileContents('sqlite.yaml');
        $sqlLiteConfig = str_replace('volumes:', '', $sqlLiteConfig);
        $this->content .= $sqlLiteConfig;
    }

    private function addMysqlDatabaseConfig(): void
    {
        $mysqlConfig = str_replace('services:', '', $this->config->getConfigFileContents('mysql.yaml'));
        $mysqlConfig = str_replace('${ROOT_PASSWORD}', $this->config->getEnvironmentOption('database', 'rootPassword'), $mysqlConfig);
        $mysqlConfig = str_replace('${DATABASE_PORT}', (string) $this->ports['database'], $mysqlConfig);
        $this->content.= $mysqlConfig;
    }
}
