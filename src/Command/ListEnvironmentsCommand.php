<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Dotenv\Dotenv;
use Loom\Spinner\Classes\Config\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(name: 'env:list', description: 'List all available development environments')]
class ListEnvironmentsCommand extends AbstractSpinnerCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (parent::execute($input, $output)) {
            return Command::FAILURE;
        }

        $this->config = new Config();
        $dataPath = $this->config->getDataDirectory();
        $outputData = [];

        if (!file_exists($dataPath)) {
            $this->style->info('No environments configured.');

            return Command::SUCCESS;
        }

        foreach (scandir($dataPath) as $file) {
            if (in_array($file, ['.', '..'])) continue;

            $projectPath = sprintf('%s%s', $dataPath, $file);
            $projectConfig = Dotenv::createMutable($projectPath);
            $projectConfig->load();

            $projectDockerCompose = Yaml::parseFile(sprintf('%s/docker-compose.yaml', $projectPath));

            if (!is_array($projectDockerCompose)) continue;

            $serviceConfig = $projectDockerCompose['services'] ?? null;

            if (!is_array($serviceConfig)) {
                continue;
            }

            $volumes = array_key_exists('nginx', $serviceConfig) && is_array($serviceConfig['nginx'])
                ? $serviceConfig['nginx']['volumes']
                : [];
            $usesSqlite = false;

            if (is_array($volumes) && count($volumes)) {
                foreach ($volumes as $volume) {
                    if (!is_string($volume)) continue;

                    if (str_contains($volume, 'sqlite')) {
                        $usesSqlite = true;
                    }
                }
            }

            $databasePort = is_string($_ENV['DATABASE_PORT']) ? $_ENV['DATABASE_PORT'] : 3306;
            $outputData[] = [
                'Environment' => $file,
                'PHP Version' => $_ENV['PHP_VERSION'],
                'Server' => array_key_exists('nginx', $serviceConfig)
                    ? '<fg=green>Nginx</>'
                    : '<fg=red>N/A</>',
                'Database' => array_key_exists('mysql', $serviceConfig)
                    ? sprintf('MySQL (%d)', $databasePort)
                    : ($usesSqlite? 'SQLite' : '<fg=red>N/A</>'),
                'Status' => $this->system->isDockerContainerRunning($file)
                    ? '<fg=green>On</>'
                    : '<fg=red>Off</>',
                'URL' => array_key_exists('nginx', $serviceConfig)
                    ? sprintf('<fg=green>https://%s.app</>', $file)
                    : '<fg=red>N/A</>',
            ];
        }

        if (!count($outputData)) {
            $this->style->info('No environments configured.');
        } else {
            $this->style->table(['Environment', 'PHP Version', 'Server', 'Database', 'Status', 'URL'], $outputData);
        }

        return Command::SUCCESS;
    }
}