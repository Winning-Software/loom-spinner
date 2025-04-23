<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Loom\Spinner\Classes\Config\Config;
use Loom\Spinner\Classes\OS\System;
use Loom\Spinner\Command\Interface\ConsoleCommandInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AbstractSpinnerCommand extends Command implements ConsoleCommandInterface
{
    protected Config $config;
    protected SymfonyStyle $style;
    protected System $system;

    public function __construct()
    {
        $this->system = new System();

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setStyle($input, $output);

        $this->style->title('Loom Spinner');

        if (!$this->system->isDockerEngineRunning()) {
            $this->style->error('It looks like the Docker Engine is not running. Please start it and try again.');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function buildDockerComposeCommand(string $command, bool $daemon = true): string
    {
        return sprintf(
            'cd %s && docker compose --env-file=%s %s%s',
            $this->config->getDataDirectory(),
            $this->config->getDataDirectory() . '/.env',
            $command,
            $daemon ? ' -d' : ''
        );
    }

    private function setStyle(InputInterface $input, OutputInterface $output): void
    {
        $this->style = new SymfonyStyle($input, $output);
    }
}