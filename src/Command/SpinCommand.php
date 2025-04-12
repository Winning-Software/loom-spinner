<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'spin:up', description: 'Spin up a new development environment')]
class SpinCommand extends AbstractSpinnerCommand
{
    /**
     * @throws \Exception
     */
    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'The name for your Docker container.')
            ->addArgument('path', InputArgument::REQUIRED, 'The absolute path to your projects root directory.')
            ->addOption('php', null, InputOption::VALUE_REQUIRED, 'The PHP version to use (e.g., 8.0).', $this->getDefaultPhpVersion());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (parent::execute($input, $output)) {
            return Command::FAILURE;
        }

        if ($this->filePaths->get('projectData')->exists()) {
            $this->style->warning('Project data already exists. Skipping new build.');

            return Command::SUCCESS;
        }

        $this->style->success("Spinning up a new development environment...");

        mkdir($this->filePaths->get('projectData')->getProvidedPath(), 0777, true);

        file_put_contents(
            $this->filePaths->get('projectEnv')->getProvidedPath(),
            sprintf(
                file_get_contents($this->filePaths->get('envTemplate')->getAbsolutePath()),
                $this->filePaths->get('project')->getAbsolutePath(),
                $input->getArgument('name'),
                $input->getOption('php')
            )
        );

        return Command::SUCCESS;
    }
}