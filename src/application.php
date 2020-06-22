#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;

// Load Dotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Require API key
$dotenv->required('CLOCKIFY_API_KEY')->notEmpty();

$application = new Application();

// Set up app information
$application->setName('Clockify CLI Tool');
$application->setVersion('0.0.1');

// ... register commands

$application->run();
