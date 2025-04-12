<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'spin', description: 'Spin up a new development environment')]
class SpinCommand extends AbstractSpinnerCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'The name for your Docker container.')
            ->addArgument('path', InputArgument::REQUIRED, 'The absolute path to your projects root directory.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!parent::execute($input, $output)) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}