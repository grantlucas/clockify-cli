<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use JDecool\Clockify\Client as ClockifyClient;
use App\Models\Item;

class TimerStartCommand extends BaseCommand
{
    protected static $defaultName = 'timer:start';

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
            ->setDescription('Start/continue a timer')
            ->setHelp('Start or continue a timer, setting the project and optional tags.');

        // Input arguments
        $this
            ->addArgument(
                'description',
                InputArgument::REQUIRED,
                'The description for this timer.'
            );

        // Input options
        $this
            // Project option
            ->addOption(
                'project',
                'p',
                InputOption::VALUE_REQUIRED,
                'The project to assign this timer to'
            )

            // Tags Option
            // TODO: Make this support multiple tags later
            ->addOption(
                'tag',
                't',
                InputOption::VALUE_OPTIONAL,
                'Tag to assign to this timer',
                false
            )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Process and select a project
        try {
            $selectedProject = $this->selectProject($input, $output);
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return Command::FAILURE;
        }

        // Output what project was selected
        $output->writeln([
            '',
            "Selected project: <comment>{$selectedProject->getName()}</comment>",
        ]);

        // Process and select tag if tag input param exists at all
        $tagSet = $input->getOption('tag');
        $selectedTag = null;
        if ($tagSet !== false) {
            try {
                $selectedTag = $this->selectTag($input, $output);
            } catch (\Exception $e) {
                $output->writeln("<error>{$e->getMessage()}</error>");
                return Command::FAILURE;
            }

            // Output what tag was selected
            $output->writeln([
                '',
                "Selected tag: <comment>{$selectedTag->getName()}</comment>",
            ]);
        }

        // Build request to send to Clockify to start a timer starting now
        $entryData = [
            'start' => date("Y-m-d\TH:i:s\Z"),
            'description' => $input->getArgument('description'),
            'projectId' => $selectedProject->getId(),
        ];

        if (!empty($selectedTag)) {
            $entryData['tagIds'] = [];
            $entryData['tagIds'][] = $selectedTag->getId();
        }

        try {
            $output->writeln('<info>Starting Timer</info>');

            // POST data to Clockify and watch for response
            $createdEntry = $this->clockifyClient->post("/workspaces/{$_ENV['CLOCKIFY_WORKSPACE']}/time-entries", $entryData);

            $output->writeln('<info>Timer started</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln([
                '',
                "<fg=red>Error:</>",
                $e->getMessage()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Select Project
     *
     * Perform the needed queries and selections for a project
     *
     * @return Item A processed item consisting of an ID and name
     * @throws Exception When no projects are found
     */
    private function selectProject(InputInterface $input, OutputInterface $output): Item
    {
        $questionHelper = $this->getHelper('question');

        $loadingSection = $this->createSection($output);
        $loadingSection->writeln('<info>Fetching Projects from Clockify...</info>');

        // Load projects
        $projectsRoute = "workspaces/{$_ENV['CLOCKIFY_WORKSPACE']}/projects";

        $queryParams = [];

        // If project is provided, add name search when loading projects
        $desiredProject = $input->getOption('project');

        if ($desiredProject) {
            $queryParams['name'] = $desiredProject;
        }

        if (!empty($queryParams)) {
            $projectsRoute .= '?' . http_build_query($queryParams);
        }

        $projectsData = $this->clockifyClient->get($projectsRoute);
        $projects = array_column($projectsData, 'name', 'id');

        // Clear loading message
        $loadingSection->clear();

        // Error out if we have no project to work with
        if (empty($projects)) {
            throw new \Exception('No projects found');
        }


        if (count($projects) > 1) {
            // If we have multiple projects returned, show a selection list
            $projectQuestion = new ChoiceQuestion(
                'Select which project to use',
                $projects
            );

            $selectedProjectID = $questionHelper->ask($input, $output, $projectQuestion);
        } else {
            // Single project found, use it's ID
            $selectedProjectID = array_key_first($projects);


            $confirmationQuestion = new ConfirmationQuestion(
                "One project found: <comment>{$projects[$selectedProjectID]}</comment>. Do you wish to continue? (y/N): ",
                false,
            );

            // Ask if we want to continue. If no, exit the process so the user
            // can start over
            if (!$questionHelper->ask($input, $output, $confirmationQuestion)) {
                throw new \Exception('Aborted');
            }
        }

        return new Item($selectedProjectID, $projects[$selectedProjectID]);
    }

    /**
     * Select Tag
     *
     * Perform the needed queries and user interaction to set a tag
     *
     * NOTE: This is currently limted to one tag for simplicity. In the future
     * we should be able to select and set multiple tags at once.
     *
     * @return Item A processed item consisting of an ID and name
     * @throws Exception When no tag is found by the desired input
     */
    private function selectTag(InputInterface $input, OutputInterface $output): Item
    {
        $questionHelper = $this->getHelper('question');

        $loadingSection = $this->createSection($output);
        $loadingSection->writeln('<info>Fetching Tags from Clockify...</info>');

        // Load tags
        $tagsRoute = "workspaces/{$_ENV['CLOCKIFY_WORKSPACE']}/tags";

        $queryParams = [];

        // If tag is provided, add name search when loading tags
        $desiredTag = $input->getOption('tag');

        if ($desiredTag) {
            $queryParams['name'] = $desiredTag;
        }

        if (!empty($queryParams)) {
            $tagsRoute .= '?' . http_build_query($queryParams);
        }

        $tagsData = $this->clockifyClient->get($tagsRoute);
        $tags = array_column($tagsData, 'name', 'id');

        // Clear loading message
        $loadingSection->clear();

        // Error out if we have no tag to work with
        if (empty($tags)) {
            throw new \Exception('No tags found');
        }

        if (count($tags) > 1) {
            // If we have multiple tags returned, show a selection list
            $tagQuestion = new ChoiceQuestion(
                'Select which tag to use',
                $tags
            );

            $selectedTagID = $questionHelper->ask($input, $output, $tagQuestion);
        } else {
            // Single tag found, use it's ID
            $selectedTagID = array_key_first($tags);

            $confirmationQuestion = new ConfirmationQuestion(
                "One tag found: <comment>{$tags[$selectedTagID]}</comment>. Do you wish to continue? (y/N): ",
                false,
            );

            // Ask if we want to continue. If no, exit the process so the user
            // can start over
            if (!$questionHelper->ask($input, $output, $confirmationQuestion)) {
                throw new \Exception('Aborted');
            }
        }

        return new Item($selectedTagID, $tags[$selectedTagID]);
    }
}
