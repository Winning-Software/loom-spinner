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

        $this->config = new Config('');
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

            $volumes = $projectDockerCompose['services']['nginx']['volumes'] ?? [];
            $usesSqlite = false;

            foreach ($volumes as $volume) {
                if (str_contains($volume, 'sqlite')) {
                    $usesSqlite = true;
                }
            }

            $outputData[] = [
                'Environment' => $file,
                'PHP Version' => $_ENV['PHP_VERSION'],
                'Server' => array_key_exists('nginx', $projectDockerCompose['services'])
                    ? '<fg=green>Nginx</>'
                    : '<fg=red>N/A</>',
                'Database' => array_key_exists('mysql', $projectDockerCompose['services'])
                    ? 'MySQL'
                    : ($usesSqlite? 'SQLite' : '<fg=red>N/A</>'),
                'Running' => $this->system->isDockerContainerRunning($file)
                    ? '<fg=green>On</>'
                    : '<fg=red>Off</>',
                'URL' => array_key_exists('nginx', $projectDockerCompose['services'])
                    ? sprintf('<fg=green>http://localhost:%s</>', $_ENV['SERVER_PORT'])
                    : '<fg=red>N/A</>',
            ];
        }

        $this->style->table(['Environment', 'PHP Version', 'Server', 'Database', 'Running', 'URL'], $outputData);

        return Command::SUCCESS;
    }
}