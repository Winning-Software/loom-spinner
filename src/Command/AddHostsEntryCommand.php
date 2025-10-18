<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'env:hosts:add', description: 'Add hosts entry')]
class AddHostsEntryCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'The name of the host entry to add.'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $projectName = $input->getArgument('name');

        if (function_exists('posix_getuid') && posix_getuid() !== 0) {
            $scriptPath = $_SERVER['PHP_SELF'] ?? $_SERVER['SCRIPT_FILENAME'] ?? null;
            
            if ($scriptPath && file_exists($scriptPath)) {
                $style->warning('This command requires elevated privileges.');
                $style->note('Attempting to re-run with sudo...');
                
                $command = sprintf(
                    'sudo -E env PATH="%s" %s %s %s',
                    getenv('PATH'),
                    PHP_BINARY,
                    escapeshellarg($scriptPath),
                    escapeshellarg('env:hosts:add') . ' ' . escapeshellarg($projectName)
                );
                
                passthru($command, $exitCode);
                return $exitCode;
            }
            
            $style->error('Please re-run this command with sudo.');
            return Command::FAILURE;
        }

        $hostsFile = '/etc/hosts';
        $sectionHeader = "# Spinner Managed Environments";
        $entry = sprintf("127.0.0.1 %s.app", $projectName);

        $hostsContents = file_get_contents($hostsFile);
        if ($hostsContents === false) {
            $style->error('Could not read hosts file.');
            return Command::FAILURE;
        }

        if (!str_contains($hostsContents, $sectionHeader)) {
            $hostsContents .= PHP_EOL . $sectionHeader . PHP_EOL;
        }

        $lines = explode(PHP_EOL, $hostsContents);
        $sectionIndex = array_search($sectionHeader, $lines, true);
        $existingEntries = [];
        for ($i = $sectionIndex + 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '' || str_starts_with($line, '#')) {
                break;
            }
            $existingEntries[] = $line;
        }

        if (in_array($entry, $existingEntries, true)) {
            $style->info("Hosts entry already exists for {$projectName}.app");
            return Command::SUCCESS;
        }

        array_splice($lines, $sectionIndex + 1, 0, $entry);
        file_put_contents($hostsFile, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX);

        $style->success("Added hosts entry for {$projectName}.app");

        return Command::SUCCESS;
    }
}
