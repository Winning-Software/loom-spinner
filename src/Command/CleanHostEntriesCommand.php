<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'env:hosts:clean', description: 'Clean up orphaned hosts entries')]
class CleanHostEntriesCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Show what would be removed without actually removing it.'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');

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
                    escapeshellarg('env:hosts:clean') . ($dryRun ? ' --dry-run' : '')
                );
                
                passthru($command, $exitCode);
                return $exitCode;
            }
            
            $style->error('Please re-run this command with sudo.');
            return Command::FAILURE;
        }

        $hostsFile = '/etc/hosts';
        $sectionHeader = '# Spinner Managed Environments';
        $environmentsDir = $this->getEnvironmentsDirectory();

        $existingProjects = [];
        if (is_dir($environmentsDir)) {
            $existingProjects = array_filter(
                scandir($environmentsDir) ?: [],
                fn($dir) => $dir !== '.' && $dir !== '..' && is_dir($environmentsDir . '/' . $dir)
            );
        }

        $hostsContents = file_get_contents($hostsFile);
        if ($hostsContents === false) {
            $style->error('Could not read hosts file.');
            return Command::FAILURE;
        }

        if (!str_contains($hostsContents, $sectionHeader)) {
            $style->info('No Spinner managed hosts entries found.');
            return Command::SUCCESS;
        }

        $lines = explode(PHP_EOL, $hostsContents);
        $sectionIndex = array_search($sectionHeader, $lines, true);
        
        if ($sectionIndex === false) {
            $style->info('No Spinner managed hosts entries found.');
            return Command::SUCCESS;
        }

        $entriesToRemove = [];
        $endOfSection = count($lines);
        
        for ($i = $sectionIndex + 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);

            if ($line === '' || (str_starts_with($line, '#') && $line !== $sectionHeader)) {
                $endOfSection = $i;
                break;
            }

            if (preg_match('/^127\.0\.0\.1\s+(\S+)\.app$/', $line, $matches)) {
                $projectName = $matches[1];

                if (!in_array($projectName, $existingProjects, true)) {
                    $entriesToRemove[] = [
                        'index' => $i,
                        'line' => $line,
                        'project' => $projectName,
                    ];
                }
            }
        }

        if (empty($entriesToRemove)) {
            $style->success('No orphaned hosts entries found. Everything is clean!');
            return Command::SUCCESS;
        }

        $style->section('Orphaned Hosts Entries Found:');
        $tableData = array_map(
            fn($entry) => [$entry['project'] . '.app', $entry['line']],
            $entriesToRemove
        );
        $style->table(['Project', 'Entry'], $tableData);

        if ($dryRun) {
            $style->note(sprintf(
                'Dry run mode: %d %s would be removed.',
                count($entriesToRemove),
                count($entriesToRemove) === 1 ? 'entry' : 'entries'
            ));
            return Command::SUCCESS;
        }

        foreach (array_reverse($entriesToRemove) as $entry) {
            unset($lines[$entry['index']]);
        }

        $lines = array_values($lines);

        $sectionIndex = array_search($sectionHeader, $lines, true);
        if ($sectionIndex !== false) {
            $hasEntries = false;
            for ($i = $sectionIndex + 1; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if ($line === '' || str_starts_with($line, '#')) {
                    break;
                }
                if (!empty($line)) {
                    $hasEntries = true;
                    break;
                }
            }

            if (!$hasEntries) {
                unset($lines[$sectionIndex]);
                $lines = array_values($lines);
            }
        }

        if (file_put_contents($hostsFile,implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX) === false) {
            $style->error('Failed to write to hosts file.');
            return Command::FAILURE;
        }

        $style->success(sprintf(
            'Successfully removed %d orphaned %s.',
            count($entriesToRemove),
            count($entriesToRemove) === 1 ? 'entry' : 'entries'
        ));

        return Command::SUCCESS;
    }

    private function getEnvironmentsDirectory(): string
    {
        $home = getenv('HOME');
        if (!$home) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $home = getenv('USERPROFILE') ?: getenv('HOMEDRIVE') . getenv('HOMEPATH');
            }
        }
        
        return $home . '/.spinner/environments';
    }
}