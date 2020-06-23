<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Input\InputOption;
use JDecool\Clockify\Client as ClockifyClient;

class SetupCommand extends BaseCommand
{
    protected static $defaultName = 'setup';

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
            ->setDescription('Setup Clockify CLI App')
            ->setHelp('This will aid in setting up the current instance to connect with the correct workspace.');

        $this->addOption(
            'automated',
            null,
            InputOption::VALUE_NONE,
            'Whether this is automated setup'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Detect if we're in automated setup
        $automatedSetup = $input->getOption('automated');

        if ($automatedSetup) {
            $output->writeln([
                'No <comment>CLOCKIFY_WORKSPACE</comment> ENV var was detected.',
                '',
                'The following setup will help determine what to set for the future.',
                'This application <options=bold,underscore>requires</> a Workspace ID to be set to function properly.',
                ''
            ]);
        }

        $loadingSection = $this->createSection($output);
        $loadingSection->writeln('<info>Fetching workspances from Clockify...</info>');

        // Load all the workspaces and prsent a list for choice
        $workspaceData = $this->clockifyClient->get('workspaces');
        $workspaces = array_column($workspaceData, 'name', 'id');

        // Clear loading message
        $loadingSection->clear();

        // Error out if we have no workspaces
        if (empty($workspaces)) {
            $output->writeln('<error>No workspaces found</error>');
            return Command::FAILURE;
        }

        // If there's only one workspace found, set that as the selected workspace
        if (count($workspaces) === 1) {
            $workspaceID = array_key_first($workspaces);
            $output->writeln("<comment>Only one workspace ({$workspaces[$workspaceID]}) found in account...</comment>");
            $selectedWorkspaceID = array_key_first($workspaces);
        } else {
            // Prompt user to choose a workspace when there are multiples
            $questionHelper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Select which workspace to use',
                $workspaces
            );

            // Ask and store the workspace ID selected
            $selectedWorkspaceID = $questionHelper->ask($input, $output, $question);
        }

        $output->writeln([
            '',
            'Add the following Workspace ID to your `.env` or Environment:',
            "<comment>CLOCKIFY_WORKSPACE='{$selectedWorkspaceID}'</comment>",
            '',
            <<<TEXT
Once added, you should not be prompted for setup again. If you wish to change
workspaces, simply remove this ENV variable or run this setup command
manually to see valid options to set in your environment.
TEXT
        ]);

        // TODO: Get the current user and prompt to store that
        // $workspaceData = $this->clockifyClient->get('user');
        // TODO: In here, detect _which_ key is missing and only show the
        // relevant information or make that input options for what to prompt
        // for

        return Command::SUCCESS;
    }
}
