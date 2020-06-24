<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

/**
 * Class BaseCommand
 * @author Grant Lucas
 */
abstract class BaseCommand extends Command
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

    /**
     * Current Timer
     *
     * Get the current, in progress, time entry for the current user. This
     * will be a hydrated object with IDs and human readable information for
     * most use cases.
     */
    protected function getCurrentTimer(): array
    {
        // FIXME: Somehow cache this result as the current user rarely changes
        // and we can save an API hit. Look into object caching libraries like
        // the [Symfony cache component](https://symfony.com/doc/current/components/cache.html)

        // Get current user so the ID can be used in subsequent requests
        $currentUser = $this->clockifyClient->get('user');

        // Build route for this user's time entries
        $route = "workspaces/{$_ENV['CLOCKIFY_WORKSPACE']}/user/{$currentUser['id']}/time-entries";

        // Add query params to get in progress, hydrated timer only
        $route .= "?" . http_build_query([
            'in-progress' => true,
            'hydrated' => true,
            'consider-duration-format' => true,
        ]);

        // Fetch the current timer
        $currentTimer =  $this->clockifyClient->get($route);

        if (empty($currentTimer)) {
            return [];
        }

        // Convert array to single item for this use case
        $currentTimer = reset($currentTimer);

        // Calculate current duration based on `timerInterval` start since it
        // doesn't seem to include it automatically.
        $startTime = strtotime($currentTimer['timeInterval']['start']);
        $durationSec = time() - $startTime;

        // Format duration in HH:MM:SS
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$durationSec");
        $durationCalculated = $dtF->diff($dtT)->format('%H:%I:%S');

        // Add calculated duration to response
        $currentTimer['calculatedDuration'] = $durationCalculated;

        return $currentTimer;
    }
}
