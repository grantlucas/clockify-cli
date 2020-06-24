<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use JDecool\Clockify\Client as ClockifyClient;

/**
 * Class TimerCurrentCommand
 * @author Grant Lucas
 */
class TimerCurrentCommand extends BaseCommand
{
    protected static $defaultName = 'timer:current';

    protected $clockifyClient;

    public function __construct(ClockifyClient $clockifyClient)
    {
        $this->clockifyClient = $clockifyClient;

        parent::__construct();
    }

    protected function configure()
    {
        // Basic description and help text
        $this
            ->setDescription('Current Timer')
            ->setHelp('Show the current timer with optional short format');

        // Input options
        $this
            ->addOption(
                'short',
                's',
                InputOption::VALUE_OPTIONAL,
                'Truncate or shorten the output. Useful for status bars',
                false
            )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get the current timer for the current user
        $currentTimer = $this->getCurrentTimer();
        print_r($currentTimer);

        if (empty($currentTimer)) {
            // If there's no current timer, just return a successful response
            // as there's nothing really wrong
            return Command::SUCCESS;
        }

        // $outputTemplate =
        //
        // When short:
        // - Shorten project to just AC from AC: Acquisition if `:` is present
        // - Count tags instead of listing

        // TODO: Format output of current timer. Duration - Description - tags
        return Command::SUCCESS;
    }
}
