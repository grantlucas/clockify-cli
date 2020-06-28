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
            ->setHelp('Show the current timer with optional short format')
            ->setAliases([
                't:c',
                'tc',
            ]);

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

        if (empty($currentTimer)) {
            // If there's no current timer, just return a successful response
            // as there's nothing really wrong
            return Command::SUCCESS;
        }

        // Set the duration
        $duration = $currentTimer['calculatedDuration'];

        // Set/format the project
        $project = $currentTimer['project']['name'];

        // Set/format the description
        $desc = $currentTimer['description'];

        // Set/format the tags
        $tags = '';
        foreach ($currentTimer['tags'] as $tag) {
            if ($tags !== '') {
                $tags .= ', ';
            }

            $tags .= $tag['name'];
        }

        // TODO: Support short tag
        // TODO: Allow number to be set in short tag that's the MAX width of
        // the entire output
        // TODO: Simplify duration output if hours are empty
        // TODO: Set up helper function to "shorten" projects and tags
        // "intelligently" (use first letters of each word or the letters
        // before `:` for projects etc)

        // Combine and output summary of current timer
        $output->writeln("{$duration} - {$project} - {$tags} - {$desc}");

        return Command::SUCCESS;
    }
}
