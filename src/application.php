#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

// Load Dotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Require API key
$dotenv->required('CLOCKIFY_API_KEY')->notEmpty();

// Build out the Clockify PHP Client to inject into commands

$builder = new JDecool\Clockify\ClientBuilder();
$clockifyClient = $builder->createClientV1($_ENV['CLOCKIFY_API_KEY']);

$application = new Application();

// Set up app information
$application->setName('Clockify CLI Tool');
$application->setVersion('0.0.1');

// Register Setup command
$application->add(new App\Commands\SetupCommand($clockifyClient));

// Check if we have a defined workspace and, if not, force the run of the setup command
if (empty($_ENV['CLOCKIFY_WORKSPACE'])) {
    // Only show automated setup if certain helper options are being used
    if (
        !in_array('--help', $_SERVER['argv'])
        && !in_array('-h', $_SERVER['argv'])
        && !in_array('-V', $_SERVER['argv'])
        && !in_array('--version', $_SERVER['argv'])
    ) {
        $setupCommand = $application->find('setup');
        $arguments = [
            '--automated' => true,
        ];

        $setupInput = new ArrayInput($arguments);
        $setupCommand->run($setupInput, new ConsoleOutput());

        // Exit early to prevent the rest of the app from showing
        return;
    }
}

// Register Timer Start command
$application->add(new App\Commands\TimerStartCommand($clockifyClient));

$application->run();
