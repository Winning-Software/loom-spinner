<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Loom\Spinner\Classes\Config\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'spin:down', description: 'Destroy a development environment')]
class DestroyCommand extends AbstractSpinnerCommand
{
    protected function configure(): void
    {
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'The name of the project (as used when running spin:up).'
        );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (parent::execute($input, $output)) {
            return Command::FAILURE;
        }

        $projectName = $input->getArgument('name');
        $this->config = new Config($projectName);

        if (!file_exists($this->config->getDataDirectory())) {
            $this->style->error('No project found with the provided name.');

            return Command::FAILURE;
        }

        try {
            passthru($this->buildDockerComposeCommand('down -v', false, false));
            $this->recursiveRmdir($this->config->getDataDirectory());
        } catch (\Exception $exception) {
            $this->style->error('An error occurred while destroying the project: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function recursiveRmdir(string $dir): void
    {
        if (!is_dir($dir)) return;

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }
}
