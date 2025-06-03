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

        foreach (scandir($dataPath) as $file) {
            if (in_array($file, ['.', '..'])) continue;

            $projectPath = sprintf('%s%s', $dataPath, $file);
            $projectConfig = Dotenv::createImmutable($projectPath);
            $projectConfig->load();

            $projectDockerCompose = Yaml::parseFile(sprintf('%s/docker-compose.yaml', $projectPath));

            $volumes = isset($projectDockerCompose['services']['nginx']['volumes'])
                ? $projectDockerCompose['services']['nginx']['volumes']
                : [];
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
            ];
        }

        $this->style->table(['Environment', 'PHP Version', 'Server', 'Database', 'Running'], $outputData);

        return Command::SUCCESS;
    }
}