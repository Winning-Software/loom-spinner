<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Loom\Spinner\Classes\Collection\FilePathCollection;
use Loom\Spinner\Classes\File\SpinnerFilePath;
use Loom\Spinner\Classes\OS\System;
use Loom\Spinner\Command\Interface\ConsoleCommandInterface;
use Loom\Utility\FilePath\FilePath;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AbstractSpinnerCommand extends Command implements ConsoleCommandInterface
{
    protected SymfonyStyle $style;
    protected System $system;
    protected FilePathCollection $filePaths;
    protected string $rootDirectory;

    public function __construct()
    {
        $this->rootDirectory = dirname(__DIR__, 2);
        $this->system = new System();
        $this->setFilePaths();

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setStyle($input, $output);

        if (!$this->system->isDockerEngineRunning()) {
            $this->style->error('It looks like the Docker Engine is not running. Please start it and try again.');

            return Command::FAILURE;
        }

        if ($input->hasArgument('path')) {
            $projectDirectory = new FilePath($input->getArgument('path'));

            if (!$projectDirectory->exists() || !$projectDirectory->isDirectory()) {
                $this->style->error('The provided path is not a valid directory.');

                return Command::FAILURE;
            }

            $this->filePaths->add($projectDirectory, 'project');
        }

        return Command::SUCCESS;
    }

    private function setStyle(InputInterface $input, OutputInterface $output): void
    {
        $this->style = new SymfonyStyle($input, $output);
    }

    private function setFilePaths(): void
    {
        $this->filePaths = new FilePathCollection([
            'config' => new SpinnerFilePath('config'),
        ]);
    }
}