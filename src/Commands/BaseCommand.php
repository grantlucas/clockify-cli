<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

/**
 * Class BaseCommand
 * @author Grant Lucas
 */
class BaseCommand extends Command
{
    /**
     * Create Section
     *
     * Create a console output section. This is mainly to satisfy static
     * analysis as OutputInterface doesn't have the section method but
     * ConsoleOutput does. Since this app is primarily console based, we can
     * work around it here.
     *
     */
    protected function createSection($output): ConsoleSectionOutput
    {
        if ($output instanceof ConsoleOutput) {
            return $output->section();
        }

        throw new \InvalidArgumentException('This should never be hit');
    }
}
